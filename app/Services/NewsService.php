<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class NewsService
{
    protected $rssUrl = 'http://www.espncricinfo.com/rss/content/story/feeds/0.xml';
    protected $cacheKey = 'cricket_news_rss';
    protected $cacheDuration = 1800; // 30 minutes

    /**
     * Fetch and parse RSS feed from ESPN Cricinfo
     */
    public function getNews($limit = 20, $page = 1)
    {
        $cacheKey = $this->cacheKey . '_' . $limit . '_' . $page;
        
        return Cache::remember($cacheKey, $this->cacheDuration, function () use ($limit, $page) {
            try {
                $response = Http::timeout(30)->get($this->rssUrl);
                
                if (!$response->successful()) {
                    Log::error('NewsService: Failed to fetch RSS feed', [
                        'status' => $response->status(),
                        'url' => $this->rssUrl
                    ]);
                    return $this->getEmptyResponse();
                }

                $xml = $response->body();
                $newsData = $this->parseRssFeed($xml);
                
                // Apply pagination
                $offset = ($page - 1) * $limit;
                $paginatedNews = array_slice($newsData, $offset, $limit);
                
                return [
                    'news' => $paginatedNews,
                    'total' => count($newsData),
                    'per_page' => $limit,
                    'current_page' => $page,
                    'last_page' => ceil(count($newsData) / $limit),
                    'has_more' => ($offset + $limit) < count($newsData)
                ];
                
            } catch (\Exception $e) {
                Log::error('NewsService: Error fetching news', [
                    'error' => $e->getMessage(),
                    'url' => $this->rssUrl
                ]);
                return $this->getEmptyResponse();
            }
        });
    }

    /**
     * Parse RSS XML feed
     */
    private function parseRssFeed($xml)
    {
        try {
            $data = simplexml_load_string($xml);
            
            if (!$data || !isset($data->channel->item)) {
                Log::error('NewsService: Invalid RSS format');
                return [];
            }

            $news = [];
            
            foreach ($data->channel->item as $item) {
                $newsItem = $this->parseNewsItem($item);
                if ($newsItem) {
                    $news[] = $newsItem;
                }
            }

            Log::info('NewsService: Successfully parsed RSS feed', [
                'total_items' => count($news)
            ]);

            return $news;
            
        } catch (\Exception $e) {
            Log::error('NewsService: Error parsing RSS feed', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Parse individual news item
     */
    private function parseNewsItem($item)
    {
        try {
            // Extract title
            $title = (string) $item->title;
            if (empty($title)) {
                return null;
            }

            // Extract description
            $description = (string) $item->description;
            
            // Extract link
            $link = (string) $item->link;
            
            // Extract GUID
            $guid = (string) $item->guid;
            
            // Extract publication date
            $pubDate = (string) $item->pubDate;
            $publishedAt = $this->parseDate($pubDate);
            
            // Extract cover image
            $coverImage = '';
            if (isset($item->coverImages)) {
                $coverImage = (string) $item->coverImages;
            } elseif (isset($item->children('media', true)->content)) {
                $mediaContent = $item->children('media', true)->content;
                if (isset($mediaContent['url'])) {
                    $coverImage = (string) $mediaContent['url'];
                }
            }
            
            // Extract URL (alternative link)
            $url = $link;
            if (isset($item->url)) {
                $url = (string) $item->url;
            }

            return [
                'title' => $title,
                'description' => $description,
                'link' => $link,
                'url' => $url,
                'guid' => $guid,
                'cover_image' => $coverImage,
                'published_at' => $publishedAt,
                'published_at_human' => $this->formatDateHuman($publishedAt),
                'excerpt' => $this->createExcerpt($description, 150),
                'slug' => $this->createSlug($title)
            ];
            
        } catch (\Exception $e) {
            Log::error('NewsService: Error parsing news item', [
                'error' => $e->getMessage(),
                'item_title' => isset($item->title) ? (string) $item->title : 'Unknown'
            ]);
            return null;
        }
    }

    /**
     * Parse date string to Carbon instance
     */
    private function parseDate($dateString)
    {
        try {
            if (empty($dateString)) {
                return now();
            }
            
            return \Carbon\Carbon::parse($dateString);
        } catch (\Exception $e) {
            Log::warning('NewsService: Error parsing date', [
                'date_string' => $dateString,
                'error' => $e->getMessage()
            ]);
            return now();
        }
    }

    /**
     * Format date for human reading
     */
    private function formatDateHuman($date)
    {
        if (!$date) {
            return 'Recently';
        }
        
        try {
            return $date->diffForHumans();
        } catch (\Exception $e) {
            return 'Recently';
        }
    }

    /**
     * Create excerpt from description
     */
    private function createExcerpt($description, $length = 150)
    {
        if (empty($description)) {
            return '';
        }
        
        // Strip HTML tags
        $cleanDescription = strip_tags($description);
        
        // Truncate to specified length
        if (strlen($cleanDescription) <= $length) {
            return $cleanDescription;
        }
        
        return substr($cleanDescription, 0, $length) . '...';
    }

    /**
     * Create URL-friendly slug
     */
    private function createSlug($title)
    {
        // Convert to lowercase
        $slug = strtolower($title);
        
        // Replace spaces and special characters with hyphens
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        
        // Remove leading/trailing hyphens
        $slug = trim($slug, '-');
        
        return $slug;
    }

    /**
     * Get latest news (first page)
     */
    public function getLatestNews($limit = 10)
    {
        return $this->getNews($limit, 1);
    }

    /**
     * Get featured news (first 3 items)
     */
    public function getFeaturedNews()
    {
        $news = $this->getNews(3, 1);
        return $news['news'] ?? [];
    }

    /**
     * Get news excluding featured items (skip first 3)
     */
    public function getNewsExcludingFeatured($limit = 20, $page = 1)
    {
        $cacheKey = $this->cacheKey . '_excluding_featured_' . $limit . '_' . $page;
        
        return Cache::remember($cacheKey, $this->cacheDuration, function () use ($limit, $page) {
            try {
                $response = Http::timeout(30)->get($this->rssUrl);
                
                if (!$response->successful()) {
                    Log::error('NewsService: Failed to fetch RSS feed for excluding featured', [
                        'status' => $response->status(),
                        'url' => $this->rssUrl
                    ]);
                    return $this->getEmptyResponse();
                }

                $xml = $response->body();
                $newsData = $this->parseRssFeed($xml);
                
                // Skip first 3 items (featured news)
                $excludedNews = array_slice($newsData, 3);
                
                // Apply pagination
                $offset = ($page - 1) * $limit;
                $paginatedNews = array_slice($excludedNews, $offset, $limit);
                
                return [
                    'news' => $paginatedNews,
                    'total' => count($excludedNews),
                    'per_page' => $limit,
                    'current_page' => $page,
                    'last_page' => ceil(count($excludedNews) / $limit),
                    'has_more' => ($offset + $limit) < count($excludedNews)
                ];
                
            } catch (\Exception $e) {
                Log::error('NewsService: Error fetching news excluding featured', [
                    'error' => $e->getMessage(),
                    'url' => $this->rssUrl
                ]);
                return $this->getEmptyResponse();
            }
        });
    }

    /**
     * Clear news cache
     */
    public function clearCache()
    {
        // Clear all news-related cache keys
        $keys = [
            $this->cacheKey,
            $this->cacheKey . '_featured',
        ];
        
        // Clear paginated cache keys (approximate)
        for ($page = 1; $page <= 10; $page++) {
            for ($limit = 10; $limit <= 50; $limit += 10) {
                $keys[] = $this->cacheKey . '_' . $limit . '_' . $page;
                $keys[] = $this->cacheKey . '_excluding_featured_' . $limit . '_' . $page;
            }
        }
        
        foreach ($keys as $key) {
            Cache::forget($key);
        }
        
        Log::info('NewsService: All cache cleared');
    }

    /**
     * Get empty response structure
     */
    private function getEmptyResponse()
    {
        return [
            'news' => [],
            'total' => 0,
            'per_page' => 20,
            'current_page' => 1,
            'last_page' => 1,
            'has_more' => false
        ];
    }

    /**
     * Search news by title or description
     */
    public function searchNews($query, $limit = 20, $page = 1)
    {
        $cacheKey = $this->cacheKey . '_search_' . md5($query) . '_' . $limit . '_' . $page;
        
        return Cache::remember($cacheKey, $this->cacheDuration, function () use ($query, $limit, $page) {
            try {
                // Get all news from RSS feed directly
                $response = Http::timeout(30)->get($this->rssUrl);
                
                if (!$response->successful()) {
                    Log::error('NewsService: Failed to fetch RSS feed for search', [
                        'status' => $response->status(),
                        'url' => $this->rssUrl
                    ]);
                    return $this->getEmptyResponse();
                }

                $xml = $response->body();
                $allNews = $this->parseRssFeed($xml);
                
                if (empty($allNews)) {
                    return $this->getEmptyResponse();
                }

                $searchResults = array_filter($allNews, function($item) use ($query) {
                    $searchText = strtolower($query);
                    $title = strtolower($item['title'] ?? '');
                    $description = strtolower($item['description'] ?? '');
                    
                    return strpos($title, $searchText) !== false || 
                           strpos($description, $searchText) !== false;
                });

                $searchResults = array_values($searchResults);
                
                // Apply pagination
                $offset = ($page - 1) * $limit;
                $paginatedResults = array_slice($searchResults, $offset, $limit);
                
                return [
                    'news' => $paginatedResults,
                    'total' => count($searchResults),
                    'per_page' => $limit,
                    'current_page' => $page,
                    'last_page' => ceil(count($searchResults) / $limit),
                    'has_more' => ($offset + $limit) < count($searchResults),
                    'query' => $query
                ];
                
            } catch (\Exception $e) {
                Log::error('NewsService: Error in searchNews', [
                    'error' => $e->getMessage(),
                    'query' => $query
                ]);
                return $this->getEmptyResponse();
            }
        });
    }
}
