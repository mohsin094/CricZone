<?php

namespace App\Http\Controllers\Cricket;

use App\Http\Controllers\Controller;
use App\Services\CricketDataService;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TeamController extends Controller
{
    protected $cricketData;

    public function __construct(CricketDataService $cricketData)
    {
        $this->cricketData = $cricketData;
    }

    /**
     * Display teams page
     */
    public function index()
    {
        // Get teams from database first
        $teams = Team::orderBy('team_name')->get();
        
        // If no teams in database, try to get from Cricbuzz API and sync
        if ($teams->isEmpty()) {
            try {
                $apiTeams = $this->cricketData->getTeamsFromApi();
                if (!empty($apiTeams)) {
                    // Store teams in database for future use
                    foreach ($apiTeams as $apiTeam) {
                        if (isset($apiTeam['id']) && isset($apiTeam['name'])) {
                            Team::updateOrCreate(
                                ['team_key' => $apiTeam['id']],
                                [
                                    'team_name' => $apiTeam['name'],
                                    'team_logo' => $apiTeam['imageId'] ?? null,
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
     * Display team detail page
     */
    public function show($teamKey)
    {
        try {
            // Get team info
            $team = Team::where('team_key', $teamKey)->first();
            
            if (!$team) {
                abort(404, 'Team not found');
            }
            
            // Get team matches from centralized service
            $allMatches = $this->cricketData->getTeamMatches($team->team_name);
            
            // Filter matches for this team using Cricbuzz API field names
            $teamMatches = collect($allMatches)->filter(function($match) use ($team) {
                $homeTeamName = $match['homeTeam'] ?? $match['team1'] ?? '';
                $awayTeamName = $match['awayTeam'] ?? $match['team2'] ?? '';
                $teamName = $team->team_name;
                
                // Try exact match first, then partial match
                $isHomeMatch = ($homeTeamName === $teamName) || (stripos($homeTeamName, $teamName) !== false);
                $isAwayMatch = ($awayTeamName === $teamName) || (stripos($awayTeamName, $teamName) !== false);
                
                return $isHomeMatch || $isAwayMatch;
            })->values();
            
            // Log for debugging
            Log::info('Team detail matches found', [
                'team_key' => $teamKey,
                'team_name' => $team->team_name,
                'total_matches' => count($allMatches),
                'team_matches' => count($teamMatches),
                'sample_match' => $allMatches[0] ?? 'No matches',
                'sample_team_names' => [
                    'home' => $allMatches[0]['homeTeam'] ?? $allMatches[0]['team1'] ?? 'Not found',
                    'away' => $allMatches[0]['awayTeam'] ?? $allMatches[0]['team2'] ?? 'Not found'
                ] ?? 'No matches'
            ]);
            
            // Additional debugging: Check first few matches for team names
            if (count($allMatches) > 0) {
                $firstFewMatches = array_slice($allMatches, 0, 5);
                $teamNamesInMatches = [];
                foreach ($firstFewMatches as $index => $match) {
                    $teamNamesInMatches[] = [
                        'match_' . $index => [
                            'home' => $match['homeTeam'] ?? $match['team1'] ?? 'Not found',
                            'away' => $match['awayTeam'] ?? $match['team2'] ?? 'Not found',
                            'status' => $match['status'] ?? $match['matchStatus'] ?? 'Not found'
                        ]
                    ];
                }
                Log::info('Team detail: First few matches team names', $teamNamesInMatches);
                
                // Test team name matching logic
                Log::info('Team detail: Team name matching test', [
                    'database_team_name' => $team->team_name,
                    'database_team_name_lower' => strtolower($team->team_name),
                    'sample_home_team' => $allMatches[0]['homeTeam'] ?? $allMatches[0]['team1'] ?? 'Not found',
                    'sample_home_team_lower' => strtolower($allMatches[0]['homeTeam'] ?? $allMatches[0]['team1'] ?? ''),
                    'sample_away_team' => $allMatches[0]['awayTeam'] ?? $allMatches[0]['team2'] ?? 'Not found',
                    'sample_away_team_lower' => strtolower($allMatches[0]['awayTeam'] ?? $allMatches[0]['team2'] ?? ''),
                    'exact_home_match' => ($allMatches[0]['homeTeam'] ?? $allMatches[0]['team1'] ?? '') === $team->team_name,
                    'exact_away_match' => ($allMatches[0]['awayTeam'] ?? $allMatches[0]['team2'] ?? '') === $team->team_name,
                    'partial_home_match' => stripos($allMatches[0]['homeTeam'] ?? $allMatches[0]['team1'] ?? '', $team->team_name) !== false,
                    'partial_away_match' => stripos($allMatches[0]['awayTeam'] ?? $allMatches[0]['team2'] ?? '', $team->team_name) !== false
                ]);
            }
            
            // Separate matches by status using Cricbuzz API field names
            $liveMatches = $teamMatches->filter(function($match) {
                $status = strtolower($match['status'] ?? $match['matchStatus'] ?? '');
                return $status === 'live' || $status === 'in progress';
            })->values();
            
            $completedMatches = $teamMatches->filter(function($match) {
                $status = strtolower($match['status'] ?? $match['matchStatus'] ?? '');
                return in_array($status, ['completed', 'finished', 'result']);
            })->values();
            
            $upcomingMatches = $teamMatches->filter(function($match) {
                $status = strtolower($match['status'] ?? $match['matchStatus'] ?? '');
                return in_array($status, ['scheduled', 'not started', '']) || empty($status);
            })->values();
            
            // Sort matches by date using Cricbuzz API field names
            $completedMatches = $completedMatches->sortByDesc('date')->values();
            $upcomingMatches = $upcomingMatches->sortBy('date')->values();
            
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

    /**
     * Get teams by league
     */
    public function getByLeague($leagueKey)
    {
        try {
            // For now, return empty teams as this method needs to be updated
            $teams = [];
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
     * Sync teams from Cricbuzz API to database
     */
    public function sync()
    {
        try {
            $apiTeams = $this->cricketData->getTeamsFromApi();
            
            if (empty($apiTeams)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No teams found in Cricbuzz API response'
                ]);
            }
            
            $created = 0;
            $updated = 0;
            
            foreach ($apiTeams as $apiTeam) {
                $teamKey = $apiTeam['id'] ?? null;
                $teamName = $apiTeam['name'] ?? '';
                $teamLogo = $apiTeam['imageId'] ?? null;
                
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
            Log::error('Error syncing teams from Cricbuzz API', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error syncing teams: ' . $e->getMessage()
            ]);
        }
    }
}


