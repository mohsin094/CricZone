<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CricketApiService;
use Illuminate\Support\Facades\Log;

class TestTeamsApi extends Command
{
    protected $signature = 'teams:test';
    protected $description = 'Test the teams API endpoint';

    public function handle()
    {
        $this->info('Testing Teams API...');
        
        try {
            $apiService = new CricketApiService();
            
            $this->info('Making API call to get_teams...');
            $teams = $apiService->getTeams();
            
            $this->info('API Response:');
            $this->info('Type: ' . gettype($teams));
            $this->info('Count: ' . (is_array($teams) ? count($teams) : 'N/A'));
            
            if (is_array($teams) && !empty($teams)) {
                $this->info('First team sample:');
                $this->info(json_encode($teams[0], JSON_PRETTY_PRINT));
            } else {
                $this->info('No teams returned or empty response');
            }
            
            // Check logs
            $this->info('Check storage/logs/laravel.log for detailed API logs');
            
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            $this->error('Trace: ' . $e->getTraceAsString());
        }
    }
}

