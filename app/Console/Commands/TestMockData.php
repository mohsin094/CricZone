<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CricketDataService;

class TestMockData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cricbuzz:mock:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test mock data functionality and data structure';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Testing Mock Data Functionality...');
        $this->newLine();
        
        try {
            $cricketData = app(CricketDataService::class);
            
            // Enable mock data
            $cricketData->useMockData(true);
            $this->info('✅ Mock data enabled');
            
            // Test getAllMatches
            $this->info('📊 Testing getAllMatches()...');
            $allMatches = $cricketData->getAllMatches();
            $this->line("Total matches returned: " . count($allMatches));
            
            if (count($allMatches) > 0) {
                $this->line("First match structure:");
                $this->table(
                    ['Key', 'Value'],
                    array_map(function($key, $value) {
                        if (is_array($value)) {
                            return [$key, 'Array(' . count($value) . ' items)'];
                        }
                        return [$key, is_string($value) ? $value : json_encode($value)];
                    }, array_keys($allMatches[0]), $allMatches[0])
                );
            }
            
            // Test getMatchesByStatus
            $this->newLine();
            $this->info('📊 Testing getMatchesByStatus()...');
            $categorized = $cricketData->getMatchesByStatus();
            
            $this->table(
                ['Category', 'Count'],
                [
                    ['Live', count($categorized['live'])],
                    ['Today', count($categorized['today'])],
                    ['Upcoming', count($categorized['upcoming'])],
                    ['Finished', count($categorized['finished'])],
                    ['Cancelled', count($categorized['cancelled'])]
                ]
            );
            
            // Test individual methods
            $this->newLine();
            $this->info('📊 Testing individual methods...');
            
            $liveMatches = $cricketData->getLiveMatches();
            $this->line("Live matches: " . count($liveMatches));
            
            $upcomingMatches = $cricketData->getUpcomingMatches();
            $this->line("Upcoming matches: " . count($upcomingMatches));
            
            $todayMatches = $cricketData->getTodayMatches();
            $this->line("Today's matches: " . count($todayMatches));
            
            // Disable mock data
            $cricketData->useMockData(false);
            $this->newLine();
            $this->info('✅ Mock data disabled - back to normal API calls');
            
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
