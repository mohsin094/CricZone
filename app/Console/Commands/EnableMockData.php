<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CricketDataService;

class EnableMockData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cricbuzz:mock {action : enable or disable mock data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enable or disable mock data for Cricbuzz API testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = strtolower($this->argument('action'));
        
        try {
            $cricketData = app(CricketDataService::class);
            
            if ($action === 'enable') {
                $cricketData->useMockData(true);
                $this->info('✅ Mock data enabled! No API calls will be made.');
                $this->line('Use this for testing without hitting the Cricbuzz API.');
                $this->line('Run: php artisan cricbuzz:mock disable to turn off mock data.');
                
            } elseif ($action === 'disable') {
                $cricketData->useMockData(false);
                $this->info('✅ Mock data disabled! API calls will be made normally.');
                $this->line('The application will now use the real Cricbuzz API.');
                
            } else {
                $this->error('❌ Invalid action. Use "enable" or "disable"');
                $this->line('Examples:');
                $this->line('  php artisan cricbuzz:mock enable');
                $this->line('  php artisan cricbuzz:mock disable');
                return 1;
            }
            
            // Show current status
            $isMockEnabled = $cricketData->isMockEnabled();
            $this->newLine();
            $this->info('Current Status:');
            $this->line('Mock Data: ' . ($isMockEnabled ? '✅ Enabled' : '❌ Disabled'));
            
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
