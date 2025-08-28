<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CricketApiService;
use App\Models\Team;

class SyncTeams extends Command
{
    protected $signature = 'teams:sync';
    protected $description = 'Sync teams from Cricket API to database';

    public function handle()
    {
        $this->info('Starting teams sync...');
        
        try {
            $cricketApi = new CricketApiService();
            $apiTeams = $cricketApi->getMensTeams();
            
            if (empty($apiTeams)) {
                $this->error('No teams found in API response');
                return 1;
            }
            
            $this->info('Found ' . count($apiTeams) . ' teams in API');
            
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
                    $this->info("Created: {$teamName}");
                } else {
                    $updated++;
                    $this->line("Updated: {$teamName}");
                }
            }
            
            $this->info("\nSync completed! Created: {$created}, Updated: {$updated}");
            return 0;
            
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }
}

