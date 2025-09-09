<?php

namespace App\Http\Controllers;

use App\Services\CricketApiService;
use App\Services\NewsService;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log; // Added for logging
use Illuminate\Support\Facades\Http; // Added for direct API testing

class CricketController extends Controller
{
    protected $cricketApi;
    protected $newsService;

    public function __construct(CricketApiService $cricketApi, NewsService $newsService)
    {
        $this->cricketApi = $cricketApi;
        $this->newsService = $newsService;
    }
    
        public function index(Request $request)
    {
        try {
            // Use the new CricketDataService instead of direct API calls
            $cricketData = app(\App\Services\CricketDataService::class);
            $homePageData = $cricketData->getHomePageData();
            
            $liveMatches = $homePageData['liveMatches'];
            $todayMatches = $homePageData['todayMatches'];
            $upcomingMatches = $homePageData['upcomingMatches'];
            $recentCompletedMatches = $homePageData['recentCompletedMatches'];
            // Filter out cancelled matches from live matches
            $liveMatches = $this->filterCancelledMatches($liveMatches);
            $todayMatches = $this->filterCancelledMatches($todayMatches);
            
            // Apply search filters if provided
            if ($request->filled('search')) {
                $liveMatches = $this->filterMatches($liveMatches, $request);
                $todayMatches = $this->filterMatches($todayMatches, $request);
                $upcomingMatches = $this->filterMatches($upcomingMatches, $request);
                $recentCompletedMatches = $this->filterMatches($recentCompletedMatches, $request);
            }
            
            // Process today's matches to fix template placeholders
            $todayMatches = $this->processTodayMatches($todayMatches);
            
            // Fetch featured news
            $featuredNews = $this->newsService->getNews(5, 1);
            
            return view('cricket.index', compact(
                'liveMatches', 
                'todayMatches', 
                'upcomingMatches',
                'recentCompletedMatches',
                'featuredNews'
            ));
            
        } catch (\Exception $e) {
            Log::error('Error in cricket index', [
                'error' => $e->getMessage()
            ]);
            
            return view('cricket.index', [
                'liveMatches' => [],
                'todayMatches' => [],
                'upcomingMatches' => [],
                'recentCompletedMatches' => []
            ]);
        }
    }

    public function liveScores(Request $request)
    {
        try {
            Log::info('Fetching live scores using CricketDataService');
            
            // Use the new CricketDataService instead of direct API calls
            $cricketData = app(\App\Services\CricketDataService::class);
            $homePageData = $cricketData->getHomePageData();
            $liveMatches = $homePageData['liveMatches'];
            $todayMatches = $homePageData['todayMatches'];
            $upcomingMatches = $homePageData['upcomingMatches'];
            $recentCompletedMatches = $homePageData['recentCompletedMatches'];
            
            // Filter out cancelled matches from live matches
            $liveMatches = $this->filterCancelledMatches($liveMatches);
            $todayMatches = $this->filterCancelledMatches($todayMatches);
            
            // Apply search filters if provided
            if ($request->filled('search')) {
                $liveMatches = $this->filterMatches($liveMatches, $request);
                $todayMatches = $this->filterMatches($todayMatches, $request);
                $upcomingMatches = $this->filterMatches($upcomingMatches, $request);
                $recentCompletedMatches = $this->filterMatches($recentCompletedMatches, $request);
            }
            
            // Process matches to fix template placeholders
            if (!empty($todayMatches)) {
                $todayMatches = $this->processTodayMatches($todayMatches);
            }
            
            // Show only live matches
            $filteredLiveMatches = [];
            if (!empty($liveMatches)) {
                foreach ($liveMatches as $match) {
                    if (isset($match['isLive']) && $match['isLive'] === true && 
                        (!isset($match['event_status']) || strtolower($match['event_status']) != 'cancelled')) {
                        $filteredLiveMatches[] = $match;
                    }
                }
            }
            
            // Also check today's matches for live status
            if (!empty($todayMatches)) {
                foreach ($todayMatches as $match) {
                    if (isset($match['isLive']) && $match['isLive'] === true && 
                        (!isset($match['event_status']) || strtolower($match['event_status']) != 'cancelled')) {
                        // Check if already in filtered live matches
                        $exists = false;
                        foreach ($filteredLiveMatches as $liveMatch) {
                            if ($liveMatch['event_key'] == $match['event_key']) {
                                $exists = true;
                                break;
                            }
                        }
                        if (!$exists) {
                            $filteredLiveMatches[] = $match;
                        }
                    }
                }
            }
            
            \Log::info('Live scores processed successfully', [
                'live_matches_count' => count($filteredLiveMatches),
                'today_matches_count' => count($todayMatches),
                'upcoming_matches_count' => count($upcomingMatches),
                'api_response_has_data' => !empty($liveMatches)
            ]);
            
            return view('cricket.live-scores', compact(
                'filteredLiveMatches', 
                'todayMatches', 
                'upcomingMatches'
            ));
            
        } catch (\Exception $e) {
            \Log::error('Error in liveScores method', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return empty data on error
            return view('cricket.live-scores', [
                'filteredLiveMatches' => [],
                'todayMatches' => [],
                'upcomingMatches' => []
            ]);
        }
    }

    /**
     * Filter out cancelled matches from a list of matches
     */
    private function filterCancelledMatches($matches)
    {
        if (empty($matches)) return $matches;
        
        return array_filter($matches, function($match) {
            return !isset($match['event_status']) || 
                   strtolower($match['event_status']) != 'cancelled';
        });
    }

    public function filterMatches($matches, Request $request)
    {
        $filteredMatches = $matches;
        
        // Filter by league
        if ($request->filled('league')) {
            $filteredMatches = array_filter($filteredMatches, function($match) use ($request) {
                return $match['league_name'] === $request->league;
            });
        }
        
        // Filter by team
        if ($request->filled('team')) {
            $filteredMatches = array_filter($filteredMatches, function($match) use ($request) {
                return stripos($match['event_home_team'], $request->team) !== false ||
                       stripos($match['event_away_team'], $request->team) !== false;
            });
        }
        
        // Filter by match type
        if ($request->filled('match_type')) {
            $filteredMatches = array_filter($filteredMatches, function($match) use ($request) {
                return $match['event_type'] === $request->match_type;
            });
        }
        
        // Filter by date range
        if ($request->filled('date_from')) {
            $filteredMatches = array_filter($filteredMatches, function($match) use ($request) {
                return $match['event_date_start'] >= $request->date_from;
            });
        }
        
        if ($request->filled('date_to')) {
            $filteredMatches = array_filter($filteredMatches, function($match) use ($request) {
                return $match['event_date_start'] <= $request->date_to;
            });
        }
        
        // Search query
        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $filteredMatches = array_filter($filteredMatches, function($match) use ($search) {
                return stripos($match['event_home_team'], $search) !== false ||
                       stripos($match['event_away_team'], $search) !== false ||
                       stripos($match['league_name'], $search) !== false ||
                       stripos($match['event_stadium'], $search) !== false;
            });
        }
        
        return array_values($filteredMatches);
    }

    public function matchDetail($eventKey)
    {
        try {
            \Log::info('Fetching match details', ['event_key' => $eventKey]);
            
            // Get comprehensive match data using the new CricketDataService
            $cricketData = app(\App\Services\CricketDataService::class);
            $match = $cricketData->getComprehensiveMatchDetails($eventKey);
        
            if (empty($match)) {
                Log::warning('Match not found', ['event_key' => $eventKey]);
                abort(404, 'Match not found');
            }
        
            // Use the comprehensive data directly
            $matchData = $match;
            // Get live score updates if match is live
            $liveScore = null;
            if (in_array(strtolower($match['event_status'] ?? ''), ['live', 'started', 'in progress'])) {
                try {
                    $liveScore = $this->cricketApi->getLiveScores(null, $eventKey);

                    if (!empty($liveScore)) {
                        $matchData['live_score'] = $liveScore[0] ?? null;
                    }
                } catch (\Exception $e) {
                    \Log::warning('Could not fetch live score', ['error' => $e->getMessage()]);
                }
            }
            
            // Get match odds if available
            try {
                $odds = $this->cricketApi->getOdds(null, null, null, $eventKey);
                if (!empty($odds)) {
                    $matchData['odds'] = $odds;
                }
            } catch (\Exception $e) {
                \Log::warning('Could not fetch match odds', ['error' => $e->getMessage()]);
            }
            
            // Get head-to-head data if both teams are available
            if (isset($match['home_team_key']) && isset($match['away_team_key'])) {
                try {
                    $h2h = $this->cricketApi->getH2H($match['home_team_key'], $match['away_team_key']);
                    if (!empty($h2h)) {
                        $matchData['head_to_head'] = $h2h;
                    }
                } catch (\Exception $e) {
                    \Log::warning('Could not fetch head-to-head data', ['error' => $e->getMessage()]);
                }
            }
            
            \Log::info('Match details prepared successfully', [
                'event_key' => $eventKey,
                'match_name' => $match['event_home_team'] ?? 'Unknown' . ' vs ' . $match['event_away_team'] ?? 'Unknown',
                'status' => $match['event_status'] ?? 'Unknown',
                'has_scorecard' => !empty($matchData['scorecard']),
                'has_commentary' => !empty($matchData['commentary']),
                'has_lineups' => !empty($matchData['lineups']),
                'has_statistics' => !empty($matchData['statistics'])
            ]);
        
        return view('cricket.match-detail', compact('match', 'matchData'));
            
        } catch (\Exception $e) {
            \Log::error('Error in matchDetail method', [
                'event_key' => $eventKey,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            abort(500, 'Error loading match details');
        }
    }

    public function fixtures(Request $request)
    {
        // Debug: Log the date range being used
        $dateStart = null;
        $dateEnd = null;
 
        // Use the new CricketDataService instead of direct API calls
        $cricketData = app(\App\Services\CricketDataService::class);
        $upcomingMatches = $cricketData->getFixturesData($dateStart, $dateEnd);
        
        // Ensure we have valid data
        if (!is_array($upcomingMatches)) {
            \Log::error('Fixtures: API returned invalid data type', [
                'type' => gettype($upcomingMatches),
                'value' => $upcomingMatches
            ]);
            $upcomingMatches = [];
        }
        
        // Debug: Log the raw response
        \Log::info('Fixtures: Raw API response', [
            'total_matches' => count($upcomingMatches),
            'sample_match' => $upcomingMatches[0] ?? 'No matches found',
            'data_type' => gettype($upcomingMatches)
        ]);
        
        // Apply filters
        $upcomingMatches = $this->filterMatches($upcomingMatches, $request);
        
        
        // Pagination
        $perPage = 24; // Increased to 24 matches per page
        $currentPage = $request->get('page', 1);
        $totalMatches = count($upcomingMatches);
        $totalPages = ceil($totalMatches / $perPage);
        
        // Get paginated matches
        $paginatedMatches = array_slice($upcomingMatches, ($currentPage - 1) * $perPage, $perPage);
        
        // Get unique leagues, teams, and formats for filters (from all matches, not just current page)
        $leagues = array_unique(array_column($upcomingMatches, 'league_name'));
        $teams = array_unique(array_merge(
            array_column($upcomingMatches, 'event_home_team'),
            array_column($upcomingMatches, 'event_away_team')
        ));
        $formats = array_unique(array_filter(array_column($upcomingMatches, 'matchFormat')));
        
        return view('cricket.fixtures', compact(
            'upcomingMatches', // Send all matches for counting and filtering
            'paginatedMatches', // Send paginated matches for display
            'currentPage', 
            'totalPages', 
            'totalMatches',
            'leagues',
            'teams',
            'formats'
        ));
    }

    public function results()
    {
        // Use the new CricketDataService instead of direct API calls
        $cricketData = app(\App\Services\CricketDataService::class);
        $finishedMatches = $cricketData->getResultsData(30);
        
        return view('cricket.results', compact('finishedMatches'));
    }


    public function teams()
    {
        // Get teams from database first
        $teams = Team::orderBy('team_name')->get();
        
        // If no teams in database, try to get from API and sync
        if ($teams->isEmpty()) {
            try {
                $apiTeams = $this->cricketApi->getTeams();
                if (!empty($apiTeams)) {
                    // Store teams in database for future use
                    foreach ($apiTeams as $apiTeam) {
                        if (isset($apiTeam['team_key']) && isset($apiTeam['team_name'])) {
                            Team::updateOrCreate(
                                ['team_key' => $apiTeam['team_key']],
                                [
                                    'team_name' => $apiTeam['team_name'],
                                    'team_logo' => $apiTeam['team_logo'] ?? null,
                                    'cached_at' => now()
                                ]
                            );
                        }
                    }
                    $teams = Team::orderBy('team_name')->get();
                }
            } catch (\Exception $e) {
                Log::error('Error fetching teams for teams page', [
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // Sort teams to prioritize international teams
        $teams = $teams->sortBy(function($team) {
            $teamName = strtolower($team->team_name);
            
            // Priority 1: Major international teams (score 100)
            if (in_array($teamName, [
                'england', 'australia', 'india', 'pakistan', 'south africa', 
                'west indies', 'new zealand', 'sri lanka', 'bangladesh', 
                'afghanistan', 'ireland', 'zimbabwe'
            ])) {
                return 100;
            }
            
            // Priority 2: Other international teams (score 50)
            if (in_array($teamName, [
                'netherlands', 'scotland', 'oman', 'uae', 'namibia', 'papua new guinea',
                'kenya', 'canada', 'bermuda', 'hong kong', 'singapore', 'malaysia'
            ])) {
                return 50;
            }
            
            // Priority 3: Domestic teams (score 0)
            return 0;
        })->reverse(); // Reverse to show highest priority first
        
        return view('cricket.teams', compact('teams'));
    }


    public function search(Request $request)
    {
        $query = $request->get('q');
        
        if (!$query) {
            return redirect()->route('cricket.index');
        }
        
        $results = [];
        
        // Search in teams
        $teams = $this->cricketApi->getTeams();
        $teamResults = array_filter($teams, function($team) use ($query) {
            return stripos($team['team_name'], $query) !== false;
        });
        
        // Search in matches using the new CricketDataService
        $cricketData = app(\App\Services\CricketDataService::class);
        $recentMatches = $cricketData->getRecentCompletedMatches(90);
        $matchResults = array_filter($recentMatches, function($match) use ($query) {
            return stripos($match['event_home_team'], $query) !== false ||
                   stripos($match['event_away_team'], $query) !== false ||
                   stripos($match['league_name'], $query) !== false;
        });
        
        $results = [
            'teams' => $teamResults,
            'matches' => $matchResults
        ];
        
        return view('cricket.search', compact('query', 'results'));
    }

    public function refreshData()
    {
        // Clear cache to force refresh
        $this->cricketApi->clearCache();
        
        return redirect()->back()->with('success', 'Data refreshed successfully!');
    }

    /**
     * Enable mock data for testing
     */
    public function enableMock()
    {
        try {
            // Get the CricketDataService instance
            $cricketData = app(\App\Services\CricketDataService::class);
            $cricketData->useMockData(true);
            
            return redirect()->back()->with('success', 'Mock data enabled successfully!');
            
        } catch (\Exception $e) {
            Log::error('Error enabling mock data', [
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Failed to enable mock data');
        }
    }

    /**
     * Disable mock data
     */
    public function disableMock()
    {
        try {
            // Get the CricketDataService instance
            $cricketData = app(\App\Services\CricketDataService::class);
            $cricketData->useMockData(false);
            
            return redirect()->back()->with('success', 'Mock data disabled successfully!');
            
        } catch (\Exception $e) {
            Log::error('Error disabling mock data', [
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Failed to disable mock data');
        }
    }

    /**
     * Get comprehensive match data including scorecard, commentary, lineups, etc.
     */
    private function getComprehensiveMatchData($match)
    {
        $matchData = [
            'scorecard' => [],
            'commentary' => [],
            'lineups' => [],
            'statistics' => [],
            'highlights' => [],
            'partnerships' => [],
            'fall_of_wickets' => [],
            'extras' => [],
            'powerplay' => [],
            'match_summary' => [],
            'ball_by_ball' => [],
            'partnerships' => [],
            'milestones' => [],
            'match_flow' => []
        ];

        try {
            if (isset($match['event_key'])) {
                $eventKey = $match['event_key'];
                
                // Get detailed scorecard data with batting and bowling details
                try {
                    $scorecardData = $this->cricketApi->getScorecard($eventKey);
                if (!empty($scorecardData)) {
                    $matchData['scorecard'] = $scorecardData;
                        
                        // Extract batting and bowling details
                        $matchData['batting_details'] = $this->extractBattingDetails($scorecardData);
                        $matchData['bowling_details'] = $this->extractBowlingDetails($scorecardData);
                        $matchData['fall_of_wickets'] = $this->extractFallOfWickets($scorecardData);
                    }
                } catch (\Exception $e) {
                    \Log::warning('Could not fetch scorecard', ['error' => $e->getMessage()]);
                }

                // Get ball-by-ball commentary data
                try {
                    $commentaryData = $this->cricketApi->getCommentary($eventKey);
                if (!empty($commentaryData)) {
                    $matchData['commentary'] = $commentaryData;
                        $matchData['ball_by_ball'] = $this->processBallByBallCommentary($commentaryData);
                        $matchData['match_flow'] = $this->generateMatchFlow($commentaryData);
                    }
                } catch (\Exception $e) {
                    \Log::warning('Could not fetch commentary', ['error' => $e->getMessage()]);
                }

                // Get detailed lineups with player information
                try {
                    $lineupsData = $this->cricketApi->getLineups($eventKey);
                if (!empty($lineupsData)) {
                    $matchData['lineups'] = $lineupsData;
                        $matchData['playing_xi'] = $this->extractPlayingXI($lineupsData);
                        $matchData['substitutes'] = $this->extractSubstitutes($lineupsData);
                    }
                } catch (\Exception $e) {
                    \Log::warning('Could not fetch lineups', ['error' => $e->getMessage()]);
                }

                // Get comprehensive match statistics
                try {
                    $statsData = $this->cricketApi->getMatchStatistics($eventKey);
                if (!empty($statsData)) {
                    $matchData['statistics'] = $statsData;
                        $matchData['key_stats'] = $this->extractKeyStatistics($statsData);
                        $matchData['partnerships'] = $this->extractPartnerships($statsData);
                        $matchData['milestones'] = $this->extractMilestones($statsData);
                    }
                } catch (\Exception $e) {
                    \Log::warning('Could not fetch statistics', ['error' => $e->getMessage()]);
                }

                // Extract extras and powerplay information
                if (isset($match['extra'])) {
                    $matchData['extras'] = $this->extractExtras($match['extra']);
                }

                // Extract wicket information
                if (isset($match['wickets'])) {
                    $matchData['wicket_details'] = $this->extractWicketDetails($match['wickets']);
                }
            }

            // Generate comprehensive match summary
            $matchData['match_summary'] = $this->generateMatchSummary($match, $matchData);

        } catch (\Exception $e) {
            \Log::error('Error fetching comprehensive match data', [
                'event_key' => $match['event_key'] ?? 'unknown',
                'error' => $e->getMessage()
            ]);
        }

        return $matchData;
    }

    /**
     * Extract batting details from scorecard
     */
    private function extractBattingDetails($scorecard)
    {
        $batting = [];
        
        if (empty($scorecard)) {
            return $batting;
        }
        
        foreach ($scorecard as $innings => $players) {
            if (is_array($players)) {
                foreach ($players as $player) {
                    if (isset($player['type']) && $player['type'] === 'Batsman') {
                        $batting[$innings][] = [
                            'player' => $player['player'] ?? 'Unknown',
                            'status' => $player['status'] ?? 'Not out',
                            'runs' => $player['R'] ?? 0,
                            'balls' => $player['B'] ?? 0,
                            'fours' => $player['4s'] ?? 0,
                            'sixes' => $player['6s'] ?? 0,
                            'strike_rate' => $player['SR'] ?? 0,
                            'minutes' => $player['Min'] ?? 0
                        ];
                    }
                }
            }
        }
        
        return $batting;
    }

    /**
     * Extract bowling details from scorecard
     */
    private function extractBowlingDetails($scorecard)
    {
        $bowling = [];
        
        if (empty($scorecard)) {
            return $bowling;
        }
        
        foreach ($scorecard as $innings => $players) {
            if (is_array($players)) {
                foreach ($players as $player) {
                    if (isset($player['type']) && $player['type'] === 'Bowler') {
                        $bowling[$innings][] = [
                            'player' => $player['player'] ?? 'Unknown',
                            'overs' => $player['O'] ?? 0,
                            'maidens' => $player['M'] ?? 0,
                            'runs' => $player['R'] ?? 0,
                            'wickets' => $player['W'] ?? 0,
                            'economy' => $player['ER'] ?? 0
                        ];
                    }
                }
            }
        }
        
        return $bowling;
    }

    /**
     * Extract fall of wickets from scorecard
     */
    private function extractFallOfWickets($scorecard)
    {
        $fallOfWickets = [];
        
        if (empty($scorecard)) {
            return $fallOfWickets;
        }
        
        foreach ($scorecard as $innings => $players) {
            if (is_array($players)) {
                foreach ($players as $player) {
                    if (isset($player['type']) && $player['type'] === 'Batsman' && 
                        isset($player['status']) && $player['status'] !== 'Not out') {
                        $fallOfWickets[$innings][] = [
                            'player' => $player['player'] ?? 'Unknown',
                            'score' => $player['R'] ?? 0,
                            'status' => $player['status'] ?? 'Unknown'
                        ];
                    }
                }
            }
        }
        
        return $fallOfWickets;
    }

    /**
     * Process ball-by-ball commentary
     */
    private function processBallByBallCommentary($commentary)
    {
        $ballByBall = [];
        
        if (empty($commentary)) {
            return $ballByBall;
        }
        
        foreach ($commentary as $innings => $balls) {
            if (is_array($balls)) {
                foreach ($balls as $ball) {
                    $ballByBall[$innings][] = [
                        'ball' => $ball['balls'] ?? 0,
                        'over' => $ball['overs'] ?? 0,
                        'runs' => $ball['runs'] ?? 0,
                        'commentary' => $ball['post'] ?? '',
                        'ended' => $ball['ended'] ?? 'No'
                    ];
                }
            }
        }
        
        return $ballByBall;
    }

    /**
     * Generate match flow from commentary
     */
    private function generateMatchFlow($commentary)
    {
        $matchFlow = [];
        
        if (empty($commentary)) {
            return $matchFlow;
        }
        
        foreach ($commentary as $innings => $balls) {
            if (is_array($balls)) {
                $inningsFlow = [];
                foreach ($balls as $ball) {
                    $inningsFlow[] = [
                        'over' => $ball['overs'] ?? 0,
                        'ball' => $ball['balls'] ?? 0,
                        'description' => $ball['post'] ?? '',
                        'runs' => $ball['runs'] ?? 0
                    ];
                }
                $matchFlow[$innings] = $inningsFlow;
            }
        }
        
        return $matchFlow;
    }

    /**
     * Extract playing XI from lineups
     */
    private function extractPlayingXI($lineups)
    {
        $playingXI = [];
        
        if (empty($lineups)) {
            return $playingXI;
        }
        
        foreach ($lineups as $team => $players) {
            if (isset($players['starting_lineups']) && is_array($players['starting_lineups'])) {
                $playingXI[$team] = array_map(function($player) {
                    return $player['player'] ?? 'Unknown';
                }, $players['starting_lineups']);
            }
        }
        
        return $playingXI;
    }

    /**
     * Extract substitutes from lineups
     */
    private function extractSubstitutes($lineups)
    {
        $substitutes = [];
        
        if (empty($lineups)) {
            return $substitutes;
        }
        
        foreach ($lineups as $team => $players) {
            if (isset($players['substitutes']) && is_array($players['substitutes'])) {
                $substitutes[$team] = array_map(function($player) {
                    return $player['player'] ?? 'Unknown';
                }, $players['substitutes']);
            }
        }
        
        return $substitutes;
    }

    /**
     * Extract key statistics
     */
    private function extractKeyStatistics($statistics)
    {
        $keyStats = [];
        
        if (empty($statistics)) {
            return $keyStats;
        }
        
        // Extract relevant statistics based on available data structure
        foreach ($statistics as $key => $value) {
            if (is_array($value)) {
                $keyStats[$key] = $value;
            } else {
                $keyStats[$key] = $value;
            }
        }
        
        return $keyStats;
    }

    /**
     * Extract partnerships
     */
    private function extractPartnerships($statistics)
    {
        $partnerships = [];
        
        if (empty($statistics)) {
            return $partnerships;
        }
        
        // Extract partnership data if available
        if (isset($statistics['partnerships'])) {
            $partnerships = $statistics['partnerships'];
        }
        
        return $partnerships;
    }

    /**
     * Extract milestones
     */
    private function extractMilestones($statistics)
    {
        $milestones = [];
        
        if (empty($statistics)) {
            return $milestones;
        }
        
        // Extract milestone data if available
        if (isset($statistics['milestones'])) {
            $milestones = $statistics['milestones'];
        }
        
        return $milestones;
    }

    /**
     * Extract extras information
     */
    private function extractExtras($extras)
    {
        $extrasData = [];
        
        if (empty($extras)) {
            return $extrasData;
        }
        
        foreach ($extras as $innings => $extra) {
            $extrasData[$innings] = [
                'no_balls' => $extra['nb'] ?? 0,
                'wides' => $extra['w'] ?? 0,
                'byes' => $extra['b'] ?? 0,
                'leg_byes' => $extra['lb'] ?? 0,
                'penalties' => $extra['pen'] ?? 0,
                'total' => $extra['total'] ?? 0
            ];
        }
        
        return $extrasData;
    }

    /**
     * Extract wicket details
     */
    private function extractWicketDetails($wickets)
    {
        $wicketDetails = [];
        
        if (empty($wickets)) {
            return $wicketDetails;
        }
        
        foreach ($wickets as $innings => $wicketList) {
            if (is_array($wicketList)) {
                foreach ($wicketList as $wicket) {
                    $wicketDetails[$innings][] = [
                        'fall' => $wicket['fall'] ?? '',
                        'bowler' => $wicket['bowler'] ?? '',
                        'batsman' => $wicket['batsman'] ?? '',
                        'score' => $wicket['score'] ?? ''
                    ];
                }
            }
        }
        
        return $wicketDetails;
    }

    /**
     * Generate comprehensive match summary
     */
    private function generateMatchSummary($match, $matchData)
    {
        $summary = [
            'toss' => $match['event_toss'] ?? 'Not available',
            'venue' => $match['event_stadium'] ?? 'TBD',
            'date' => $match['event_date_start'] ?? 'TBD',
            'time' => $match['event_time'] ?? '',
            'status' => $match['event_status'] ?? 'Unknown',
            'result' => $match['event_final_result'] ?? 'Match in progress',
            'man_of_match' => $match['event_man_of_match'] ?? 'Not announced yet',
            'series' => $match['league_name'] ?? 'Unknown Series',
            'format' => $this->determineMatchFormat($match),
            'weather' => $match['event_weather'] ?? 'Not available',
            'umpires' => $match['event_umpires'] ?? 'Not available',
            'referee' => $match['event_referee'] ?? 'Not available',
            'tv_umpire' => $match['event_tv_umpire'] ?? 'Not available',
            'reserve_umpire' => $match['event_reserve_umpire'] ?? 'Not available'
        ];

        // Add match highlights if available
        if (!empty($matchData['highlights'])) {
            $summary['highlights'] = $matchData['highlights'];
        }

        // Add key statistics
        if (!empty($matchData['statistics'])) {
            $summary['key_stats'] = $matchData['statistics'];
        }

        return $summary;
    }

    /**
     * Determine match format based on available data
     */
    private function determineMatchFormat($match)
    {
        $leagueName = strtolower($match['league_name'] ?? '');
        
        if (strpos($leagueName, 'test') !== false) {
            return 'Test Match';
        } elseif (strpos($leagueName, 'odi') !== false || strpos($leagueName, 'one day') !== false) {
            return 'One Day International';
        } elseif (strpos($leagueName, 't20') !== false || strpos($leagueName, 'twenty20') !== false) {
            return 'T20 International';
        } elseif (strpos($leagueName, 'ipl') !== false) {
            return 'T20 League';
        } elseif (strpos($leagueName, 'psl') !== false) {
            return 'T20 League';
        } else {
            return 'Limited Overs';
        }
    }

    /**
     * Process today's matches to fix template placeholders in event_status_info
     */
    private function processTodayMatches($todayMatches)
    {
        if (empty($todayMatches)) return $todayMatches;
        
        foreach ($todayMatches as &$match) {
            if (isset($match['event_status_info']) && !empty($match['event_status_info'])) {
                $match['event_status_info'] = $this->processEventStatusInfo($match);
            }
        }
        
        return $todayMatches;
    }
    
    /**
     * Process event status info to replace template placeholders with actual values
     */
    private function processEventStatusInfo($match)
    {
        $statusInfo = $match['event_status_info'];
        
        // Check if it contains template placeholders
        if (strpos($statusInfo, '{{MATCH_START_HOURS}}') !== false || 
            strpos($statusInfo, '{{MATCH_START_MINS}}') !== false) {
            
            // Calculate time until match starts
            $matchDateTime = null;
            if (isset($match['event_date_start']) && isset($match['event_time'])) {
                $matchDateTime = \Carbon\Carbon::parse($match['event_date_start'] . ' ' . $match['event_time']);
            } elseif (isset($match['event_date_start'])) {
                $matchDateTime = \Carbon\Carbon::parse($match['event_date_start'] . ' 00:00:00');
            }
            
            if ($matchDateTime && $matchDateTime->isFuture()) {
                $now = \Carbon\Carbon::now();
                $diff = $now->diff($matchDateTime);
                
                $hours = $diff->h + ($diff->days * 24);
                $minutes = $diff->i;
                
                // Replace placeholders with actual values
                $statusInfo = str_replace('{{MATCH_START_HOURS}}', $hours, $statusInfo);
                $statusInfo = str_replace('{{MATCH_START_MINS}}', $minutes, $statusInfo);
                
                // Add "ago" or "until" context
                if ($hours > 0) {
                    $statusInfo = "Match starts in {$hours}h {$minutes}m";
            } else {
                    $statusInfo = "Match starts in {$minutes}m";
                }
            } else {
                // If match time has passed, show a different message
                $statusInfo = "Match scheduled for today";
            }
        }
        
        return $statusInfo;
    }

    
    /**
     * Live update endpoint for match details
     */
    public function liveUpdate($eventKey)
    {
        try {
            \Log::info('Fetching live update for match', ['event_key' => $eventKey]);
            
            // Get current match data using the new CricketDataService
            $cricketData = app(\App\Services\CricketDataService::class);
            $match = $cricketData->getMatchByKey($eventKey);
            
            if (empty($match)) {
                return response()->json(['error' => 'Match not found'], 404);
            }
            
            // Prepare live update data
            $liveData = [
                'scorecard' => $match['scorecard'] ?? [],
                'commentary' => $match['comments']['Live'] ?? [],
                'wickets' => $match['wickets'] ?? [],
                'current_over' => null,
                'current_score' => $match['event_home_final_result'] ?? '0/0',
                'run_rate' => $match['event_home_rr'] ?? null,
                'match_status' => $match['event_status'] ?? 'Unknown',
                'timestamp' => now()->timestamp
            ];
            
            // Extract current over from extras
            if (isset($match['extra'])) {
                foreach ($match['extra'] as $innings => $extra) {
                    if (isset($extra['total_overs'])) {
                        $liveData['current_over'] = $extra['total_overs'];
                        break;
                    }
                }
            }
            
            \Log::info('Live update data prepared', [
                'event_key' => $eventKey,
                'current_over' => $liveData['current_over'],
                'current_score' => $liveData['current_score'],
                'commentary_count' => count($liveData['commentary']),
                'wickets_count' => count($liveData['wickets'])
            ]);
            
            return response()->json($liveData);
            
        } catch (\Exception $e) {
            \Log::error('Error in liveUpdate method', [
                'event_key' => $eventKey,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['error' => 'Failed to fetch live update'], 500);
        }
    }

}
