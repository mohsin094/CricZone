<?php

namespace App\Console\Commands;

use App\Services\CricketApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncCricketData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cricket:sync {--type=all : Type of data to sync (all, leagues, teams, matches)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync cricket data from API-Cricket.com';

    /**
     * Execute the console command.
     */
    public function handle(CricketApiService $cricketApi)
    {
        $type = $this->option('type');
        
        $this->info("Starting cricket data sync for type: {$type}");
        
        try {
            switch ($type) {
                case 'leagues':
                    $this->syncLeagues($cricketApi);
                    break;
                case 'teams':
                    $this->syncTeams($cricketApi);
                    break;
                case 'matches':
                    $this->syncMatches($cricketApi);
                    break;
                case 'all':
                default:
                    $this->syncLeagues($cricketApi);
                    $this->syncTeams($cricketApi);
                    $this->syncMatches($cricketApi);
                    break;
            }
            
            $this->info('Cricket data sync completed successfully!');
            
        } catch (\Exception $e) {
            $this->error('Error during cricket data sync: ' . $e->getMessage());
            Log::error('Cricket data sync error', ['error' => $e->getMessage()]);
            return 1;
        }
        
        return 0;
    }
    
    private function syncLeagues(CricketApiService $cricketApi)
    {
        $this->info('Syncing leagues...');
        
        $leagues = $cricketApi->getLeagues();
        
        if (empty($leagues)) {
            $this->warn('No leagues found from API');
            return;
        }
        
        $this->info("Found " . count($leagues) . " leagues");
        
        foreach ($leagues as $league) {
            $this->line("  - {$league['league_name']} ({$league['league_year']})");
        }
        
        $this->info('Leagues sync completed');
    }
    
    private function syncTeams(CricketApiService $cricketApi)
    {
        $this->info('Syncing teams...');
        
        $teams = $cricketApi->getTeams();
        
        if (empty($teams)) {
            $this->warn('No teams found from API');
            return;
        }
        
        $this->info("Found " . count($teams) . " teams");
        
        foreach (array_slice($teams, 0, 10) as $team) {
            $this->line("  - {$team['team_name']}");
        }
        
        if (count($teams) > 10) {
            $this->line("  ... and " . (count($teams) - 10) . " more teams");
        }
        
        $this->info('Teams sync completed');
    }
    
    private function syncMatches(CricketApiService $cricketApi)
    {
        $this->info('Syncing matches...');
        
        // Get today's matches
        $today = now()->format('Y-m-d');
        $matches = $cricketApi->getEvents($today, $today);
        
        if (empty($matches)) {
            $this->warn('No matches found for today');
            return;
        }
        
        $this->info("Found " . count($matches) . " matches for today");
        
        foreach (array_slice($matches, 0, 5) as $match) {
            $this->line("  - {$match['event_home_team']} vs {$match['event_away_team']} ({$match['event_status']})");
        }
        
        if (count($matches) > 5) {
            $this->line("  ... and " . (count($matches) - 5) . " more matches");
        }
        
        // Get live matches
        $liveMatches = $cricketApi->getLiveScores();
        
        if (!empty($liveMatches)) {
            $this->info("Found " . count($liveMatches) . " live matches");
            
            foreach ($liveMatches as $match) {
                $this->line("  🔥 {$match['event_home_team']} vs {$match['event_away_team']} - {$match['event_service_home']} vs {$match['event_service_away']}");
            }
        }
        
        $this->info('Matches sync completed');
    }
}

