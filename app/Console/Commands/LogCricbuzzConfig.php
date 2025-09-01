<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CricketApiService;

class LogCricbuzzConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cricbuzz:config';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Log Cricbuzz API configuration for debugging';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Logging Cricbuzz API Configuration...');
        
        try {
            $cricketApi = app(CricketApiService::class);
            $cricketApi->logApiConfiguration();
            
            $this->info('✅ Configuration logged to Laravel logs');
            $this->line('Check your logs at: storage/logs/laravel.log');
            
        } catch (\Exception $e) {
            $this->error('❌ Error logging configuration: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
