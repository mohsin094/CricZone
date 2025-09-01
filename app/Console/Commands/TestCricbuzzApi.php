<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CricketApiService;
use App\Services\CricketDataService;

class TestCricbuzzApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cricbuzz:test {--type=status : Test type: status, matches, teams, or all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Cricbuzz API integration and check subscription status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Cricbuzz API Integration...');
        $this->newLine();

        $type = $this->option('type');
        
        try {
            $cricketApi = app(CricketApiService::class);
            $cricketData = app(CricketDataService::class);

            switch ($type) {
                case 'status':
                    $this->testApiStatus($cricketApi);
                    break;
                case 'matches':
                    $this->testMatches($cricketApi);
                    break;
                case 'teams':
                    $this->testTeams($cricketApi);
                    break;
                case 'all':
                    $this->testApiStatus($cricketApi);
                    $this->newLine();
                    $this->testMatches($cricketApi);
                    $this->newLine();
                    $this->testTeams($cricketApi);
                    break;
                default:
                    $this->error('Invalid test type. Use: status, matches, teams, or all');
                    return 1;
            }

        } catch (\Exception $e) {
            $this->error('Error testing API: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function testApiStatus($cricketApi)
    {
        $this->info('🔍 Checking API Status...');
        
        // Log complete configuration first
        $cricketApi->logApiConfiguration();
        
        $status = $cricketApi->checkApiStatus();
        
        $this->table(
            ['Status', 'Value'],
            [
                ['Status Code', $status['status_code']],
                ['Is Subscribed', $status['is_subscribed'] ? '✅ Yes' : '❌ No'],
                ['Rate Limited', $status['rate_limited'] ? '⚠️ Yes' : '✅ No'],
                ['API Key Valid', $status['api_key_valid'] ? '✅ Yes' : '❌ No'],
                ['Headers Set', $status['headers_set'] ? '✅ Yes' : '❌ No'],
                ['Full URL', $status['full_url'] ?? 'N/A'],
            ]
        );

        if (!$status['is_subscribed']) {
            $this->error('❌ API Subscription Required!');
            $this->line('Please visit: https://rapidapi.com/apiservicesprovider/api/cricbuzz-cricket2/');
            $this->line('Sign up and subscribe to get your API key.');
        }

        if ($status['rate_limited']) {
            $this->warn('⚠️ API Rate Limited!');
            $this->line('Consider increasing cache duration or upgrading your plan.');
        }

        if (!$status['api_key_valid']) {
            $this->error('❌ API Key Not Set!');
            $this->line('Please set CRICBUZZ_API_KEY in your .env file.');
        }
    }

    private function testMatches($cricketApi)
    {
        $this->info('🏏 Testing Matches API...');
        
        try {
            $liveMatches = $cricketApi->getLiveMatches();
            $this->info("Live Matches: " . count($liveMatches));
            
            $upcomingMatches = $cricketApi->getUpcomingMatches();
            $this->info("Upcoming Matches: " . count($upcomingMatches));
            
            $completedMatches = $cricketApi->getCompletedMatches();
            $this->info("Completed Matches: " . count($completedMatches));
            
            if (count($liveMatches) > 0) {
                $this->info("Sample Live Match: " . ($liveMatches[0]['name'] ?? 'N/A'));
            }
            
        } catch (\Exception $e) {
            $this->error('Error fetching matches: ' . $e->getMessage());
        }
    }

    private function testTeams($cricketApi)
    {
        $this->info('🏏 Testing Teams API...');
        
        try {
            $teams = $cricketApi->getTeams();
            $this->info("Total Teams: " . count($teams));
            
            if (count($teams) > 0) {
                $this->info("Sample Team: " . ($teams[0]['name'] ?? 'N/A'));
            }
            
        } catch (\Exception $e) {
            $this->error('Error fetching teams: ' . $e->getMessage());
        }
    }
}
