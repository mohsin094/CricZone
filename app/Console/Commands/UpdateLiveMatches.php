<?php

namespace App\Console\Commands;

use App\Services\LiveMatchService;
use Illuminate\Console\Command;

class UpdateLiveMatches extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'cricket:update-live-matches';

    /**
     * The console command description.
     */
    protected $description = 'Update live cricket matches and broadcast changes via WebSocket';

    protected $liveMatchService;

    public function __construct(LiveMatchService $liveMatchService)
    {
        parent::__construct();
        $this->liveMatchService = $liveMatchService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating live matches...');
        
        try {
            $this->liveMatchService->updateLiveMatches();
            $this->info('Live matches updated successfully');
        } catch (\Exception $e) {
            $this->error('Error updating live matches: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
