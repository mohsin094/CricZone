<?php

namespace App\Http\Controllers;

use App\Services\CricketApiService;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log; // Added for logging
use Illuminate\Support\Facades\Http; // Added for direct API testing

class CricketController extends Controller
{
    protected $cricketApi;

    public function __construct(CricketApiService $cricketApi)
    {
        $this->cricketApi = $cricketApi;
    }
    
        /**
     * Get teams for navbar dropdown
     */
    public function getTeamsForNavbar()
    {
        // Get teams from database first
        $teams = Team::orderBy('team_name')->get();
        
        // If no teams in database, try to get from API
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
                Log::error('Error fetching teams for navbar', [
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        return $teams;
    }

    /**
     * Get teams by league
     */
    public function getTeamsByLeague($leagueKey)
    {
        try {
            $teams = $this->cricketApi->getTeamsByLeague($leagueKey);
            return response()->json([
                'success' => true,
                'teams' => $teams
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching teams by league', [
                'league_key' => $leagueKey,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error fetching teams: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get team details with matches
     */
    public function teamDetail($teamKey)
    {
        try {
            // Get team info
            $team = Team::where('team_key', $teamKey)->first();
            
            if (!$team) {
                abort(404, 'Team not found');
            }
            
            // Get team matches from API (same date range as fixtures page)
            $dateStart = now()->format('Y-m-d');
            $dateEnd = now()->addDays(30)->format('Y-m-d');
            
            $allMatches = $this->cricketApi->getEvents($dateStart, $dateEnd);
            
            // Filter matches for this team using team names (same as fixtures page)
            $teamMatches = collect($allMatches)->filter(function($match) use ($team) {
                $homeTeamName = $match['event_home_team'] ?? '';
                $awayTeamName = $match['event_away_team'] ?? '';
                $teamName = $team->team_name;
                
                // Try exact match first, then partial match
                $isHomeMatch = ($homeTeamName === $teamName) || (stripos($homeTeamName, $teamName) !== false);
                $isAwayMatch = ($awayTeamName === $teamName) || (stripos($awayTeamName, $teamName) !== false);
                
                return $isHomeMatch || $isAwayMatch;
            })->values();
            
            // Log for debugging
            \Log::info('Team detail matches found', [
                'team_key' => $teamKey,
                'team_name' => $team->team_name,
                'date_range' => [$dateStart, $dateEnd],
                'total_matches' => count($allMatches),
                'team_matches' => count($teamMatches),
                'sample_match' => $allMatches[0] ?? 'No matches',
                'sample_team_names' => [
                    'home' => $allMatches[0]['event_home_team'] ?? 'Not found',
                    'away' => $allMatches[0]['event_away_team'] ?? 'Not found'
                ] ?? 'No matches'
            ]);
            
            // Additional debugging: Check first few matches for team names
            if (count($allMatches) > 0) {
                $firstFewMatches = array_slice($allMatches, 0, 5);
                $teamNamesInMatches = [];
                foreach ($firstFewMatches as $index => $match) {
                    $teamNamesInMatches[] = [
                        'match_' . $index => [
                            'home' => $match['event_home_team'] ?? 'Not found',
                            'away' => $match['event_away_team'] ?? 'Not found',
                            'status' => $match['event_status'] ?? 'Not found'
                        ]
                    ];
                }
                \Log::info('Team detail: First few matches team names', $teamNamesInMatches);
                
                // Test team name matching logic
                \Log::info('Team detail: Team name matching test', [
                    'database_team_name' => $team->team_name,
                    'database_team_name_lower' => strtolower($team->team_name),
                    'sample_home_team' => $allMatches[0]['event_home_team'] ?? 'Not found',
                    'sample_home_team_lower' => strtolower($allMatches[0]['event_home_team'] ?? ''),
                    'sample_away_team' => $allMatches[0]['event_away_team'] ?? 'Not found',
                    'sample_away_team_lower' => strtolower($allMatches[0]['event_away_team'] ?? ''),
                    'exact_home_match' => ($allMatches[0]['event_home_team'] ?? '') === $team->team_name,
                    'exact_away_match' => ($allMatches[0]['event_away_team'] ?? '') === $team->team_name,
                    'partial_home_match' => stripos($allMatches[0]['event_home_team'] ?? '', $team->team_name) !== false,
                    'partial_away_match' => stripos($allMatches[0]['event_away_team'] ?? '', $team->team_name) !== false
                ]);
            }
            
            // Separate matches by status
            $liveMatches = $teamMatches->filter(function($match) {
                return ($match['event_status'] ?? '') === 'Live';
            })->values();
            
            $completedMatches = $teamMatches->filter(function($match) {
                return in_array($match['event_status'] ?? '', ['Completed', 'Finished']);
            })->values();
            
            $upcomingMatches = $teamMatches->filter(function($match) {
                return in_array($match['event_status'] ?? '', ['Scheduled', 'Not Started', '']);
            })->values();
            
            // Sort matches by date
            $completedMatches = $completedMatches->sortByDesc('event_date_start')->values();
            $upcomingMatches = $upcomingMatches->sortBy('event_date_start')->values();
            
            return view('cricket.team-detail', compact(
                'team',
                'liveMatches',
                'completedMatches',
                'upcomingMatches'
            ));
            
        } catch (\Exception $e) {
            Log::error('Error fetching team detail', [
                'team_key' => $teamKey,
                'error' => $e->getMessage()
            ]);
            
            abort(500, 'Error loading team details');
        }
    }

    public function index(Request $request)
    {
        $liveMatches = $this->cricketApi->getLiveScores();
        $todayMatches = $this->cricketApi->getEvents(now()->format('Y-m-d'), now()->format('Y-m-d'));
        $upcomingMatches = $this->cricketApi->getEvents(now()->addDay()->format('Y-m-d'), now()->addDays(30)->format('Y-m-d'));
        // Filter out cancelled matches from live matches
        $liveMatches = $this->filterCancelledMatches($liveMatches);
        $todayMatches = $this->filterCancelledMatches($todayMatches);
        // Apply search filters if provided
        if ($request->filled('search')) {
            $liveMatches = $this->filterMatches($liveMatches, $request);
            $todayMatches = $this->filterMatches($todayMatches, $request);
            $upcomingMatches = $this->filterMatches($upcomingMatches, $request);
        }
        
        // Process today's matches to fix template placeholders
        $todayMatches = $this->processTodayMatches($todayMatches);
        // Fetch leagues to identify international and famous leagues
        $leagues = $this->cricketApi->getLeagues();
        
        // Filter for international and famous leagues
        $internationalLeagues = $this->filterInternationalLeagues($leagues);
        $famousLeagues = $this->filterFamousLeagues($leagues);
        
        // Get featured series (ongoing international series)
        $featuredSeries = $this->getFeaturedSeries($internationalLeagues);
        
        // Get upcoming series (scheduled international series)
        $upcomingSeries = $this->getUpcomingSeries($internationalLeagues);
        
        // Get all current international series with comprehensive data
        $allCurrentSeries = $this->getAllCurrentInternationalSeries($internationalLeagues);
        
        // Get teams for international competitions
        $internationalTeams = $this->getInternationalTeams();
        // Debug information
        $debugInfo = [
            'current_date' => now()->format('Y-m-d H:i:s'),
            'timezone' => config('app.timezone'),
            'total_live_matches' => count($liveMatches),
            'total_today_matches' => count($todayMatches),
            'total_upcoming_matches' => count($upcomingMatches),
            'upcoming_date_range' => now()->addDay()->format('Y-m-d') . ' to ' . now()->addDays(30)->format('Y-m-d'),
            'total_leagues' => count($leagues),
            'total_international_leagues' => count($internationalLeagues),
            'total_featured_series' => count($featuredSeries),
            'total_upcoming_series' => count($upcomingSeries),
            'total_all_current_series' => count($allCurrentSeries)
        ];
        
        // Log upcoming matches info for debugging
        \Log::info('Home page upcoming matches', [
            'total_upcoming' => count($upcomingMatches),
            'date_range' => now()->addDay()->format('Y-m-d') . ' to ' . now()->addDays(30)->format('Y-m-d'),
            'sample_matches' => array_slice($upcomingMatches, 0, 3)
        ]);
        
        return view('cricket.index', compact(
            'liveMatches', 
            'todayMatches', 
            'upcomingMatches',
            'featuredSeries',
            'upcomingSeries',
            'allCurrentSeries',
            'internationalTeams',
            'internationalLeagues',
            'famousLeagues',
            'debugInfo'
        ));
    }

    public function liveScores(Request $request)
    {
        try {
            \Log::info('Fetching live scores using Cricket API livescore method');
            
            // Get live matches using the same method as the main page
        $liveMatches = $this->cricketApi->getLiveScores();
        
            // Get today's matches to show as well
            $todayMatches = $this->cricketApi->getEvents(now()->format('Y-m-d'), now()->format('Y-m-d'));
            
            // Filter out cancelled matches from live matches
            $liveMatches = $this->filterCancelledMatches($liveMatches);
            $todayMatches = $this->filterCancelledMatches($todayMatches);
            
            // Apply search filters if provided
            if ($request->filled('search')) {
                $liveMatches = $this->filterMatches($liveMatches, $request);
                $todayMatches = $this->filterMatches($todayMatches, $request);
            }
            
            // Get upcoming matches for tomorrow
            $tomorrow = now()->addDay()->format('Y-m-d');
            $upcomingMatches = $this->cricketApi->getEvents($tomorrow, $tomorrow);
            
            // Process matches to fix template placeholders
            if (!empty($todayMatches)) {
                $todayMatches = $this->processTodayMatches($todayMatches);
            }
            
            if (!empty($upcomingMatches)) {
                foreach ($upcomingMatches as &$match) {
                    if (isset($match['event_status_info']) && !empty($match['event_status_info'])) {
                        $match['event_status_info'] = $this->processEventStatusInfo($match);
                    }
                }
            }
            
            // Show only live matches
            $filteredLiveMatches = [];
            if (!empty($liveMatches)) {
                foreach ($liveMatches as $match) {
                    if (isset($match['event_live']) && $match['event_live'] == '1' && 
                        (!isset($match['event_status']) || strtolower($match['event_status']) != 'cancelled')) {
                        $filteredLiveMatches[] = $match;
                    }
                }
            }
            
            // Also check today's matches for live status
            if (!empty($todayMatches)) {
                foreach ($todayMatches as $match) {
                    if (isset($match['event_live']) && $match['event_live'] == '1' && 
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

    /**
     * Enrich live match data with additional information
     */
    private function enrichLiveMatchData($match)
    {
        $enrichedMatch = $match;
        
        // Add live match indicators
        $enrichedMatch['is_live'] = true;
        $enrichedMatch['live_status'] = $this->getLiveMatchStatus($match);
        $enrichedMatch['current_score'] = $this->getCurrentScore($match);
        $enrichedMatch['match_progress'] = $this->getMatchProgress($match);
        
        // Add live commentary preview if available
        if (isset($match['comments']['Live']) && !empty($match['comments']['Live'])) {
            $latestComment = end($match['comments']['Live']);
            $enrichedMatch['latest_commentary'] = $latestComment['post'] ?? '';
            $enrichedMatch['current_over'] = $latestComment['overs'] ?? '';
        }
        
        // Add scorecard summary if available
        if (isset($match['scorecard'])) {
            $enrichedMatch['scorecard_summary'] = $this->getScorecardSummary($match['scorecard']);
        }
        
        // Add extras summary if available
        if (isset($match['extra'])) {
            $enrichedMatch['extras_summary'] = $this->getExtrasSummary($match['extra']);
        }
        
        return $enrichedMatch;
    }

    /**
     * Get live match status description
     */
    private function getLiveMatchStatus($match)
    {
        $status = $match['event_status'] ?? 'Unknown';
        $statusInfo = $match['event_status_info'] ?? '';
        
        if (strpos(strtolower($status), 'stumps') !== false) {
            return 'Day ' . $this->extractDayNumber($statusInfo) . ' - ' . $statusInfo;
        } elseif (strpos(strtolower($status), 'lunch') !== false) {
            return 'Lunch Break - ' . $statusInfo;
        } elseif (strpos(strtolower($status), 'tea') !== false) {
            return 'Tea Break - ' . $statusInfo;
        } elseif (strpos(strtolower($status), 'innings') !== false) {
            return 'Innings Break - ' . $statusInfo;
        } else {
            return $statusInfo ?: $status;
        }
    }

    /**
     * Extract day number from status info
     */
    private function extractDayNumber($statusInfo)
    {
        if (preg_match('/Day (\d+)/', $statusInfo, $matches)) {
            return $matches[1];
        }
        return '1';
    }

    /**
     * Get current score display
     */
    private function getCurrentScore($match)
    {
        $homeScore = $match['event_service_home'] ?? $match['event_home_final_result'] ?? '0/0';
        $awayScore = $match['event_service_away'] ?? $match['event_away_final_result'] ?? '0/0';
        
        return [
            'home' => $homeScore,
            'away' => $awayScore,
            'home_rr' => $match['event_home_rr'] ?? null,
            'away_rr' => $match['event_away_rr'] ?? null
        ];
    }

    /**
     * Get match progress information
     */
    private function getMatchProgress($match)
    {
        $progress = [
            'type' => $match['event_type'] ?? 'Unknown',
            'toss' => $match['event_toss'] ?? 'Toss not available',
            'venue' => $match['event_stadium'] ?? 'Venue TBD',
            'series' => $match['league_name'] ?? 'Unknown Series',
            'season' => $match['league_season'] ?? 'Unknown Season'
        ];
        
        // Add match duration if available
        if (isset($match['event_date_start']) && isset($match['event_date_stop'])) {
            $startDate = \Carbon\Carbon::parse($match['event_date_start']);
            $endDate = \Carbon\Carbon::parse($match['event_date_stop']);
            $progress['duration'] = $startDate->diffInDays($endDate) + 1;
        }
        
        return $progress;
    }

    /**
     * Get scorecard summary
     */
    private function getScorecardSummary($scorecard)
    {
        $summary = [];
        
        foreach ($scorecard as $innings => $players) {
            if (is_array($players)) {
                $inningsSummary = [
                    'total_runs' => 0,
                    'total_wickets' => 0,
                    'total_overs' => 0,
                    'top_scorer' => null,
                    'top_scorer_runs' => 0
                ];
                
                foreach ($players as $player) {
                    if (isset($player['type']) && $player['type'] === 'Batsman') {
                        $runs = intval($player['R'] ?? 0);
                        $inningsSummary['total_runs'] += $runs;
                        
                        if ($runs > $inningsSummary['top_scorer_runs']) {
                            $inningsSummary['top_scorer'] = $player['player'];
                            $inningsSummary['top_scorer_runs'] = $runs;
                        }
                    } elseif (isset($player['type']) && $player['type'] === 'Bowler') {
                        $wickets = intval($player['W'] ?? 0);
                        $inningsSummary['total_wickets'] += $wickets;
                        $inningsSummary['total_overs'] = max($inningsSummary['total_overs'], floatval($player['O'] ?? 0));
                    }
                }
                
                $summary[$innings] = $inningsSummary;
            }
        }
        
        return $summary;
    }

    /**
     * Get extras summary
     */
    private function getExtrasSummary($extras)
    {
        $summary = [];
        
        foreach ($extras as $innings => $extra) {
            $summary[$innings] = [
                'no_balls' => floatval($extra['nr'] ?? 0),
                'wides' => floatval($extra['w'] ?? 0),
                'byes' => floatval($extra['b'] ?? 0),
                'leg_byes' => floatval($extra['lb'] ?? 0),
                'total_extras' => floatval($extra['total'] ?? 0),
                'total_overs' => $extra['total_overs'] ?? null,
                'text' => $extra['text'] ?? ''
            ];
        }
        
        return $summary;
    }

    /**
     * Get match priority for sorting (international matches get higher priority)
     */
    private function getMatchPriority($match)
    {
        $priority = 0;
        
        // International matches get higher priority
        if (isset($match['league_name'])) {
            $leagueName = strtolower($match['league_name']);
            
            if (strpos($leagueName, 'test') !== false || strpos($leagueName, 'odi') !== false || strpos($leagueName, 't20') !== false) {
                $priority += 100;
            }
            
            if (strpos($leagueName, 'world cup') !== false || strpos($leagueName, 'champions trophy') !== false) {
                $priority += 200;
            }
            
            if (strpos($leagueName, 'asia cup') !== false || strpos($leagueName, 'european') !== false) {
                $priority += 150;
            }
        }
        
        // Live matches get higher priority
        if (isset($match['event_live']) && $match['event_live'] == '1') {
            $priority += 50;
        }
        
        // Recent matches get higher priority
        if (isset($match['event_date_start'])) {
            $matchDate = strtotime($match['event_date_start']);
            $daysDiff = (time() - $matchDate) / (24 * 60 * 60);
            if ($daysDiff <= 1) {
                $priority += 25;
            }
        }
        
        return $priority;
    }

    public function matchDetail($eventKey)
    {
        try {
            \Log::info('Fetching match details', ['event_key' => $eventKey]);
            
            // Get detailed match data using proper API method
        $match = $this->cricketApi->getEvents(null, null, null, $eventKey);
        
        if (empty($match)) {
                \Log::warning('Match not found', ['event_key' => $eventKey]);
            abort(404, 'Match not found');
        }
        
        $match = $match[0]; // Get first (and only) match
        
            // Fetch comprehensive real-time match data
        $matchData = $this->getComprehensiveMatchData($match);
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
        $dateStart = now()->format('Y-m-d');
        $dateEnd = now()->addDays(30)->format('Y-m-d');
        
        \Log::info('Fixtures: Fetching matches', [
            'date_start' => $dateStart,
            'date_end' => $dateEnd,
            'current_time' => now()->format('Y-m-d H:i:s')
        ]);
        
        $upcomingMatches = $this->cricketApi->getEvents($dateStart, $dateEnd);
        
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
        
        // Debug: Log after filtering
        \Log::info('Fixtures: After filtering', [
            'total_matches_after_filter' => count($upcomingMatches),
            'sample_match_after_filter' => $upcomingMatches[0] ?? 'No matches after filter'
        ]);
        
        // Pagination
        $perPage = 24; // Increased to 24 matches per page
        $currentPage = $request->get('page', 1);
        $totalMatches = count($upcomingMatches);
        $totalPages = ceil($totalMatches / $perPage);
        
        // Get paginated matches
        $paginatedMatches = array_slice($upcomingMatches, ($currentPage - 1) * $perPage, $perPage);
        
        // Get unique leagues and teams for filters (from all matches, not just current page)
        $leagues = array_unique(array_column($upcomingMatches, 'league_name'));
        $teams = array_unique(array_merge(
            array_column($upcomingMatches, 'event_home_team'),
            array_column($upcomingMatches, 'event_away_team')
        ));
        
        // Debug: Final data being sent to view
        \Log::info('Fixtures: Final view data', [
            'total_matches_final' => count($paginatedMatches),
            'current_page' => $currentPage,
            'total_pages' => $totalPages,
            'total_matches_total' => $totalMatches,
            'leagues_count' => count($leagues),
            'teams_count' => count($teams)
        ]);
        
        return view('cricket.fixtures', compact(
            'upcomingMatches', // Send all matches for counting and filtering
            'paginatedMatches', // Send paginated matches for display
            'currentPage', 
            'totalPages', 
            'totalMatches',
            'leagues',
            'teams'
        ));
    }

    public function results()
    {
        $finishedMatches = $this->cricketApi->getEvents(now()->subDays(30)->format('Y-m-d'), now()->format('Y-m-d'));
        
        // Filter only finished matches
        $finishedMatches = array_filter($finishedMatches, function($match) {
            return $match['event_status'] === 'Finished';
        });
        
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

    /**
     * Sync teams from API to database
     */
    public function syncTeams()
    {
        try {
            $apiTeams = $this->cricketApi->getTeams();
            
            if (empty($apiTeams)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No teams found in API response'
                ]);
            }
            
            $created = 0;
            $updated = 0;
            
            foreach ($apiTeams as $apiTeam) {
                $teamKey = $apiTeam['team_key'] ?? null;
                $teamName = $apiTeam['team_name'] ?? '';
                $teamLogo = $apiTeam['team_logo'] ?? null;
                
                if (!$teamKey || !$teamName) {
                    continue;
                }
                
                $team = Team::updateOrCreate(
                    ['team_key' => $teamKey],
                    [
                        'team_name' => $teamName,
                        'team_logo' => $teamLogo,
                        'cached_at' => now()
                    ]
                );
                
                if ($team->wasRecentlyCreated) {
                    $created++;
                } else {
                    $updated++;
                }
            }
            
            $message = "Created: {$created}, Updated: {$updated}";
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'created' => $created,
                'updated' => $updated
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error syncing teams', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error syncing teams: ' . $e->getMessage()
            ]);
        }
    }

    public function leagues()
    {
        $leagues = $this->cricketApi->getLeagues();
        
        return view('cricket.leagues', compact('leagues'));
    }

    public function leagueDetail($leagueKey)
    {
        $league = null;
        $leagues = $this->cricketApi->getLeagues();
        
        foreach ($leagues as $l) {
            if ($l['league_key'] == $leagueKey) {
                $league = $l;
                break;
            }
        }
        
        if (!$league) {
            abort(404, 'League not found');
        }
        
        $matches = $this->cricketApi->getEvents(null, null, $leagueKey);
        $standings = $this->cricketApi->getStandings($leagueKey);
        
        return view('cricket.league-detail', compact('league', 'matches', 'standings'));
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
        
        // Search in matches
        $recentMatches = $this->cricketApi->getEvents(now()->subDays(90)->format('Y-m-d'), now()->format('Y-m-d'));
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
     * Debug method to check what data we're getting from the API
     */
    public function debug()
    {
        // Get raw data from API
        $leagues = $this->cricketApi->getLeagues();
        $series = $this->cricketApi->getSeriesWithResultsAndStandings();
        $liveScores = $this->cricketApi->getLiveScores();
        
        // Get raw events data for different date ranges
        $todayEvents = $this->cricketApi->getEvents(now()->format('Y-m-d'), now()->format('Y-m-d'));
        $weekEvents = $this->cricketApi->getEvents(now()->subDays(7)->format('Y-m-d'), now()->addDays(7)->format('Y-m-d'));
        $monthEvents = $this->cricketApi->getEvents(now()->subDays(30)->format('Y-m-d'), now()->addDays(30)->format('Y-m-d'));
        
        // Check for Pakistan vs West Indies specifically in events
        $pakWiEvents = [];
        foreach ($monthEvents as $event) {
            $homeTeam = strtolower($event['event_home_team'] ?? '');
            $awayTeam = strtolower($event['event_away_team'] ?? '');
            $leagueName = strtolower($event['league_name'] ?? '');
            
            if ((strpos($homeTeam, 'pakistan') !== false && strpos($awayTeam, 'west indies') !== false) ||
                (strpos($homeTeam, 'west indies') !== false && strpos($awayTeam, 'pakistan') !== false) ||
                strpos($leagueName, 'pakistan') !== false && strpos($leagueName, 'west indies') !== false) {
                $pakWiEvents[] = $event;
            }
        }
        
        // Check for Pakistan vs West Indies specifically in series
        $pakWiSeries = [];
        foreach ($series as $s) {
            $seriesName = strtolower($s['series_name'] ?? $s['league_name'] ?? '');
            if (strpos($seriesName, 'pakistan') !== false && strpos($seriesName, 'west indies') !== false) {
                $pakWiSeries[] = $s;
            }
        }
        
        $debugData = [
            'total_leagues' => count($leagues),
            'total_series' => count($series),
            'total_live_matches' => count($liveScores),
            'today_events' => count($todayEvents),
            'week_events' => count($weekEvents),
            'month_events' => count($monthEvents),
            'pak_wi_events' => $pakWiEvents,
            'pak_wi_series' => $pakWiSeries,
            'sample_leagues' => array_slice($leagues, 0, 5),
            'sample_series' => array_slice($series, 0, 5),
            'sample_live' => array_slice($liveScores, 0, 5),
            'sample_today' => array_slice($todayEvents, 0, 5),
            'sample_week' => array_slice($weekEvents, 0, 5),
            'sample_month' => array_slice($monthEvents, 0, 5)
        ];
        
        return response()->json($debugData);
    }

    /**
     * Debug API calls directly to see what's happening
     */
    public function debugApiCalls()
    {
        try {
            // Test the API endpoint directly
            $apiKey = config('services.cricket.api_key');
            $baseUrl = 'https://apiv2.api-cricket.com/cricket/';
            
            // Test 1: Get leagues
            $leaguesResponse = Http::timeout(30)->get($baseUrl, [
                'method' => 'get_leagues',
                'APIkey' => $apiKey
            ]);
            
            // Test 2: Get events for current month
            $eventsResponse = Http::timeout(30)->get($baseUrl, [
                'method' => 'get_events',
                'APIkey' => $apiKey,
                'date_start' => now()->startOfMonth()->format('Y-m-d'),
                'date_stop' => now()->endOfMonth()->format('Y-m-d')
            ]);
            
            // Test 3: Get live scores
            $liveResponse = Http::timeout(30)->get($baseUrl, [
                'method' => 'get_livescore',
                'APIkey' => $apiKey
            ]);
            
            $debugData = [
                'api_config' => [
                    'base_url' => $baseUrl,
                    'api_key' => substr($apiKey, 0, 10) . '...',
                    'api_key_length' => strlen($apiKey)
                ],
                'leagues_test' => [
                    'status' => $leaguesResponse->status(),
                    'success' => $leaguesResponse->successful(),
                    'body_length' => strlen($leaguesResponse->body()),
                    'response' => $leaguesResponse->json()
                ],
                'events_test' => [
                    'status' => $eventsResponse->status(),
                    'success' => $eventsResponse->successful(),
                    'body_length' => strlen($eventsResponse->body()),
                    'response' => $eventsResponse->json()
                ],
                'live_test' => [
                    'status' => $liveResponse->status(),
                    'success' => $liveResponse->successful(),
                    'body_length' => strlen($liveResponse->body()),
                    'response' => $liveResponse->json()
                ]
            ];
            
            return response()->json($debugData);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Test domain resolution and basic connectivity
     */
    public function testDomainConnectivity()
    {
        try {
            $domains = [
                'apiv2.api-cricket.com',
                'api-cricket.com',
                'www.api-cricket.com'
            ];
            
            $results = [];
            
            foreach ($domains as $domain) {
                $url = "https://{$domain}/cricket/";
                
                try {
                    $response = Http::timeout(10)->get($url, [
                        'method' => 'get_leagues',
                        'APIkey' => config('services.cricket.api_key')
                    ]);
                    
                    $results[$domain] = [
                        'status' => $response->status(),
                        'success' => $response->successful(),
                        'body_length' => strlen($response->body()),
                        'response_preview' => substr($response->body(), 0, 200)
                    ];
                } catch (\Exception $e) {
                    $results[$domain] = [
                        'error' => $e->getMessage(),
                        'status' => 'failed'
                    ];
                }
            }
            
            return response()->json([
                'domain_tests' => $results,
                'api_key' => substr(config('services.cricket.api_key'), 0, 10) . '...'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Filter leagues to identify international cricket competitions
     */
    private function filterInternationalLeagues($leagues)
    {
        if (empty($leagues)) return [];
        
        $internationalKeywords = [
            'international', 'test', 'odi', 't20', 'world cup', 'champions trophy',
            'asia cup', 'ashes', 'pakistan', 'india', 'australia', 'england',
            'south africa', 'new zealand', 'west indies', 'sri lanka', 'bangladesh'
        ];
        
        return array_filter($leagues, function($league) use ($internationalKeywords) {
            $leagueName = strtolower($league['league_name'] ?? '');
            foreach ($internationalKeywords as $keyword) {
                if (strpos($leagueName, $keyword) !== false) {
                    return true;
                }
            }
            return false;
        });
    }

    /**
     * Filter leagues to identify famous cricket competitions
     */
    private function filterFamousLeagues($leagues)
    {
        if (empty($leagues)) return [];
        
        $famousKeywords = [
            'ipl', 'psl', 'bbl', 'cpl', 'super smash', 'premier league',
            'championship', 'premier', 'elite', 'division 1', 'division one'
        ];
        
        return array_filter($leagues, function($league) use ($famousKeywords) {
            $leagueName = strtolower($league['league_name'] ?? '');
            foreach ($famousKeywords as $keyword) {
                if (strpos($leagueName, $keyword) !== false) {
                    return true;
                }
            }
            return false;
        });
    }

    /**
     * Get featured series (ongoing international series)
     */
    private function getFeaturedSeries($internationalLeagues)
    {
        if (empty($internationalLeagues)) return [];
        
        $featuredSeries = [];
        
        // Get comprehensive series data with results and standings for the next 60 days
        $series = $this->cricketApi->getSeriesWithResultsAndStandings(
            null, 
            now()->subDays(30)->format('Y-m-d'),
            now()->addDays(30)->format('Y-m-d')
        );
        
        if (empty($series)) return [];
        
        foreach ($series as $seriesData) {
            // Check if this series has live matches
            if (isset($seriesData['live_matches']) && $seriesData['live_matches'] > 0) {
                // Check if it's an international series by name
                $seriesName = strtolower($seriesData['series_name'] ?? '');
                $isInternational = $this->isInternationalSeries($seriesName);
                
                if ($isInternational) {
                    $featuredSeries[] = [
                        'league' => [
                            'league_key' => $seriesData['series_key'] ?? $seriesData['league_key'] ?? '',
                            'league_name' => $seriesData['series_name'] ?? $seriesData['league_name'] ?? '',
                            'league_year' => $seriesData['series_year'] ?? $seriesData['league_year'] ?? '',
                            'league_country' => $seriesData['series_country'] ?? ''
                        ],
                        'events' => array_slice($seriesData['events'] ?? [], 0, 3), // Show max 3 events per series
                        'type' => 'International',
                        'status' => 'Live',
                        'live_matches' => $seriesData['live_matches'],
                        'total_matches' => $seriesData['total_matches'] ?? count($seriesData['events'] ?? []),
                        'results' => array_slice($seriesData['results'] ?? [], 0, 5), // Show last 5 results
                        'standings' => $seriesData['standings'] ?? [],
                        'stats' => $seriesData['stats'] ?? []
                    ];
                }
            }
        }
        
        return array_slice($featuredSeries, 0, 6); // Show max 6 featured series
    }

    /**
     * Get upcoming series (scheduled international series)
     */
    private function getUpcomingSeries($internationalLeagues)
    {
        $upcomingSeries = [];
        
        try {
        // Get comprehensive series data with results and standings for the next 90 days
        $series = $this->cricketApi->getSeriesWithResultsAndStandings(
            null, 
            now()->addDays(1)->format('Y-m-d'),
            now()->addDays(90)->format('Y-m-d')
        );
        
            if (empty($series)) {
                \Log::info('No series data returned from API for upcoming series');
                return [];
            }
            
            \Log::info('Found ' . count($series) . ' series from API for upcoming series');
        
        foreach ($series as $seriesData) {
            // Check if this series has upcoming matches
            if (isset($seriesData['upcoming_matches']) && $seriesData['upcoming_matches'] > 0) {
                    // Be less restrictive - accept more series types
                $seriesName = strtolower($seriesData['series_name'] ?? '');
                $isInternational = $this->isInternationalSeries($seriesName);
                
                    // Also accept series with upcoming matches even if not strictly international
                    if ($isInternational || $seriesData['upcoming_matches'] >= 2) {
                    $upcomingSeries[] = [
                        'league' => [
                            'league_key' => $seriesData['series_key'] ?? $seriesData['league_key'] ?? '',
                            'league_name' => $seriesData['series_name'] ?? $seriesData['league_name'] ?? '',
                            'league_year' => $seriesData['series_year'] ?? $seriesData['league_year'] ?? '',
                            'league_country' => $seriesData['series_country'] ?? ''
                        ],
                        'events' => array_slice($seriesData['events'] ?? [], 0, 3), // Show max 3 events per series
                            'type' => $isInternational ? 'International' : 'League',
                        'status' => 'Upcoming',
                        'upcoming_matches' => $seriesData['upcoming_matches'],
                        'total_matches' => $seriesData['total_matches'] ?? count($seriesData['events'] ?? []),
                        'results' => array_slice($seriesData['results'] ?? [], 0, 5), // Show last 5 results
                        'standings' => $seriesData['standings'] ?? [],
                        'stats' => $seriesData['stats'] ?? []
                    ];
                }
            }
            }
            
            \Log::info('Filtered to ' . count($upcomingSeries) . ' upcoming series');
            
        } catch (\Exception $e) {
            \Log::error('Error in getUpcomingSeries: ' . $e->getMessage());
        }
        
        return array_slice($upcomingSeries, 0, 6); // Show max 6 upcoming series
    }

    /**
     * Get all current international series with comprehensive data
     */
    private function getAllCurrentInternationalSeries($internationalLeagues)
    {
        if (empty($internationalLeagues)) return [];
        
        $allSeries = [];
        
        // Get comprehensive series data for the current year
        $series = $this->cricketApi->getSeriesWithResultsAndStandings(
            null, 
            now()->startOfYear()->format('Y-m-d'),
            now()->endOfYear()->format('Y-m-d')
        );
        
        if (empty($series)) return [];
        
        foreach ($series as $seriesData) {
            // Check if it's an international series by name
            $seriesName = strtolower($seriesData['series_name'] ?? '');
            $isInternational = $this->isInternationalSeries($seriesName);
            
            if ($isInternational) {
                $allSeries[] = [
                    'league' => [
                        'league_key' => $seriesData['series_key'] ?? $seriesData['league_key'] ?? '',
                        'league_name' => $seriesData['series_name'] ?? $seriesData['league_name'] ?? '',
                        'league_year' => $seriesData['series_year'] ?? $seriesData['league_year'] ?? '',
                        'league_country' => $seriesData['series_country'] ?? ''
                    ],
                    'events' => $seriesData['events'] ?? [],
                    'type' => 'International',
                    'status' => $this->getSeriesStatus($seriesData),
                    'live_matches' => $seriesData['live_matches'] ?? 0,
                    'upcoming_matches' => $seriesData['upcoming_matches'] ?? 0,
                    'total_matches' => $seriesData['total_matches'] ?? count($seriesData['events'] ?? []),
                    'results' => $seriesData['results'] ?? [],
                    'standings' => $seriesData['standings'] ?? [],
                    'stats' => $seriesData['stats'] ?? []
                ];
            }
        }
        
        // Sort by status priority: Live > Recent > Upcoming
        usort($allSeries, function($a, $b) {
            $priority = ['Live' => 3, 'Recent' => 2, 'Upcoming' => 1, 'Completed' => 0];
            return ($priority[$b['status']] ?? 0) - ($priority[$a['status']] ?? 0);
        });
        
        return $allSeries;
    }
    
    /**
     * Determine series status based on match states
     */
    private function getSeriesStatus($seriesData)
    {
        if ($seriesData['live_matches'] > 0) {
            return 'Live';
        } elseif ($seriesData['upcoming_matches'] > 0 && $seriesData['completed_matches'] > 0) {
            return 'Recent';
        } elseif ($seriesData['upcoming_matches'] > 0) {
            return 'Upcoming';
        } elseif ($seriesData['completed_matches'] > 0) {
            return 'Completed';
        } else {
            return 'Unknown';
        }
    }

    /**
     * Get international teams
     */
    private function getInternationalTeams()
    {
        $teams = $this->cricketApi->getTeams();
        
        if (empty($teams)) return [];
        
        $internationalTeamNames = [
            'Pakistan', 'India', 'Australia', 'England', 'South Africa', 'New Zealand',
            'West Indies', 'Sri Lanka', 'Bangladesh', 'Afghanistan', 'Ireland',
            'Zimbabwe', 'Netherlands', 'Scotland', 'UAE', 'Oman', 'Nepal'
        ];
        
        return array_filter($teams, function($team) use ($internationalTeamNames) {
            $teamName = $team['team_name'] ?? '';
            foreach ($internationalTeamNames as $internationalName) {
                if (stripos($teamName, $internationalName) !== false) {
                    return true;
                }
            }
            return false;
                });
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
     * Check if a series name indicates an international cricket competition.
     */
    private function isInternationalSeries($seriesName)
    {
        $internationalKeywords = [
            'international', 'test', 'odi', 't20', 'world cup', 'champions trophy',
            'asia cup', 'ashes', 'pakistan', 'india', 'australia', 'england',
            'south africa', 'new zealand', 'west indies', 'sri lanka', 'bangladesh',
            'afghanistan', 'ireland', 'zimbabwe', 'netherlands', 'scotland', 'namibia',
            'oman', 'uae', 'hong kong', 'singapore', 'malaysia', 'thailand',
            'nepal', 'bhutan', 'maldives', 'myanmar', 'cambodia', 'laos',
            'vietnam', 'philippines', 'indonesia', 'brunei', 'east timor'
        ];

        foreach ($internationalKeywords as $keyword) {
            if (strpos($seriesName, $keyword) !== false) {
                return true;
            }
        }
        
        // Also check for common cricket terms that might indicate a series
        $cricketTerms = ['series', 'tour', 'championship', 'league', 'cup', 'trophy'];
        foreach ($cricketTerms as $term) {
            if (strpos($seriesName, $term) !== false) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Test API directly to see what's available
     */
    public function testApi()
    {
        try {
            // Test basic API calls
            $leagues = $this->cricketApi->getLeagues(); // Get all leagues (not filtered)
            $teams = $this->cricketApi->getTeams(); // Get teams extracted from events
            
            // Test specific date ranges
            $currentMonth = $this->cricketApi->getEvents(
                now()->startOfMonth()->format('Y-m-d'),
                now()->endOfMonth()->format('Y-m-d')
            );
            
            $nextMonth = $this->cricketApi->getEvents(
                now()->addMonth()->startOfMonth()->format('Y-m-d'),
                now()->addMonth()->endOfMonth()->format('Y-m-d')
            );
            
            // Look for Pakistan and West Indies specifically
            $pakistanMatches = [];
            $westIndiesMatches = [];
            
            foreach (array_merge($currentMonth, $nextMonth) as $match) {
                $homeTeam = strtolower($match['event_home_team'] ?? '');
                $awayTeam = strtolower($match['event_away_team'] ?? '');
                $leagueName = strtolower($match['league_name'] ?? '');
                
                if (strpos($homeTeam, 'pakistan') !== false || strpos($awayTeam, 'pakistan') !== false) {
                    $pakistanMatches[] = $match;
                }
                
                if (strpos($homeTeam, 'west indies') !== false || strpos($awayTeam, 'westIndies') !== false) {
                    $westIndiesMatches[] = $match;
                }
            }
            
            $apiTestData = [
                'total_leagues_available' => count($leagues),
                'total_teams_available' => count($teams),
                'current_month_events' => count($currentMonth),
                'next_month_events' => count($nextMonth),
                'pakistan_matches' => $pakistanMatches,
                'west_indies_matches' => $westIndiesMatches,
                'sample_leagues' => array_slice($leagues, 0, 10),
                'sample_teams' => array_slice($teams, 0, 10),
                'sample_current_month' => array_slice($currentMonth, 0, 5),
                'sample_next_month' => array_slice($nextMonth, 0, 5)
            ];
            
            return response()->json($apiTestData);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
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
     * Get empty test API data for error cases
     */
    private function getEmptyTestApiData($errorMessage)
    {
        return [
            'error' => $errorMessage,
            'total_leagues_available' => 0,
            'total_teams_available' => 0,
            'current_month_events' => 0,
            'next_month_events' => 0,
            'pakistan_matches' => [],
            'west_indies_matches' => [],
            'sample_leagues' => [],
            'sample_teams' => [],
            'sample_current_month' => [],
            'sample_next_month' => []
        ];
    }

    /**
     * Get empty monthly series data for error cases
     */
    private function getEmptyMonthlySeriesData()
    {
        return [
            'current_month' => [
                'period' => now()->format('F Y'),
                'series' => [],
                'total_events' => 0
            ],
            'previous_month' => [
                'period' => now()->subMonth()->format('F Y'),
                'series' => [],
                'total_events' => 0
            ]
        ];
    }

    /**
     * Process series data with proper categorization and enrichment
     */
    private function processSeriesData($series, $leagues)
    {
        if (empty($series)) {
            return [];
        }

        $processedSeries = [];
        
        foreach ($series as $seriesItem) {
            $processedItem = [
                'series_key' => $seriesItem['series_key'] ?? $seriesItem['league_key'] ?? uniqid(),
                'series_name' => $seriesItem['series_name'] ?? $seriesItem['league_name'] ?? 'Unknown Series',
                'series_country' => $seriesItem['series_country'] ?? $seriesItem['league_country'] ?? 'Unknown',
                'series_season' => $seriesItem['series_season'] ?? $seriesItem['league_season'] ?? date('Y'),
                'series_type' => $this->determineSeriesType($seriesItem),
                'series_status' => $this->determineSeriesStatus($seriesItem),
                'total_matches' => count($seriesItem['events'] ?? []),
                'live_matches' => 0,
                'upcoming_matches' => 0,
                'completed_matches' => 0,
                'events' => $seriesItem['events'] ?? [],
                'standings' => $seriesItem['standings'] ?? [],
                'results' => $seriesItem['results'] ?? [],
                'match_summary' => $this->calculateSeriesSummary($seriesItem['events'] ?? []),
                'league_info' => $this->findLeagueInfo($seriesItem, $leagues)
            ];

            // Count match types
            foreach ($processedItem['events'] as $event) {
                $status = strtolower($event['event_status'] ?? '');
                if (in_array($status, ['live', 'started', 'in progress'])) {
                    $processedItem['live_matches']++;
                } elseif (in_array($status, ['scheduled', 'not started'])) {
                    $processedItem['upcoming_matches']++;
                } elseif (in_array($status, ['finished', 'completed'])) {
                    $processedItem['completed_matches']++;
                }
            }

            $processedSeries[] = $processedItem;
        }

        return $processedSeries;
    }

    /**
     * Categorize series by status and time
     */
    private function categorizeSeries($series, $currentMonthEvents, $previousMonthEvents, $nextMonthEvents)
    {
        $categorized = [
            'active' => [],
            'recent' => [],
            'upcoming' => [],
            'completed' => []
        ];

        foreach ($series as $seriesItem) {
            if ($seriesItem['live_matches'] > 0) {
                $categorized['active'][] = $seriesItem;
            } elseif ($seriesItem['upcoming_matches'] > 0) {
                $categorized['upcoming'][] = $seriesItem;
            } elseif ($seriesItem['completed_matches'] > 0) {
                $categorized['completed'][] = $seriesItem;
            } else {
                $categorized['recent'][] = $seriesItem;
            }
        }

        return $categorized;
    }

    /**
     * Determine series type based on league name and events
     */
    private function determineSeriesType($series)
    {
        $leagueName = strtolower($series['league_name'] ?? $series['series_name'] ?? '');
        
        if (strpos($leagueName, 'test') !== false) {
            return 'Test Series';
        } elseif (strpos($leagueName, 'odi') !== false || strpos($leagueName, 'one day') !== false) {
            return 'ODI Series';
        } elseif (strpos($leagueName, 't20') !== false || strpos($leagueName, 'twenty20') !== false) {
            return 'T20 Series';
        } elseif (strpos($leagueName, 'ipl') !== false || strpos($leagueName, 'psl') !== false) {
            return 'T20 League';
        } elseif (strpos($leagueName, 'tour') !== false) {
            return 'Tour Series';
        } else {
            return 'Limited Overs';
        }
    }

    /**
     * Determine series status based on events
     */
    private function determineSeriesStatus($series)
    {
        $events = $series['events'] ?? [];
        $liveCount = 0;
        $upcomingCount = 0;
        $completedCount = 0;

        foreach ($events as $event) {
            $status = strtolower($event['event_status'] ?? '');
            if (in_array($status, ['live', 'started', 'in progress'])) {
                $liveCount++;
            } elseif (in_array($status, ['scheduled', 'not started'])) {
                $upcomingCount++;
            } elseif (in_array($status, ['finished', 'completed'])) {
                $completedCount++;
            }
        }

        if ($liveCount > 0) {
            return 'Live';
        } elseif ($upcomingCount > 0) {
            return 'Upcoming';
        } elseif ($completedCount > 0) {
            return 'Completed';
        } else {
            return 'Unknown';
        }
    }

    /**
     * Find league information from leagues array
     */
    private function findLeagueInfo($series, $leagues)
    {
        $seriesKey = $series['series_key'] ?? $series['league_key'] ?? '';
        
        foreach ($leagues as $league) {
            if (($league['league_key'] ?? '') == $seriesKey) {
                return $league;
            }
        }
        
        return null;
    }

    /**
     * Get series priority for sorting
     */
    private function getSeriesPriority($series)
    {
        $priority = 0;
        
        // Live series get highest priority
        if ($series['live_matches'] > 0) {
            $priority += 1000;
        }
        
        // International series get higher priority
        if (strpos(strtolower($series['series_country'] ?? ''), 'international') !== false) {
            $priority += 500;
        }
        
        // More matches get higher priority
        $priority += $series['total_matches'];
        
        // Recent series get higher priority
        if (strpos($series['series_season'] ?? '', date('Y')) !== false) {
            $priority += 100;
        }
        
        return $priority;
    }

    /**
     * Get series statistics from matches
     */
    private function getSeriesStatistics($matches)
    {
        $stats = [
            'total_matches' => count($matches),
            'live_matches' => 0,
            'upcoming_matches' => 0,
            'completed_matches' => 0,
            'total_runs' => 0,
            'total_wickets' => 0,
            'highest_score' => 0,
            'lowest_score' => 999999,
            'match_types' => [],
            'venues' => [],
            'teams' => []
        ];
        
        foreach ($matches as $match) {
            $status = strtolower($match['event_status'] ?? '');
            if (in_array($status, ['live', 'started', 'in progress'])) {
                $stats['live_matches']++;
            } elseif (in_array($status, ['scheduled', 'not started'])) {
                $stats['upcoming_matches']++;
            } elseif (in_array($status, ['finished', 'completed'])) {
                $stats['completed_matches']++;
            }
            
            // Collect match types
            $matchType = $match['event_type'] ?? 'Unknown';
            if (!in_array($matchType, $stats['match_types'])) {
                $stats['match_types'][] = $matchType;
            }
            
            // Collect venues
            $venue = $match['event_stadium'] ?? 'Unknown';
            if (!in_array($venue, $stats['venues'])) {
                $stats['venues'][] = $venue;
            }
            
            // Collect teams
            $homeTeam = $match['event_home_team'] ?? '';
            $awayTeam = $match['event_away_team'] ?? '';
            if ($homeTeam && !in_array($homeTeam, $stats['teams'])) {
                $stats['teams'][] = $homeTeam;
            }
            if ($awayTeam && !in_array($awayTeam, $stats['teams'])) {
                $stats['teams'][] = $awayTeam;
            }
        }
        
        return $stats;
    }

    /**
     * Get test API data for display on series page
     */
    private function getTestApiData()
    {
        try {
            // Test basic API calls
            $leagues = $this->cricketApi->getLeagues(); // Get all leagues (not filtered)
            $teams = $this->cricketApi->getTeams(); // Get teams extracted from events
            
            // Test specific date ranges
            $currentMonth = $this->cricketApi->getEvents(
                now()->startOfMonth()->format('Y-m-d'),
                now()->endOfMonth()->format('Y-m-d')
            );
            
            $nextMonth = $this->cricketApi->getEvents(
                now()->addMonth()->startOfMonth()->format('Y-m-d'),
                now()->addMonth()->endOfMonth()->format('Y-m-d')
            );
            
            // Look for Pakistan and West Indies specifically
            $pakistanMatches = [];
            $westIndiesMatches = [];
            
            foreach (array_merge($currentMonth, $nextMonth) as $match) {
                $homeTeam = strtolower($match['event_home_team'] ?? '');
                $awayTeam = strtolower($match['event_home_team'] ?? '');
                $leagueName = strtolower($match['league_name'] ?? '');
                
                if (strpos($homeTeam, 'pakistan') !== false || strpos($awayTeam, 'pakistan') !== false) {
                    $pakistanMatches[] = $match;
                }
                
                if (strpos($homeTeam, 'west indies') !== false || strpos($awayTeam, 'west indies') !== false) {
                    $westIndiesMatches[] = $match;
                }
            }
            
            return [
                'total_leagues_available' => count($leagues),
                'total_teams_available' => count($teams),
                'current_month_events' => count($currentMonth),
                'next_month_events' => count($nextMonth),
                'pakistan_matches' => $pakistanMatches,
                'west_indies_matches' => $westIndiesMatches,
                'sample_leagues' => array_slice($leagues, 0, 10),
                'sample_teams' => array_slice($teams, 0, 10),
                'sample_current_month' => array_slice($currentMonth, 0, 5),
                'sample_next_month' => array_slice($nextMonth, 0, 5)
            ];
            
        } catch (\Exception $e) {
            \Log::error('Error getting test API data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'error' => $e->getMessage(),
                'total_leagues_available' => 0,
                'total_teams_available' => 0,
                'current_month_events' => 0,
                'next_month_events' => 0,
                'pakistan_matches' => [],
                'west_indies_matches' => [],
                'sample_leagues' => [],
                'sample_teams' => [],
                'sample_current_month' => [],
                'sample_next_month' => []
            ];
        }
    }

    /**
     * Get current and previous month series with detailed match information
     */
    private function getCurrentAndPreviousMonthSeries()
    {
        try {
            $currentMonthStart = now()->startOfMonth()->format('Y-m-d');
            $currentMonthEnd = now()->endOfMonth()->format('Y-m-d');
            $previousMonthStart = now()->subMonth()->startOfMonth()->format('Y-m-d');
            $previousMonthEnd = now()->subMonth()->endOfMonth()->format('Y-m-d');
            
            // Get events for current and previous month
            $currentMonthEvents = $this->cricketApi->getEvents($currentMonthStart, $currentMonthEnd);
            $previousMonthEvents = $this->cricketApi->getEvents($previousMonthStart, $previousMonthEnd);
            
            // Group events by series/league
            $currentMonthSeries = $this->groupEventsBySeries($currentMonthEvents);
            $previousMonthSeries = $this->groupEventsBySeries($previousMonthEvents);
            
            // Get detailed match results for each series
            $currentMonthSeries = $this->addDetailedMatchResults($currentMonthSeries);
            $previousMonthSeries = $this->addDetailedMatchResults($previousMonthSeries);
            
            return [
                'current_month' => [
                    'period' => now()->format('F Y'),
                    'series' => $currentMonthSeries,
                    'total_events' => count($currentMonthEvents)
                ],
                'previous_month' => [
                    'period' => now()->subMonth()->format('F Y'),
                    'series' => $previousMonthSeries,
                    'total_events' => count($previousMonthEvents)
                ]
            ];
            
        } catch (\Exception $e) {
            \Log::error('Error getting current and previous month series', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'current_month' => [
                    'period' => now()->format('F Y'),
                    'series' => [],
                    'total_events' => 0
                ],
                'previous_month' => [
                    'period' => now()->subMonth()->format('F Y'),
                    'series' => [],
                    'total_events' => 0
                ]
            ];
        }
    }

    /**
     * Group events by series/league
     */
    private function groupEventsBySeries($events)
    {
        if (empty($events)) {
            return [];
        }
        
        $seriesGroups = [];
        
        foreach ($events as $event) {
            $leagueKey = $event['league_key'] ?? $event['series_key'] ?? 'unknown_' . uniqid();
            $leagueName = $event['league_name'] ?? $event['series_name'] ?? 'Unknown Series';
            
            if (!isset($seriesGroups[$leagueKey])) {
                $seriesGroups[$leagueKey] = [
                    'series_key' => $leagueKey,
                    'series_name' => $leagueName,
                    'series_country' => $event['league_country'] ?? $event['series_country'] ?? 'Unknown',
                    'series_season' => $event['league_season'] ?? date('Y'),
                    'total_matches' => 0,
                    'live_matches' => 0,
                    'upcoming_matches' => 0,
                    'completed_matches' => 0,
                    'events' => [],
                    'match_summary' => [
                        'total_runs' => 0,
                        'total_wickets' => 0,
                        'highest_score' => 0,
                        'lowest_score' => 999999,
                        'most_runs' => ['player' => '', 'runs' => 0],
                        'most_wickets' => ['player' => '', 'wickets' => 0]
                    ]
                ];
            }
            
            // Add event to series
            $seriesGroups[$leagueKey]['events'][] = $event;
            $seriesGroups[$leagueKey]['total_matches']++;
            
            // Count match types
            $status = strtolower($event['event_status'] ?? '');
            if (in_array($status, ['live', 'started', 'in progress'])) {
                $seriesGroups[$leagueKey]['live_matches']++;
            } elseif (in_array($status, ['scheduled', 'not started'])) {
                $seriesGroups[$leagueKey]['upcoming_matches']++;
            } elseif (in_array($status, ['finished', 'completed'])) {
                $seriesGroups[$leagueKey]['completed_matches']++;
            }
        }
        
        return array_values($seriesGroups);
    }

    /**
     * Add detailed match results and statistics to series
     */
    private function addDetailedMatchResults($series)
    {
        foreach ($series as &$seriesData) {
            foreach ($seriesData['events'] as &$event) {
                // Get detailed match data if available
                if (isset($event['event_key'])) {
                    try {
                        $detailedMatch = $this->cricketApi->getEvents(null, null, null, $event['event_key']);
                        if (!empty($detailedMatch)) {
                            $event['detailed_data'] = $detailedMatch[0];
                            
                            // Extract match results if available
                            if (isset($detailedMatch[0]['scores'])) {
                                $event['match_results'] = $this->extractMatchResults($detailedMatch[0]['scores']);
                            }
                        }
                    } catch (\Exception $e) {
                        \Log::warning('Could not get detailed data for event: ' . $event['event_key']);
                    }
                }
            }
            
            // Calculate series summary statistics
            $seriesData['match_summary'] = $this->calculateSeriesSummary($seriesData['events']);
        }
        
        return $series;
    }

    /**
     * Extract match results from scores data
     */
    private function extractMatchResults($scores)
    {
        if (empty($scores)) {
            return null;
        }
        
        $results = [
            'home_team' => [
                'name' => '',
                'score' => '',
                'overs' => '',
                'wickets' => 0
            ],
            'away_team' => [
                'name' => '',
                'score' => '',
                'overs' => '',
                'wickets' => 0
            ],
            'result' => '',
            'highlights' => []
        ];
        
        foreach ($scores as $score) {
            if (isset($score['team_name'])) {
                $teamData = [
                    'name' => $score['team_name'],
                    'score' => $score['score'] ?? '0/0',
                    'overs' => $score['overs'] ?? '0.0',
                    'wickets' => $score['wickets'] ?? 0
                ];
                
                if (isset($score['is_home']) && $score['is_home']) {
                    $results['home_team'] = $teamData;
                } else {
                    $results['away_team'] = $teamData;
                }
            }
        }
        
        // Determine result
        if (!empty($results['home_team']['name']) && !empty($results['away_team']['name'])) {
            $homeScore = $this->parseScore($results['home_team']['score']);
            $awayScore = $this->parseScore($results['away_team']['score']);
            
            if ($homeScore['runs'] > $awayScore['runs']) {
                $results['result'] = $results['home_team']['name'] . ' won by ' . ($homeScore['runs'] - $awayScore['runs']) . ' runs';
            } elseif ($awayScore['runs'] > $homeScore['runs']) {
                $results['result'] = $results['away_team']['name'] . ' won by ' . ($awayScore['runs'] - $homeScore['runs']) . ' runs';
            } else {
                $results['result'] = 'Match tied';
            }
        }
        
        return $results;
    }

    /**
     * Parse score string to extract runs and wickets
     */
    private function parseScore($scoreString)
    {
        $parts = explode('/', $scoreString);
        return [
            'runs' => intval($parts[0] ?? 0),
            'wickets' => intval($parts[1] ?? 0)
        ];
    }

    /**
     * Calculate series summary statistics
     */
    private function calculateSeriesSummary($events)
    {
        $summary = [
            'total_runs' => 0,
            'total_wickets' => 0,
            'highest_score' => 0,
            'lowest_score' => 999999,
            'most_runs' => ['player' => '', 'runs' => 0],
            'most_wickets' => ['player' => '', 'wickets' => 0]
        ];
        
        foreach ($events as $event) {
            if (isset($event['match_results'])) {
                $homeScore = $this->parseScore($event['match_results']['home_team']['score']);
                $awayScore = $this->parseScore($event['match_results']['away_team']['score']);
                
                $summary['total_runs'] += $homeScore['runs'] + $awayScore['runs'];
                $summary['total_wickets'] += $homeScore['wickets'] + $awayScore['wickets'];
                
                $summary['highest_score'] = max($summary['highest_score'], $homeScore['runs'], $awayScore['runs']);
                $summary['lowest_score'] = min($summary['lowest_score'], $homeScore['runs'], $awayScore['runs']);
            }
        }
        
        return $summary;
    }

    /**
     * Show all active series in a table format using proper Cricket API endpoints
     */
    public function series()
    {
        try {
            \Log::info('Starting series method with proper API implementation');
            
            // Get current date ranges
            $currentDate = now()->format('Y-m-d');
            $currentMonthStart = now()->startOfMonth()->format('Y-m-d');
            $currentMonthEnd = now()->endOfMonth()->format('Y-m-d');
            $previousMonthStart = now()->subMonth()->startOfMonth()->format('Y-m-d');
            $previousMonthEnd = now()->subMonth()->endOfMonth()->format('Y-m-d');
            $nextMonthStart = now()->addMonth()->startOfMonth()->format('Y-m-d');
            $nextMonthEnd = now()->addMonth()->endOfMonth()->format('Y-m-d');
            
            // Get all leagues first
            $allLeagues = $this->cricketApi->getLeagues();
            \Log::info('Fetched leagues', ['count' => count($allLeagues)]);
            
            // Get series data using proper API methods
            $allSeries = $this->cricketApi->getSeriesWithResultsAndStandings();
            \Log::info('Fetched series with results', ['count' => count($allSeries)]);
            
            // Get current month events
            $currentMonthEvents = $this->cricketApi->getEvents($currentMonthStart, $currentMonthEnd);
            \Log::info('Fetched current month events', ['count' => count($currentMonthEvents)]);
            
            // Get previous month events
            $previousMonthEvents = $this->cricketApi->getEvents($previousMonthStart, $previousMonthEnd);
            \Log::info('Fetched previous month events', ['count' => count($previousMonthEvents)]);
            
            // Get next month events
            $nextMonthEvents = $this->cricketApi->getEvents($nextMonthStart, $nextMonthEnd);
            \Log::info('Fetched next month events', ['count' => count($nextMonthEvents)]);
            
            // Get live scores
            $liveScores = $this->cricketApi->getLiveScores();
            \Log::info('Fetched live scores', ['count' => count($liveScores)]);
            
            // Process series data with proper categorization
            $processedSeries = $this->processSeriesData($allSeries, $allLeagues);
            
            // Categorize series by status and time
            $categorizedSeries = $this->categorizeSeries($processedSeries, $currentMonthEvents, $previousMonthEvents, $nextMonthEvents);
            
            // Get test API data for comprehensive display
            $testApiData = $this->getTestApiData();
            
            // Get current and previous month series with detailed information
            $monthlySeriesData = $this->getCurrentAndPreviousMonthSeries();
            
            // Prepare data for view
            $activeSeries = $categorizedSeries['active'] ?? [];
            $recentSeries = $categorizedSeries['recent'] ?? [];
            $upcomingSeries = $categorizedSeries['upcoming'] ?? [];
            $completedSeries = $categorizedSeries['completed'] ?? [];
            
            // Sort series by relevance
        usort($activeSeries, function($a, $b) {
                $aPriority = $this->getSeriesPriority($a);
                $bPriority = $this->getSeriesPriority($b);
                return $bPriority - $aPriority;
        });
        
        // Debug information
        $debugInfo = [
            'total_series_from_api' => count($allSeries),
            'active_series_count' => count($activeSeries),
            'recent_series_count' => count($recentSeries),
            'upcoming_series_count' => count($upcomingSeries),
                'completed_series_count' => count($completedSeries),
                'total_leagues' => count($allLeagues),
                'total_live_matches' => count($liveScores),
                'current_month_events' => count($currentMonthEvents),
                'previous_month_events' => count($previousMonthEvents),
                'next_month_events' => count($nextMonthEvents),
            'current_date' => now()->format('Y-m-d H:i:s'),
                'api_endpoints_used' => [
                    'get_leagues' => '✅',
                    'get_series_with_results' => '✅',
                    'get_events' => '✅',
                    'get_livescore' => '✅'
                ]
            ];
            
            return view('cricket.series', compact(
                'activeSeries', 
                'recentSeries', 
                'upcomingSeries', 
                'completedSeries',
                'allSeries', 
                'allLeagues',
                'liveScores',
                'currentMonthEvents',
                'previousMonthEvents',
                'nextMonthEvents',
                'debugInfo', 
                'testApiData', 
                'monthlySeriesData'
            ));
        
        } catch (\Exception $e) {
            \Log::error('Error in series method', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return empty data with error info
            $debugInfo = [
                'error' => $e->getMessage(),
                'total_series_from_api' => 0,
                'active_series_count' => 0,
                'recent_series_count' => 0,
                'upcoming_series_count' => 0,
                'completed_series_count' => 0,
                'current_date' => now()->format('Y-m-d H:i:s'),
                'api_endpoints_used' => [
                    'get_leagues' => '❌',
                    'get_series_with_results' => '❌',
                    'get_events' => '❌',
                    'get_livescore' => '❌'
                ]
            ];
            
            // Initialize empty data
            $testApiData = $this->getEmptyTestApiData($e->getMessage());
            $monthlySeriesData = $this->getEmptyMonthlySeriesData();
            
            return view('cricket.series', compact(
                'activeSeries', 
                'recentSeries', 
                'upcomingSeries', 
                'completedSeries',
                'allSeries', 
                'allLeagues',
                'liveScores',
                'currentMonthEvents',
                'previousMonthEvents',
                'nextMonthEvents',
                'debugInfo', 
                'testApiData', 
                'monthlySeriesData'
            ));
        }
    }
    
    /**
     * Convert events data to series format for display
     */
    private function convertEventsToSeries($events)
    {
        if (empty($events)) {
            return [];
        }
        
        // Group events by league
        $seriesGroups = [];
        
        foreach ($events as $event) {
            $leagueKey = $event['league_key'] ?? 'unknown';
            $leagueName = $event['league_name'] ?? 'Unknown League';
            
            if (!isset($seriesGroups[$leagueKey])) {
                $seriesGroups[$leagueKey] = [
                    'series_key' => $leagueKey,
                    'series_name' => $leagueName,
                    'series_year' => $event['league_season'] ?? date('Y'),
                    'series_country' => 'Unknown',
                    'events' => [],
                    'live_matches' => 0,
                    'upcoming_matches' => 0,
                    'completed_matches' => 0
                ];
            }
            
            // Add event to series
            $seriesGroups[$leagueKey]['events'][] = $event;
            
            // Count match types
            $status = strtolower($event['event_status'] ?? '');
            if (in_array($status, ['live', 'started', 'in progress'])) {
                $seriesGroups[$leagueKey]['live_matches']++;
            } elseif (in_array($status, ['scheduled', 'not started'])) {
                $seriesGroups[$leagueKey]['upcoming_matches']++;
            } elseif (in_array($status, ['finished', 'completed'])) {
                $seriesGroups[$leagueKey]['completed_matches']++;
            }
        }
        
        return array_values($seriesGroups);
    }

    /**
     * Show detailed information for a specific series
     */
    public function seriesDetail($seriesKey)
    {
        try {
            \Log::info('Fetching series detail', ['series_key' => $seriesKey]);
            
            // Get series data using proper API method
            $allSeries = $this->cricketApi->getSeriesWithResultsAndStandings();
        
        $series = null;
        foreach ($allSeries as $s) {
            if (($s['series_key'] ?? $s['league_key'] ?? '') == $seriesKey) {
                $series = $s;
                break;
            }
        }
        
        if (!$series) {
                \Log::warning('Series not found', ['series_key' => $seriesKey]);
            abort(404, 'Series not found');
        }
            
            // Get detailed match information for each event
            $enrichedMatches = [];
            foreach ($series['events'] ?? [] as $event) {
                if (isset($event['event_key'])) {
                    try {
                        // Get detailed match data
                        $detailedMatch = $this->cricketApi->getEvents(null, null, null, $event['event_key']);
                        if (!empty($detailedMatch)) {
                            $event['detailed_data'] = $detailedMatch[0];
                            
                            // Get additional match data
                            $event['scorecard'] = $this->cricketApi->getScorecard($event['event_key']);
                            $event['commentary'] = $this->cricketApi->getCommentary($event['event_key']);
                            $event['lineups'] = $this->cricketApi->getLineups($event['event_key']);
                            $event['statistics'] = $this->cricketApi->getMatchStatistics($event['event_key']);
                        }
                    } catch (\Exception $e) {
                        \Log::warning('Could not get detailed data for event', [
                            'event_key' => $event['event_key'],
                            'error' => $e->getMessage()
                        ]);
                    }
                }
                $enrichedMatches[] = $event;
            }
            
            // Get standings if available
            $standings = [];
            if (isset($series['league_key'])) {
                try {
                    $standings = $this->cricketApi->getStandings($series['league_key']);
                } catch (\Exception $e) {
                    \Log::warning('Could not get standings for series', [
                        'league_key' => $series['league_key'],
                        'error' => $e->getMessage()
                    ]);
                }
        }
        
        // Get comprehensive series data
        $seriesData = [
            'series' => $series,
                'matches' => $enrichedMatches,
            'results' => $series['results'] ?? [],
                'standings' => $standings,
            'stats' => $series['stats'] ?? [],
            'teams' => $this->getSeriesTeams($series),
                'summary' => $this->getSeriesSummary($series),
                'match_summary' => $this->calculateSeriesSummary($enrichedMatches),
                'series_statistics' => $this->getSeriesStatistics($enrichedMatches)
            ];
            
            \Log::info('Series detail data prepared', [
                'series_name' => $series['series_name'] ?? 'Unknown',
                'total_matches' => count($enrichedMatches),
                'has_standings' => !empty($standings)
            ]);
            
            return view('cricket.series-detail', compact('series', 'seriesData'));
            
        } catch (\Exception $e) {
            \Log::error('Error in seriesDetail method', [
                'series_key' => $seriesKey,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            abort(500, 'Error loading series details');
        }
    }

    /**
     * Get the latest event date from a series
     */
    private function getLatestEventDate($series)
    {
        if (!isset($series['events']) || !is_array($series['events'])) {
            return now()->subDays(365); // Default to old date if no events
        }
        
        $latestDate = null;
        foreach ($series['events'] as $event) {
            $eventDate = $event['event_date_start'] ?? null;
            if ($eventDate) {
                $eventDateTime = \Carbon\Carbon::parse($eventDate);
                if (!$latestDate || $eventDateTime->gt($latestDate)) {
                    $latestDate = $eventDateTime;
                }
            }
        }
        
        return $latestDate ?: now()->subDays(365);
    }

    /**
     * Get teams participating in a series
     */
    private function getSeriesTeams($series)
    {
        $teams = [];
        
        if (isset($series['events']) && is_array($series['events'])) {
            foreach ($series['events'] as $event) {
                $homeTeam = $event['event_home_team'] ?? '';
                $awayTeam = $event['event_away_team'] ?? '';
                
                if ($homeTeam && !in_array($homeTeam, $teams)) {
                    $teams[] = $homeTeam;
                }
                
                if ($awayTeam && !in_array($awayTeam, $teams)) {
                    $teams[] = $awayTeam;
                }
            }
        }
        
        return $teams;
    }

    /**
     * Get a summary of the series
     */
    private function getSeriesSummary($series)
    {
        $totalMatches = count($series['events'] ?? []);
        $completedMatches = count($series['results'] ?? []);
        $liveMatches = $series['live_matches'] ?? 0;
        $upcomingMatches = $totalMatches - $completedMatches - $liveMatches;
        
        // Calculate completed matches from events with "Finished" status
        if (isset($series['events']) && is_array($series['events'])) {
            $completedMatches = 0;
            foreach ($series['events'] as $event) {
                if (($event['event_status'] ?? '') === 'Finished') {
                    $completedMatches++;
                }
            }
        }
        
        return [
            'total_matches' => $totalMatches,
            'completed_matches' => $completedMatches,
            'live_matches' => $liveMatches,
            'upcoming_matches' => $upcomingMatches,
            'series_type' => $this->getSeriesType($series),
            'series_status' => $this->getSeriesStatus([
                'live_matches' => $liveMatches,
                'upcoming_matches' => $upcomingMatches,
                'completed_matches' => $completedMatches
            ])
        ];
    }

    /**
     * Get the type of series
     */
    private function getSeriesType($series)
    {
        $seriesName = strtolower($series['series_name'] ?? $series['league_name'] ?? '');
        
        if (strpos($seriesName, 'test') !== false) {
            return 'Test Series';
        } elseif (strpos($seriesName, 'odi') !== false || strpos($seriesName, 'one day') !== false) {
            return 'ODI Series';
        } elseif (strpos($seriesName, 't20') !== false || strpos($seriesName, 'twenty20') !== false) {
            return 'T20 Series';
        } else {
            return 'Limited Overs Series';
        }
    }

    /**
     * Live update endpoint for match details
     */
    public function liveUpdate($eventKey)
    {
        try {
            \Log::info('Fetching live update for match', ['event_key' => $eventKey]);
            
            // Get current match data
            $match = $this->cricketApi->getEvents(null, null, null, $eventKey);
            
            if (empty($match)) {
                return response()->json(['error' => 'Match not found'], 404);
            }
            
            $match = $match[0];
            
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
