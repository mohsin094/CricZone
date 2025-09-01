<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CricketApiService
{
    protected $apiKey;
    protected $baseUrl;
    protected $cacheTtl;

    public function __construct()
    {
        $this->apiKey = config('services.cricbuzz.api_key');
        $this->baseUrl = 'https://cricbuzz-cricket.p.rapidapi.com';
        $this->cacheTtl = 300; // 5 minutes cache for API data
        
        // Log API configuration for debugging
        Log::info('Cricbuzz API Service Initialized', [
            'base_url' => $this->baseUrl,
            'api_key_set' => !empty($this->apiKey),
            'api_key_length' => strlen($this->apiKey),
            'headers' => [
                'X-RapidAPI-Key' => $this->apiKey,
                'X-RapidAPI-Host' => 'cricbuzz-cricket.p.rapidapi.com'
            ]
        ]);
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
                        'X-RapidAPI-Host' => 'cricbuzz-cricket.p.rapidapi.com'
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
                    'total_matches' => count($data['typeMatches'] ?? []),
                    'response_structure' => array_keys($data)
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
        
        return Cache::remember($cacheKey, 300, function () use ($dateStart, $dateStop, $leagueKey, $eventKey) { // Cache for 5 minutes
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
        
        return Cache::remember($cacheKey, 3600, function () use ($teamKey, $leagueKey) {
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
        
        return Cache::remember($cacheKey, 1800, function () use ($leagueKey) { // Cache for 30 minutes
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
        
        return Cache::remember($cacheKey, 1800, function () use ($firstTeamKey, $secondTeamKey) { // Cache for 30 minutes
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
        
        return Cache::remember($cacheKey, 900, function () use ($dateStart, $dateStop, $leagueKey, $eventKey) { // Cache for 15 minutes
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
     * Get series data for a specific league or all series
     */
    public function getSeries($leagueKey = null, $dateStart = null, $dateStop = null)
    {
        $cacheKey = 'cricket_series_' . ($leagueKey ?? 'all') . '_' . ($dateStart ?? 'all') . '_' . ($dateStop ?? 'all');
        
        return Cache::remember($cacheKey, 1800, function () use ($leagueKey, $dateStart, $dateStop) { // Cache for 30 minutes
            try {
                // Use events method to get series data
                $events = $this->getEvents($dateStart, $dateStop, $leagueKey);
                
                if (empty($events)) {
                    return [];
                }
                
                // Group events by league to form series
                $series = [];
                $leagueGroups = [];
                
                foreach ($events as $event) {
                    $leagueKey = $event['league_key'] ?? 'unknown';
                    if (!isset($leagueGroups[$leagueKey])) {
                        $leagueGroups[$leagueKey] = [
                            'league_info' => [
                                'league_key' => $leagueKey,
                                'league_name' => $event['league_name'] ?? 'Unknown League',
                                'league_year' => $event['league_year'] ?? date('Y'),
                                'league_country' => $event['league_country'] ?? 'Unknown'
                            ],
                            'events' => []
                        ];
                    }
                    $leagueGroups[$leagueKey]['events'][] = $event;
                }
                
                // Convert to series format
                foreach ($leagueGroups as $leagueKey => $group) {
                    if (count($group['events']) > 0) {
                        $series[] = [
                            'series_key' => $leagueKey,
                            'series_name' => $group['league_info']['league_name'],
                            'series_year' => $group['league_info']['league_year'],
                            'series_country' => $group['league_info']['league_country'],
                            'total_matches' => count($group['events']),
                            'live_matches' => count(array_filter($group['events'], function ($e) {
                                return in_array($e['event_status'] ?? '', ['Live', 'Started', 'In Progress']);
                            })),
                            'upcoming_matches' => count(array_filter($group['events'], function ($e) {
                                return in_array($e['event_status'] ?? '', ['Scheduled', 'Not Started']);
                            })),
                            'finished_matches' => count(array_filter($group['events'], function ($e) {
                                return in_array($e['event_status'] ?? '', ['Finished', 'Completed']);
                            })),
                            'events' => $group['events']
                        ];
                    }
                }
                return $series;
            } catch (\Exception $e) {
                Log::error('Exception while fetching series', ['error' => $e->getMessage()]);
                return [];
            }
        });
    }

    /**
     * Get series with detailed results and standings
     */
    public function getSeriesWithResultsAndStandings($leagueKey = null, $dateStart = null, $dateStop = null)
    {
        $cacheKey = 'cricket_series_results_standings_' . ($leagueKey ?? 'all') . '_' . ($dateStart ?? 'all') . '_' . ($dateStop ?? 'all');
        
        return Cache::remember($cacheKey, 1800, function () use ($leagueKey, $dateStart, $dateStop) { // Cache for 30 minutes
            try {
                // Get series data
                $series = $this->getSeries($leagueKey, $dateStart, $dateStop);
                
                if (empty($series)) {
                    return [];
                }
                
                // Enhance each series with results and standings
                foreach ($series as &$seriesData) {
                    $leagueKey = $seriesData['series_key'];
                    
                    // Get standings for this series
                    try {
                        $standings = $this->getStandings($leagueKey);
                        $seriesData['standings'] = $standings;
                    } catch (\Exception $e) {
                        $seriesData['standings'] = [];
                        Log::warning('Failed to fetch standings for series: ' . $leagueKey, ['error' => $e->getMessage()]);
                    }
                    
                    // Process events to get results and match details
                    $seriesData['results'] = [];
                    $seriesData['upcoming'] = [];
                    $seriesData['live'] = [];
                    
                    foreach ($seriesData['events'] as $event) {
                        $eventStatus = $event['event_status'] ?? '';
                        
                        if (in_array($eventStatus, ['Finished', 'Completed'])) {
                            $seriesData['results'][] = $event;
                        } elseif (in_array($eventStatus, ['Live', 'Started', 'In Progress'])) {
                            $seriesData['live'][] = $event;
                        } elseif (in_array($eventStatus, ['Scheduled', 'Not Started'])) {
                            $seriesData['upcoming'][] = $event;
                        }
                    }
                    
                    // Calculate series statistics
                    $seriesData['stats'] = [
                        'total_matches' => count($seriesData['events']),
                        'completed_matches' => count($seriesData['results']),
                        'live_matches' => count($seriesData['live']),
                        'upcoming_matches' => count($seriesData['upcoming']),
                        'win_percentage' => $this->calculateWinPercentage($seriesData['results']),
                        'series_progress' => $this->calculateSeriesProgress($seriesData['events'])
                    ];
                }
                
                return $series;
            } catch (\Exception $e) {
                Log::error('Exception while fetching series with results and standings', ['error' => $e->getMessage()]);
                return [];
            }
        });
    }
    
    /**
     * Calculate win percentage for completed matches
     */
    private function calculateWinPercentage($results)
    {
        if (empty($results)) return 0;
        
        $totalMatches = count($results);
        $decidedMatches = 0;
        
        foreach ($results as $match) {
            // Check if match has a clear winner (not tied or abandoned)
            if (
                isset($match['event_final_result']) &&
                !in_array(strtolower($match['event_final_result']), ['tie', 'draw', 'abandoned', 'no result'])
            ) {
                $decidedMatches++;
            }
        }
        
        return $decidedMatches > 0 ? round(($decidedMatches / $totalMatches) * 100, 1) : 0;
    }
    
    /**
     * Calculate series progress percentage
     */
    private function calculateSeriesProgress($events)
    {
        if (empty($events)) return 0;
        
        $totalMatches = count($events);
        $completedMatches = 0;
        
        foreach ($events as $event) {
            if (in_array($event['event_status'] ?? '', ['Finished', 'Completed'])) {
                $completedMatches++;
            }
        }
        
        return $totalMatches > 0 ? round(($completedMatches / $totalMatches) * 100, 1) : 0;
    }

    /**
     * Get detailed scorecard for a specific match
     */
    public function getScorecard($eventKey)
    {
        $cacheKey = 'cricket_scorecard_' . $eventKey;
        
        return Cache::remember($cacheKey, 300, function () use ($eventKey) { // Cache for 5 minutes
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
        
        return Cache::remember($cacheKey, 300, function () use ($eventKey) { // Cache for 5 minutes
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
        
        return Cache::remember($cacheKey, 300, function () use ($eventKey) { // Cache for 5 minutes
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
        
        return Cache::remember($cacheKey, 300, function () use ($eventKey) { // Cache for 5 minutes
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
     * Filter data to show only men's cricket (exclude women's cricket)
     */
    public function filterMensCricket($data, $type = 'general')
    {
        if (empty($data)) {
            return [];
        }

        switch ($type) {
            case 'matches':
            case 'events':
                return $this->filterMensMatches($data);
            case 'teams':
                return $this->filterMensTeams($data);
            case 'leagues':
                return $this->filterMensLeagues($data);
            case 'series':
                return $this->filterMensSeries($data);
            default:
                return $this->filterMensGeneral($data);
        }
    }

    /**
     * Filter matches to exclude women's cricket
     */
    private function filterMensMatches($matches)
    {
        if (!is_array($matches)) {
            return [];
        }

        return array_filter($matches, function ($match) {
            // Check if team names contain "Women" or "women"
            $homeTeam = $match['event_home_team'] ?? '';
            $awayTeam = $match['event_away_team'] ?? '';
            $leagueName = $match['league_name'] ?? '';
            
            // Exclude if any team name contains "Women" or "women"
            if (
                stripos($homeTeam, 'women') !== false ||
                stripos($awayTeam, 'women') !== false ||
                stripos($leagueName, 'women') !== false
            ) {
                return false;
            }
            
            return true;
        });
    }

    /**
     * Filter teams to exclude women's teams
     */
    private function filterMensTeams($teams)
    {
        if (!is_array($teams)) {
            return [];
        }

        return array_filter($teams, function ($team) {
            $teamName = $team['team_name'] ?? '';
            
            // Exclude if team name contains "Women" or "women"
            return stripos($teamName, 'women') === false;
        });
    }

    /**
     * Filter leagues to exclude women's leagues
     */
    private function filterMensLeagues($leagues)
    {
        if (!is_array($leagues)) {
            return [];
        }

        return array_filter($leagues, function ($league) {
            $leagueName = $league['league_name'] ?? '';
            
            // Exclude if league name contains "Women" or "women"
            return stripos($leagueName, 'women') === false;
        });
    }

    /**
     * Filter series to exclude women's series
     */
    private function filterMensSeries($series)
    {
        if (!is_array($series)) {
            return [];
        }

        // Log the filtering process for debugging
        Log::info('Filtering series data', [
            'total_series_before_filter' => count($series),
            'sample_series_keys' => array_keys($series[0] ?? []),
            'sample_series_name' => $series[0]['series_name'] ?? 'Not found',
            'sample_league_name' => $series[0]['league_name'] ?? 'Not found'
        ]);

        $filtered = array_filter($series, function ($seriesItem) {
            // Check multiple possible fields for series/league names
            $seriesName = $seriesItem['series_name'] ?? '';
            $leagueName = $seriesItem['league_name'] ?? '';
            
            // Exclude if any name contains "Women" or "women"
            if (stripos($seriesName, 'women') !== false || stripos($leagueName, 'women') !== false) {
                return false;
            }
            
            return true;
        });

        Log::info('Series filtering result', [
            'total_series_after_filter' => count($filtered)
        ]);

        return $filtered;
    }

    /**
     * General filter for any data type
     */
    private function filterMensGeneral($data)
    {
        if (!is_array($data)) {
            return [];
        }

        return array_filter($data, function ($item) {
            // Check common fields that might contain team or league names
            $fieldsToCheck = ['event_home_team', 'event_away_team', 'league_name', 'team_name'];
            
            foreach ($fieldsToCheck as $field) {
                if (isset($item[$field]) && stripos($item[$field], 'women') !== false) {
                    return false;
                }
            }
            
            return true;
        });
    }

    /**
     * Get men's cricket live scores
     */
    public function getMensLiveScores($leagueKey = null, $matchKey = null)
    {
        $liveScores = $this->getLiveScores($leagueKey, $matchKey);
        return $this->filterMensCricket($liveScores, 'matches');
    }

    /**
     * Get men's cricket events
     */
    public function getMensEvents($dateStart = null, $dateStop = null, $leagueKey = null, $eventKey = null)
    {
        $events = $this->getEvents($dateStart, $dateStop, $leagueKey, $eventKey);
        return $this->filterMensCricket($events, 'events');
    }

    /**
     * Get men's cricket teams
     */
    public function getMensTeams($teamKey = null, $leagueKey = null)
    {
        $teams = $this->getTeams($teamKey, $leagueKey);
        return $this->filterMensCricket($teams, 'teams');
    }

    /**
     * Get men's cricket leagues
     */
    public function getMensLeagues()
    {
        $leagues = $this->getLeagues();
        return $this->filterMensCricket($leagues, 'leagues');
    }

    /**
     * Get men's cricket series
     */
    public function getMensSeries($leagueKey = null, $dateStart = null, $dateStop = null)
    {
        $series = $this->getSeries($leagueKey, $dateStart, $dateStop);
        return $this->filterMensCricket($series, 'series');
    }

    /**
     * Get men's cricket series with results and standings
     */
    public function getMensSeriesWithResultsAndStandings($leagueKey = null, $dateStart = null, $dateStop = null)
    {
        $series = $this->getSeriesWithResultsAndStandings($leagueKey, $dateStart, $dateStop);
        
        // Log the raw series data before filtering
        Log::info('Raw series data before filtering', [
            'total_series' => count($series),
            'sample_series' => $series[0] ?? 'No series found'
        ]);
        
        // Temporarily bypass filtering to debug the issue
        $filtered = $this->filterMensCricket($series, 'series');
        
        Log::info('Series data after filtering', [
            'total_series' => count($filtered),
            'filtered_sample' => $filtered[0] ?? 'No filtered series found'
        ]);
        
        return $filtered;
    }
    
    /**
     * Get raw series data without filtering (for debugging)
     */
    public function getRawSeriesWithResultsAndStandings($leagueKey = null, $dateStart = null, $dateStop = null)
    {
        return $this->getSeriesWithResultsAndStandings($leagueKey, $dateStart, $dateStop);
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
                    'X-RapidAPI-Host' => 'cricbuzz-cricket.p.rapidapi.com'
                ])
                ->get($this->baseUrl . $endpoint);
            
            if ($response->successful()) {
                return $response->json();
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
                    'X-RapidAPI-Host' => 'cricbuzz-cricket.p.rapidapi.com'
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
                    'X-RapidAPI-Host' => 'cricbuzz-cricket.p.rapidapi.com'
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
