<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CricketApiService;
use App\Models\Team;
use Illuminate\Support\Facades\Log;

class SyncTeamsFromApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'teams:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync teams from Cricket API to database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting teams sync from Cricket API...');
        
        try {
            $cricketApi = new CricketApiService();
            
            // Get teams from API
            $this->info('Fetching teams from API...');
            $apiTeams = $cricketApi->getMensTeams();
            
            if (empty($apiTeams)) {
                $this->error('No teams found in API response');
                return 1;
            }
            
            $this->info('Found ' . count($apiTeams) . ' teams in API');
            
            $created = 0;
            $updated = 0;
            $skipped = 0;
            
            foreach ($apiTeams as $apiTeam) {
                $teamKey = $apiTeam['team_key'] ?? null;
                $teamName = $apiTeam['team_name'] ?? '';
                $teamLogo = $apiTeam['team_logo'] ?? null;
                
                if (!$teamKey || !$teamName) {
                    $this->warn("Skipping team with missing key or name: " . json_encode($apiTeam));
                    $skipped++;
                    continue;
                }
                
                // Check if team already exists
                $existingTeam = Team::where('team_key', $teamKey)->first();
                
                if ($existingTeam) {
                    // Update existing team
                    $existingTeam->update([
                        'team_name' => $teamName,
                        'team_logo' => $teamLogo,
                        'cached_at' => now()
                    ]);
                    $updated++;
                    $this->line("Updated: {$teamName}");
                } else {
                    // Create new team
                    Team::create([
                        'team_key' => $teamKey,
                        'team_name' => $teamName,
                        'team_logo' => $teamLogo,
                        'cached_at' => now()
                    ]);
                    $created++;
                    $this->info("Created: {$teamName}");
                }
            }
            
            $this->info("\nSync completed successfully!");
            $this->info("Created: {$created} teams");
            $this->info("Updated: {$updated} teams");
            $this->info("Skipped: {$skipped} teams");
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('Error during teams sync: ' . $e->getMessage());
            Log::error('Teams sync error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
}

