<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Team;
use App\Services\CricketApiService;
use Illuminate\Support\Facades\Log;

class TeamsSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Starting Teams Seeder...');
        
        try {
            $apiService = new CricketApiService();
            $teams = []; // Initialize teams array
            
            $this->command->info('Fetching teams from API...');
            $teams = $apiService->getTeams();
            
            $this->command->info('API Response type: ' . gettype($teams));
            $this->command->info('API Response: ' . json_encode($teams));
            
            if (empty($teams)) {
                $this->command->warn('No teams found in getTeams() response, trying to get teams by league...');
                
                // Try to get teams from specific leagues
                $leagues = $apiService->getLeagues();
                if (!empty($leagues)) {
                    $this->command->info('Found ' . count($leagues) . ' leagues, trying to get teams from more leagues...');
                    
                    // Try more leagues to get more teams
                    $leaguesToTry = array_slice($leagues, 0, 20); // Try first 20 leagues instead of just 3
                    $this->command->info('Will try ' . count($leaguesToTry) . ' leagues to get more teams...');
                    
                    $leagueCount = 0;
                    foreach ($leaguesToTry as $league) {
                        $leagueCount++;
                        $leagueKey = $league['league_key'] ?? null;
                        if ($leagueKey) {
                            $this->command->info('[' . $leagueCount . '/20] Trying league: ' . ($league['league_name'] ?? 'Unknown') . ' (Key: ' . $leagueKey . ')');
                            $leagueTeams = $apiService->getTeamsByLeague($leagueKey);
                            if (!empty($leagueTeams)) {
                                $this->command->info('Found ' . count($leagueTeams) . ' teams in league ' . $league['league_name']);
                                $teams = array_merge($teams, $leagueTeams);
                                
                                // Remove duplicates based on team_key
                                $uniqueTeams = [];
                                $seenKeys = [];
                                foreach ($teams as $team) {
                                    if (!in_array($team['team_key'], $seenKeys)) {
                                        $uniqueTeams[] = $team;
                                        $seenKeys[] = $team['team_key'];
                                    }
                                }
                                $teams = $uniqueTeams;
                                $this->command->info('After deduplication: ' . count($teams) . ' unique teams');
                                
                                // Stop if we have a good number of teams (e.g., 100+)
                                if (count($teams) >= 100) {
                                    $this->command->info('Reached target of 100+ teams, stopping league iteration');
                                    break;
                                }
                            }
                        }
                    }
                }
                
                if (empty($teams)) {
                    $this->command->warn('No teams found in leagues, trying to extract from events...');
                    
                    // Try to extract teams from events as fallback
                    $events = $apiService->getEvents();
                    if (!empty($events)) {
                        $this->command->info('Found ' . count($events) . ' events, extracting teams...');
                        $teams = $this->extractTeamsFromEvents($events);
                        $this->command->info('Extracted ' . count($teams) . ' teams from events');
                    }
                    
                    if (empty($teams)) {
                        $this->command->error('No teams found in API response, leagues, or events');
                        return;
                    }
                }
            }
            
            // Final deduplication before saving
            $uniqueTeams = [];
            $seenKeys = [];
            foreach ($teams as $team) {
                if (!in_array($team['team_key'], $seenKeys)) {
                    $uniqueTeams[] = $team;
                    $seenKeys[] = $team['team_key'];
                }
            }
            $teams = $uniqueTeams;
            
            $this->command->info('Final count: ' . count($teams) . ' unique teams found. Starting to save...');
            
            $savedCount = 0;
            $updatedCount = 0;
            
            foreach ($teams as $team) {
                if (empty($team['team_key']) || empty($team['team_name'])) {
                    $this->command->warn('Skipping team with missing data: ' . json_encode($team));
                    continue;
                }
                
                $teamData = [
                    'team_key' => $team['team_key'],
                    'team_name' => $team['team_name'],
                    'team_logo' => $team['team_logo'] ?? null,
                    'cached_at' => now(),
                ];
                
                $existingTeam = Team::where('team_key', $team['team_key'])->first();
                
                if ($existingTeam) {
                    $existingTeam->update($teamData);
                    $updatedCount++;
                    $this->command->info('Updated: ' . $team['team_name']);
                } else {
                    Team::create($teamData);
                    $savedCount++;
                    $this->command->info('Created: ' . $team['team_name']);
                }
            }
            
            $this->command->info('Teams seeding completed!');
            $this->command->info('Created: ' . $savedCount . ' teams');
            $this->command->info('Updated: ' . $updatedCount . ' teams');
            $this->command->info('Total teams in database: ' . Team::count());
            
        } catch (\Exception $e) {
            $this->command->error('Error seeding teams: ' . $e->getMessage());
            Log::error('TeamsSeeder error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Extract unique teams from events data
     */
    private function extractTeamsFromEvents($events)
    {
        $teams = [];
        $teamKeys = [];
        
        foreach ($events as $event) {
            // Extract home team
            if (!empty($event['event_home_team']) && !empty($event['home_team_key'])) {
                $homeTeamKey = $event['home_team_key'];
                if (!in_array($homeTeamKey, $teamKeys)) {
                    $teams[] = [
                        'team_key' => $homeTeamKey,
                        'team_name' => $event['event_home_team'],
                        'team_logo' => $event['home_team_logo'] ?? null,
                    ];
                    $teamKeys[] = $homeTeamKey;
                }
            }
            
            // Extract away team
            if (!empty($event['event_away_team']) && !empty($event['away_team_key'])) {
                $awayTeamKey = $event['away_team_key'];
                if (!in_array($awayTeamKey, $teamKeys)) {
                    $teams[] = [
                        'team_key' => $awayTeamKey,
                        'team_name' => $event['event_away_team'],
                        'team_logo' => $event['away_team_logo'] ?? null,
                    ];
                    $teamKeys[] = $awayTeamKey;
                }
            }
        }
        
        return $teams;
    }
}