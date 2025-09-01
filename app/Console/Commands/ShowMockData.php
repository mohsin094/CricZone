<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CricketDataService;

class ShowMockData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cricbuzz:mock:show {type? : Type of data to show (live, upcoming, finished, teams, or all)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show mock data for Cricbuzz API testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->argument('type') ?? 'all';
        
        try {
            $cricketData = app(CricketDataService::class);
            $mockData = $cricketData->getMockData($type);
            
            if ($type === 'all') {
                $this->info('🏏 All Mock Data Available:');
                $this->newLine();
                
                foreach ($mockData as $dataType => $data) {
                    $this->info("📊 {$dataType} (" . count($data) . " items):");
                    $this->table(
                        ['ID', 'Name', 'Status', 'Date', 'Teams', 'Details'],
                        array_map(function($item) {
                            return [
                                $item['id'] ?? 'N/A',
                                $item['name'] ?? 'N/A',
                                $item['status'] ?? 'N/A',
                                $item['date'] ?? 'N/A',
                                isset($item['homeTeam']) ? $item['homeTeam'] . ' vs ' . $item['awayTeam'] : 'N/A',
                                $item['score'] ?? $item['result'] ?? $item['venue'] ?? 'N/A'
                            ];
                        }, $data)
                    );
                    $this->newLine();
                }
            } else {
                $this->info("🏏 Mock Data for: {$type}");
                $this->table(
                    ['ID', 'Name', 'Status', 'Date', 'Teams', 'Details'],
                    array_map(function($item) {
                        return [
                            $item['id'] ?? 'N/A',
                            $item['name'] ?? 'N/A',
                            $item['status'] ?? 'N/A',
                            $item['date'] ?? 'N/A',
                            isset($item['homeTeam']) ? $item['homeTeam'] . ' vs ' . $item['awayTeam'] : 'N/A',
                            $item['score'] ?? $item['result'] ?? $item['venue'] ?? 'N/A'
                        ];
                    }, $mockData)
                );
            }
            
            $this->newLine();
            $this->info('💡 To use this mock data:');
            $this->line('  php artisan cricbuzz:mock enable');
            
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
