<?php

namespace App\Console\Commands;

use App\Services\RankingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateRankingsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rankings:update 
                            {--force : Force update even if not needed}
                            {--category= : Update specific category (men/women)}
                            {--type= : Update specific type (team/batter/bowler/all_rounder)}
                            {--format= : Update specific format (odi/t20/test)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update cricket rankings from Cricbuzz API';

    /**
     * The ranking service instance.
     *
     * @var RankingService
     */
    protected $rankingService;

    /**
     * Create a new command instance.
     */
    public function __construct(RankingService $rankingService)
    {
        parent::__construct();
        $this->rankingService = $rankingService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting rankings update process...');

        try {
            $force = $this->option('force');
            $category = $this->option('category');
            $type = $this->option('type');
            $format = $this->option('format');

            // If specific options are provided, update only those
            if ($category || $type || $format) {
                $this->updateSpecificRankings($category, $type, $format, $force);
            } else {
                // Update all rankings
                $this->updateAllRankings($force);
            }

            $this->info('Rankings update completed successfully!');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error updating rankings: ' . $e->getMessage());
            Log::error('Rankings update command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return Command::FAILURE;
        }
    }

    /**
     * Update all rankings
     */
    protected function updateAllRankings($force = false)
    {
        $this->info('Updating all rankings...');

        if (!$force) {
            // Check if update is needed
            $needsUpdate = $this->checkIfUpdateNeeded();
            if (!$needsUpdate) {
                $this->info('Rankings are up to date. Use --force to update anyway.');
                return;
            }
        }

        $success = $this->rankingService->updateAllRankings();

        if ($success) {
            $this->info('All rankings updated successfully!');
        } else {
            $this->error('Failed to update rankings.');
        }
    }

    /**
     * Update specific rankings
     */
    protected function updateSpecificRankings($category, $type, $format, $force = false)
    {
        $this->info("Updating specific rankings: Category={$category}, Type={$type}, Format={$format}");

        if (!$force) {
            // Check if update is needed for specific category/type/format
            $needsUpdate = $this->rankingService->needsUpdate($category, $format, $type);
            if (!$needsUpdate) {
                $this->info('These rankings are up to date. Use --force to update anyway.');
                return;
            }
        }

        try {
            if ($type === 'team') {
                $this->rankingService->updateTeamRankingsForFormat($category, $format);
                $this->info("Team rankings updated for {$category} {$format}");
            } else {
                $this->rankingService->updatePlayerRankingsForType($category, $type, $format);
                $this->info("Player rankings updated for {$category} {$type} {$format}");
            }
        } catch (\Exception $e) {
            $this->error("Failed to update specific rankings: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Check if update is needed
     */
    protected function checkIfUpdateNeeded()
    {
        $categories = ['men', 'women'];
        $formats = ['odi', 't20', 'test'];
        $types = ['batter', 'bowler', 'all_rounder'];

        $needsUpdate = false;

        // Check team rankings
        foreach ($categories as $category) {
            foreach ($formats as $format) {
                if ($this->rankingService->needsUpdate($category, $format)) {
                    $this->info("Team rankings need update: {$category} {$format}");
                    $needsUpdate = true;
                }
            }
        }

        // Check player rankings
        foreach ($categories as $category) {
            foreach ($types as $type) {
                foreach ($formats as $format) {
                    if ($this->rankingService->needsUpdate($category, $format, $type)) {
                        $this->info("Player rankings need update: {$category} {$type} {$format}");
                        $needsUpdate = true;
                    }
                }
            }
        }

        return $needsUpdate;
    }

    /**
     * Display ranking statistics
     */
    protected function displayRankingStats()
    {
        $this->info('Current ranking statistics:');

        $categories = ['men', 'women'];
        $formats = ['odi', 't20', 'test'];
        $types = ['batter', 'bowler', 'all_rounder'];

        // Team rankings
        $this->info('Team Rankings:');
        foreach ($categories as $category) {
            foreach ($formats as $format) {
                $count = \App\Models\TeamRanking::category($category)->format($format)->count();
                $this->line("  {$category} {$format}: {$count} teams");
            }
        }

        // Player rankings
        $this->info('Player Rankings:');
        foreach ($categories as $category) {
            foreach ($types as $type) {
                foreach ($formats as $format) {
                    $count = \App\Models\PlayerRanking::category($category)->type($type)->format($format)->count();
                    $this->line("  {$category} {$type} {$format}: {$count} players");
                }
            }
        }
    }
}
