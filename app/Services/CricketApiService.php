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
        $this->cacheTtl = 30000; // 5 minutes cache for API data
    }

    public function getLiveScores($leagueKey = null, $matchKey = null)
    {
        $cacheKey = 'cricket_livescores_' . ($leagueKey ?? 'all') . '_' . ($matchKey ?? 'all');

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($leagueKey, $matchKey) {
            try {
                // Use the actual Cricbuzz API endpoint for live matches
                $endpoint = '/matches/v1/live';

                $response = Http::timeout(30)
                    ->withHeaders([
                        'X-RapidAPI-Key' => $this->apiKey,
                        'X-RapidAPI-Host' =>  $this->apiHost
                    ])
                    ->get($this->baseUrl . $endpoint);

                Log::info('Cricbuzz Live Matches API call', [
                    'endpoint' => $endpoint,
                    'status' => $response->status(),
                    'response_length' => strlen($response->body())
                ]);

                if ($response->successful()) {
                    $data = $response->json();

                    // Log API response for future mock data
                    $this->logApiResponse('live_matches', $data);

                    Log::info('Cricbuzz Live Matches API response', [
                        'total_matches' => count($data['typeMatches'] ?? [])
                    ]);
                    return $data;
                }

                Log::error('Failed to fetch live scores from Cricbuzz API', [
                    'response' => $response->body(),
                    'status' => $response->status()
                ]);

                return [];
            } catch (\Exception $e) {
                Log::error('Exception while fetching live scores from Cricbuzz API', ['error' => $e->getMessage()]);
                return [];
            }
        });
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
     * Get teams data using the get_teams API method
     */
    public function getTeams($teamKey = null, $leagueKey = null)
    {
        $cacheKey = 'cricket_teams_' . ($teamKey ?? 'all') . '_' . ($leagueKey ?? 'all');

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($teamKey, $leagueKey) {
            try {
                Log::info('Fetching teams from Cricket API', [
                    'url' => $this->baseUrl,
                    'api_key' => substr($this->apiKey, 0, 10) . '...',
                    'method' => 'get_teams',
                    'team_key' => $teamKey,
                    'league_key' => $leagueKey
                ]);

                $params = [
                    'method' => 'get_teams',
                    'APIkey' => $this->apiKey
                ];

                if ($teamKey) {
                    $params['team_key'] = $teamKey;
                }

                if ($leagueKey) {
                    $params['league_key'] = $leagueKey;
                }

                $fullUrl = $this->baseUrl . http_build_query($params);
                Log::info('Making HTTP request to teams API', [
                    'full_url' => $fullUrl,
                    'params' => $params
                ]);

                $response = Http::timeout(30)->get($this->baseUrl, $params);

                Log::info('Teams API response received', [
                    'status' => $response->status(),
                    'success' => $response->successful(),
                    'body_length' => strlen($response->body())
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    Log::info('Teams API response parsed', [
                        'success' => $data['success'] ?? 'not_set',
                        'result_count' => isset($data['result']) ? count($data['result']) : 'not_set',
                        'full_response' => $data
                    ]);

                    if (!empty($data['success']) && $data['success'] == 1) {
                        return $data['result'];
                    }
                }

                Log::error('Failed to fetch teams from Cricket API', [
                    'response' => $response->body(),
                    'status' => $response->status(),
                    'headers' => $response->headers()
                ]);

                return [];
            } catch (\Exception $e) {
                Log::error('Exception while fetching teams from API', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return [];
            }
        });
    }


    /**
     * Get teams for a specific league
     */
    public function getTeamsByLeague($leagueKey)
    {
        return $this->getTeams(null, $leagueKey);
    }

    /**
     * Get a specific team by team_key
     */
    public function getTeam($teamKey)
    {
        $teams = $this->getTeams($teamKey);
        return $teams[0] ?? null;
    }

    public function getStandings($leagueKey)
    {
        $cacheKey = 'cricket_standings_' . $leagueKey;

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($leagueKey) { // Cache for 30 minutes
            try {
                $response = Http::timeout(30)->get($this->baseUrl, [
                    'method' => 'get_standings',
                    'league_key' => $leagueKey,
                    'APIkey' => $this->apiKey
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    if ($data['success'] == 1) {
                        return $data['result'];
                    }
                }

                Log::error('Failed to fetch standings from Cricket API', [
                    'response' => $response->body(),
                    'status' => $response->status()
                ]);

                return [];
            } catch (\Exception $e) {
                Log::error('Exception while fetching standings', ['error' => $e->getMessage()]);
                return [];
            }
        });
    }

    public function getH2H($firstTeamKey, $secondTeamKey)
    {
        $cacheKey = 'cricket_h2h_' . $firstTeamKey . '_' . $secondTeamKey;

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($firstTeamKey, $secondTeamKey) { // Cache for 30 minutes
            try {
                $response = Http::timeout(30)->get($this->baseUrl, [
                    'method' => 'get_H2H',
                    'first_team_key' => $firstTeamKey,
                    'second_team_key' => $secondTeamKey,
                    'APIkey' => $this->apiKey
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    if ($data['success'] == 1) {
                        return $data['result'];
                    }
                }

                Log::error('Failed to fetch H2H from Cricket API', [
                    'response' => $response->body(),
                    'status' => $response->status()
                ]);

                return [];
            } catch (\Exception $e) {
                Log::error('Exception while fetching H2H', ['error' => $e->getMessage()]);
                return [];
            }
        });
    }

    public function getOdds($dateStart = null, $dateStop = null, $leagueKey = null, $eventKey = null)
    {
        $cacheKey = 'cricket_odds_' . ($dateStart ?? 'all') . '_' . ($dateStop ?? 'all') . '_' . ($leagueKey ?? 'all') . '_' . ($eventKey ?? 'all');

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($dateStart, $dateStop, $leagueKey, $eventKey) { // Cache for 15 minutes
            try {
                $params = [
                    'method' => 'get_odds',
                    'APIkey' => $this->apiKey
                ];

                if ($dateStart) {
                    $params['date_start'] = $dateStart;
                }

                if ($dateStop) {
                    $params['date_stop'] = $dateStop;
                }

                if ($leagueKey) {
                    $params['league_key'] = $leagueKey;
                }

                if ($eventKey) {
                    $params['event_key'] = $eventKey;
                }

                $response = Http::timeout(30)->get($this->baseUrl, $params);

                if ($response->successful()) {
                    $data = $response->json();
                    if ($data['success'] == 1) {
                        return $data['result'];
                    }
                }

                Log::error('Failed to fetch odds from Cricket API', [
                    'response' => $response->body(),
                    'status' => $response->status()
                ]);

                return [];
            } catch (\Exception $e) {
                Log::error('Exception while fetching odds', ['error' => $e->getMessage()]);
                return [];
            }
        });
    }

    /**
     * Get detailed scorecard for a specific match
     */
    public function getScorecard($eventKey)
    {
        $cacheKey = 'cricket_scorecard_' . $eventKey;

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($eventKey) { // Cache for 5 minutes
            try {
                $params = [
                    'method' => 'get_scorecard',
                    'APIkey' => $this->apiKey,
                    'event_key' => $eventKey
                ];

                $response = Http::timeout(30)->get($this->baseUrl, $params);

                if ($response->successful()) {
                    $data = $response->json();
                    if ($data['success'] == 1) {
                        return $data['result'];
                    }
                }

                Log::warning('Failed to fetch scorecard from Cricket API', [
                    'event_key' => $eventKey,
                    'response' => $response->body(),
                    'status' => $response->status()
                ]);

                return [];
            } catch (\Exception $e) {
                Log::error('Exception while fetching scorecard', [
                    'event_key' => $eventKey,
                    'error' => $e->getMessage()
                ]);
                return [];
            }
        });
    }

    /**
     * Get ball-by-ball commentary for a specific match
     */
    public function getCommentary($eventKey)
    {
        $cacheKey = 'cricket_commentary_' . $eventKey;

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($eventKey) { // Cache for 5 minutes
            try {
                $params = [
                    'method' => 'get_commentary',
                    'APIkey' => $this->apiKey,
                    'event_key' => $eventKey
                ];

                $response = Http::timeout(30)->get($this->baseUrl, $params);

                if ($response->successful()) {
                    $data = $response->json();
                    if ($data['success'] == 1) {
                        return $data['result'];
                    }
                }

                Log::warning('Failed to fetch commentary from Cricket API', [
                    'event_key' => $eventKey,
                    'response' => $response->body(),
                    'status' => $response->status()
                ]);

                return [];
            } catch (\Exception $e) {
                Log::error('Exception while fetching commentary', [
                    'event_key' => $eventKey,
                    'error' => $e->getMessage()
                ]);
                return [];
            }
        });
    }

    /**
     * Get detailed lineups for a specific match
     */
    public function getLineups($eventKey)
    {
        $cacheKey = 'cricket_lineups_' . $eventKey;

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($eventKey) { // Cache for 5 minutes
            try {
                $params = [
                    'method' => 'get_lineups',
                    'APIkey' => $this->apiKey,
                    'event_key' => $eventKey
                ];

                $response = Http::timeout(30)->get($this->baseUrl, $params);

                if ($response->successful()) {
                    $data = $response->json();
                    if ($data['success'] == 1) {
                        return $data['result'];
                    }
                }

                Log::warning('Failed to fetch lineups from Cricket API', [
                    'event_key' => $eventKey,
                    'response' => $response->body(),
                    'status' => $response->status()
                ]);

                return [];
            } catch (\Exception $e) {
                Log::error('Exception while fetching lineups', [
                    'event_key' => $eventKey,
                    'error' => $e->getMessage()
                ]);
                return [];
            }
        });
    }

    /**
     * Get comprehensive match statistics
     */
    public function getMatchStatistics($eventKey)
    {
        $cacheKey = 'cricket_match_stats_' . $eventKey;

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($eventKey) { // Cache for 5 minutes
            try {
                $params = [
                    'method' => 'get_match_statistics',
                    'APIkey' => $this->apiKey,
                    'event_key' => $eventKey
                ];

                $response = Http::timeout(30)->get($this->baseUrl, $params);

                if ($response->successful()) {
                    $data = $response->json();
                    if ($data['success'] == 1) {
                        return $data['result'];
                    }
                }

                Log::warning('Failed to fetch match statistics from Cricket API', [
                    'event_key' => $eventKey,
                    'response' => $response->body(),
                    'status' => $response->status()
                ]);

                return [];
            } catch (\Exception $e) {
                Log::error('Exception while fetching match statistics', [
                    'event_key' => $eventKey,
                    'error' => $e->getMessage()
                ]);
                return [];
            }
        });
    }

    public function clearCache($type = null)
    {
        if ($type) {
            Cache::forget('cricket_' . $type);
        } else {
            // Clear all cricket cache
            $keys = [
                'cricket_leagues',
                'cricket_livescores_all_all',
                'cricket_events_all_all_all_all',
                'cricket_teams_all_all',
                'cricket_standings_',
                'cricket_h2h_',
                'cricket_odds_all_all_all_all',
                'cricket_series_',
                'cricket_series_with_results_',
                'cricket_scorecard_',
                'cricket_commentary_',
                'cricket_lineups_',
                'cricket_match_stats_'
            ];

            foreach ($keys as $key) {
                Cache::forget($key);
            }
        }
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
                'data_size' => strlen(json_encode($data))
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log API response', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage()
            ]);
        }
    }
}
