<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\NewsService;
use Illuminate\Support\Facades\Log;

class NewsController extends Controller
{
    protected $newsService;

    public function __construct(NewsService $newsService)
    {
        $this->newsService = $newsService;
    }

    /**
     * Display the news page
     */
    public function index(Request $request)
    {
        try {
            $page = $request->get('page', 1);
            $limit = $request->get('limit', 12);
            $search = $request->get('search');

            // Validate page and limit
            $page = max(1, (int) $page);
            $limit = max(1, min(50, (int) $limit)); // Limit between 1-50

            // Always get featured news
            $featuredNews = $this->newsService->getFeaturedNews();
            
            if ($search) {
                $newsData = $this->newsService->searchNews($search, $limit, $page);
            } else {
                // Get main news excluding featured items
                $newsData = $this->newsService->getNewsExcludingFeatured($limit, $page);
            }

            return view('cricket.news', compact('newsData', 'featuredNews', 'search'));
            
        } catch (\Exception $e) {
            Log::error('NewsController: Error in index method', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);
            
            return view('cricket.news', [
                'newsData' => [
                    'news' => [],
                    'total' => 0,
                    'per_page' => 12,
                    'current_page' => 1,
                    'last_page' => 1,
                    'has_more' => false
                ],
                'featuredNews' => [],
                'search' => $request->get('search'),
                'error' => 'Unable to load news at the moment. Please try again later.'
            ]);
        }
    }

    /**
     * Get latest news (AJAX endpoint)
     */
    public function getLatest(Request $request)
    {
        try {
            $limit = $request->get('limit', 10);
            $page = $request->get('page', 1);
            $limit = max(1, min(50, (int) $limit));
            $page = max(1, (int) $page);

            Log::info('NewsController: getLatest called', [
                'limit' => $limit,
                'page' => $page
            ]);

            $newsData = $this->newsService->getNewsExcludingFeatured($limit, $page);

            Log::info('NewsController: getLatest success', [
                'news_count' => count($newsData['news']),
                'has_more' => $newsData['has_more']
            ]);

            return response()->json([
                'success' => true,
                'data' => $newsData
            ]);
            
        } catch (\Exception $e) {
            Log::error('NewsController: Error in getLatest method', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Unable to fetch latest news: ' . $e->getMessage(),
                'data' => ['news' => []]
            ], 500);
        }
    }

    /**
     * Search news (AJAX endpoint)
     */
    public function search(Request $request)
    {
        try {
            $query = $request->get('q', '');
            $page = $request->get('page', 1);
            $limit = $request->get('limit', 12);

            Log::info('NewsController: search called', [
                'query' => $query,
                'page' => $page,
                'limit' => $limit
            ]);

            if (empty(trim($query))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Search query is required'
                ], 400);
            }

            $page = max(1, (int) $page);
            $limit = max(1, min(50, (int) $limit));

            $newsData = $this->newsService->searchNews($query, $limit, $page);

            Log::info('NewsController: search success', [
                'query' => $query,
                'news_count' => count($newsData['news']),
                'has_more' => $newsData['has_more']
            ]);

            return response()->json([
                'success' => true,
                'data' => $newsData
            ]);
            
        } catch (\Exception $e) {
            Log::error('NewsController: Error in search method', [
                'error' => $e->getMessage(),
                'query' => $request->get('q'),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Search failed: ' . $e->getMessage(),
                'data' => ['news' => []]
            ], 500);
        }
    }

    /**
     * Refresh news cache
     */
    public function refresh()
    {
        try {
            $this->newsService->clearCache();
            
            return response()->json([
                'success' => true,
                'message' => 'News cache refreshed successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('NewsController: Error in refresh method', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to refresh news cache'
            ], 500);
        }
    }
}
