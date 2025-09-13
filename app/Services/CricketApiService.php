<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CricketApiService
{
    protected $apiKey;
    protected $baseUrl;
    protected $apiHost;
    protected $cacheTtl;

    public function __construct()
    {
        $this->apiKey = config('services.cricbuzz.api_key');
        $this->baseUrl = config('services.cricbuzz.base_url');
        $this->apiHost = config('services.cricbuzz.api_host');
        $this->cacheTtl = 30000;
    }


    public function getEvents($dateStart = null, $dateStop = null, $leagueKey = null, $eventKey = null)
    {
        $cacheKey = 'cricket_events_' . ($dateStart ?? 'all') . '_' . ($dateStop ?? 'all') . '_' . ($leagueKey ?? 'all') . '_' . ($eventKey ?? 'all');

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($dateStart, $dateStop, $leagueKey, $eventKey) { // Cache for 5 minutes
            try {

                // Use Cricbuzz API endpoints based on the request
                $allMatches = [];

                // Get live matches
                $liveMatches = $this->getLiveMatches();
                if (!empty($liveMatches)) {
                    $allMatches = array_merge($allMatches, $this->extractMatchesFromCricbuzzResponse($liveMatches));
                }

                // Get upcoming matches
                $upcomingMatches = $this->getUpcomingMatches();
                if (!empty($upcomingMatches)) {
                    $allMatches = array_merge($allMatches, $this->extractMatchesFromCricbuzzResponse($upcomingMatches));
                }

                // Get recent matches
                $recentMatches = $this->getRecentMatches();
                if (!empty($recentMatches)) {
                    $allMatches = array_merge($allMatches, $this->extractMatchesFromCricbuzzResponse($recentMatches));
                }

                Log::info('Cricbuzz Events API response', [
                    'total_matches' => count($allMatches),
                    'live_count' => count($this->extractMatchesFromCricbuzzResponse($liveMatches)),
                    'upcoming_count' => count($this->extractMatchesFromCricbuzzResponse($upcomingMatches)),
                    'recent_count' => count($this->extractMatchesFromCricbuzzResponse($recentMatches))
                ]);

                return $allMatches;
            } catch (\Exception $e) {
                Log::error('Exception while fetching events from Cricbuzz API', ['error' => $e->getMessage()]);
                return [];
            }
        });
    }

    /**
     * Get live matches from Cricbuzz API
     */
    public function getLiveMatches()
    {
        try {
            $endpoint = '/matches/v1/live';

            $response = Http::timeout(30)
                ->withHeaders([
                    'X-RapidAPI-Key' => $this->apiKey,
                    'X-RapidAPI-Host' =>  $this->apiHost
                ])
                ->get($this->baseUrl . $endpoint);

            if ($response->successful()) {
                $data = $response->json();
                log::info('Cricbuzz Live Matches API response', [
                    'total_matches' => count($data['typeMatches'] ?? [])
                ]);
                // Log API response for future mock data
                $this->logApiResponse('live_matches', $data);

                return $data;
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Failed to fetch live matches from Cricbuzz API', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get upcoming matches from Cricbuzz API
     */
    public function getUpcomingMatches()
    {
        try {
            $endpoint = '/matches/v1/upcoming';

            $response = Http::timeout(30)
                ->withHeaders([
                    'X-RapidAPI-Key' => $this->apiKey,
                    'X-RapidAPI-Host' =>  $this->apiHost
                ])
                ->get($this->baseUrl . $endpoint);

            if ($response->successful()) {
                $data = $response->json();

                // Log API response for future mock data
                $this->logApiResponse('upcoming_matches', $data);

                return $data;
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Failed to fetch upcoming matches from Cricbuzz API', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get recent matches from Cricbuzz API
     */
    public function getRecentMatches()
    {
        try {
            $endpoint = '/matches/v1/recent';

            $response = Http::timeout(30)
                ->withHeaders([
                    'X-RapidAPI-Key' => $this->apiKey,
                    'X-RapidAPI-Host' =>  $this->apiHost
                ])
                ->get($this->baseUrl . $endpoint);

            if ($response->successful()) {
                $data = $response->json();

                // Log API response for future mock data
                $this->logApiResponse('recent_matches', $data);

                return $data;
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Failed to fetch recent matches from Cricbuzz API', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Extract matches from Cricbuzz API response
     */
    private function extractMatchesFromCricbuzzResponse($cricbuzzData)
    {
        if (empty($cricbuzzData)) {
            return [];
        }

        $matches = [];

        // Handle the actual Cricbuzz API response structure
        if (isset($cricbuzzData['typeMatches'])) {
            // If data has typeMatches structure (from some endpoints)
            foreach ($cricbuzzData['typeMatches'] as $typeMatch) {
                if (isset($typeMatch['seriesMatches'])) {
                    foreach ($typeMatch['seriesMatches'] as $seriesMatch) {
                        if (isset($seriesMatch['seriesAdWrapper']['matches'])) {
                            foreach ($seriesMatch['seriesAdWrapper']['matches'] as $match) {
                                $matches[] = $match;
                            }
                        }
                    }
                }
            }
        } else {
            // If data is already a flat array of matches (like from /matches/v1/live)
            if (is_array($cricbuzzData)) {
                foreach ($cricbuzzData as $item) {
                    if (isset($item['matchInfo'])) {
                        $matches[] = $item;
                    }
                }
            }
        }

        return $matches;
    }

    /**
     * Get match commentary from Cricbuzz API
     */
    public function getMatchCommentary($matchId, $iid = 2)
    {
        // Use default value if iid is null
        $iid = $iid ?? 2;
        $cacheKey = "cricket_match_commentary_{$matchId}_{$iid}";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($matchId, $iid) {
            try {
                $endpoint = "/mcenter/v1/{$matchId}/comm?iid={$iid}";

                $response = Http::timeout(30)
                    ->withHeaders([
                        'X-RapidAPI-Key' => $this->apiKey,
                        'X-RapidAPI-Host' => $this->apiHost
                    ])
                    ->get($this->baseUrl . $endpoint);

                Log::info('Cricbuzz Commentary API call', [
                    'endpoint' => $endpoint,
                    'status' => $response->status()
                ]);

                if ($response->successful()) {
                    $data = $response->json();

                    // Debug if empty
                    if (empty($data)) {
                        Log::warning('No commentary data returned', [
                            'match_id' => $matchId,
                            'iid' => $iid,
                            'body' => $response->body()
                        ]);
                    }

                    return $data;
                }

                Log::error('Failed to fetch commentary from Cricbuzz API', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);

                return [];
            } catch (\Exception $e) {
                Log::error('Exception fetching match commentary', [
                    'match_id' => $matchId,
                    'error' => $e->getMessage()
                ]);
                return [];
            }
        });
    }

    /**
     * Get match Scorecard from Cricbuzz API
     */
    public function getMatchScorecard($matchId)
    {
        $cacheKey = "cricket_match_scorecard_{$matchId}";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($matchId) {
            try {
                $endpoint = "/mcenter/v1/{$matchId}/scard";

                $response = Http::timeout(30)
                    ->withHeaders([
                        'X-RapidAPI-Key' => $this->apiKey,
                        'X-RapidAPI-Host' => $this->apiHost
                    ])
                    ->get($this->baseUrl . $endpoint);

                Log::info('Cricbuzz Scorecard API call', [
                    'endpoint' => $endpoint,
                    'status' => $response->status()
                ]);

                if ($response->successful()) {
                    $data = $response->json();

                    // Debug if empty
                    if (empty($data)) {
                        Log::warning('No Scorecard data returned', [
                            'match_id' => $matchId,
                            'body' => $response->body()
                        ]);
                    }

                    return $data;
                }

                Log::error('Failed to fetch Scorecard from Cricbuzz API', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);

                return [];
            } catch (\Exception $e) {
                Log::error('Exception fetching match Scorecard', [
                    'match_id' => $matchId,
                    'error' => $e->getMessage()
                ]);
                return [];
            }
        });
    }

    /**
     * Get match Scorecard from Cricbuzz API
     */
    public function getMatchSquads($matchId)
    {
        $cacheKey = "cricket_match_squads_{$matchId}";

        return Cache::remember($cacheKey, now()->addDays(5), function () use ($matchId) {
            try {
                $endpoint = "/mcenter/v1/{$matchId}/teams";

                $response = Http::timeout(30)
                    ->withHeaders([
                        'X-RapidAPI-Key' => $this->apiKey,
                        'X-RapidAPI-Host' => $this->apiHost
                    ])
                    ->get($this->baseUrl . $endpoint);

                Log::info('Cricbuzz squads API call', [
                    'endpoint' => $endpoint,
                    'status' => $response->status()
                ]);

                if ($response->successful()) {
                    $data = $response->json();

                    // Debug if empty
                    if (empty($data)) {
                        Log::warning('No squads data returned', [
                            'match_id' => $matchId,
                            'body' => $response->body()
                        ]);
                    }

                    return $data;
                }

                Log::error('Failed to fetch squads from Cricbuzz API', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);

                return [];
            } catch (\Exception $e) {
                Log::error('Exception fetching match squads', [
                    'match_id' => $matchId,
                    'error' => $e->getMessage()
                ]);
                return [];
            }
        });
    }

    /**
     * Log API response for future mock data
     */
    private function logApiResponse($endpoint, $data)
    {
        try {
            $logDir = storage_path('logs/api_responses');
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }

            $filename = $endpoint . '_' . date('Y-m-d_H-i-s') . '.json';
            $filepath = $logDir . '/' . $filename;

            $logData = [
                'timestamp' => now()->toISOString(),
                'endpoint' => $endpoint,
                'data' => $data
            ];

            file_put_contents($filepath, json_encode($logData, JSON_PRETTY_PRINT));

            Log::info('API response logged for mock data', [
                'endpoint' => $endpoint,
                'filepath' => $filepath,
                'data_size' => strlen(json_encode($data)),
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log API response', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage()
            ]);
        }
    }
}
