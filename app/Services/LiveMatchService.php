<?php

namespace App\Services;

use App\Events\LiveMatchUpdate;
use App\Services\CricketDataService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LiveMatchService
{
    protected $cricketDataService;

    public function __construct(CricketDataService $cricketDataService)
    {
        $this->cricketDataService = $cricketDataService;
    }

    /**
     * Get live matches and broadcast updates
     */
    public function updateLiveMatches()
    {
        try {
            // Get live matches from the API
            $liveMatches = $this->cricketDataService->getLiveMatches();
            
            // Only proceed if there are live matches
            if (empty($liveMatches)) {
                Log::info("No live matches found, skipping update");
                return;
            }

            Log::info("Found " . count($liveMatches) . " live matches, checking for updates");

            $updatedMatches = 0;
            foreach ($liveMatches as $match) {
                $matchKey = $match['event_key'] ?? null;
                if (!$matchKey) {
                    continue;
                }

                // Get cached match data for comparison
                $cachedMatch = Cache::get("live_match_{$matchKey}");
                
                // Check if match data has changed
                if ($this->hasMatchDataChanged($cachedMatch, $match)) {
                    // Update cache
                    Cache::put("live_match_{$matchKey}", $match, now()->addMinutes(5));
                    
                    // Broadcast update
                    event(new LiveMatchUpdate($match, $matchKey));
                    
                    $updatedMatches++;
                    Log::info("Live match updated: {$matchKey}");
                }
            }

            Log::info("Live match update completed. {$updatedMatches} matches updated out of " . count($liveMatches) . " total live matches");
        } catch (\Exception $e) {
            Log::error("Error updating live matches: " . $e->getMessage());
        }
    }

    /**
     * Check if match data has changed
     */
    private function hasMatchDataChanged($cachedMatch, $newMatch)
    {
        if (!$cachedMatch) {
            return true;
        }

        // Compare key fields that indicate a change
        $keyFields = [
            'event_home_final_result',
            'event_away_final_result',
            'event_home_overs',
            'event_away_overs',
            'event_status',
            'event_status_info',
            'event_state_title',
            'status',
            'state',
            'stateTitle'
        ];

        foreach ($keyFields as $field) {
            $cachedValue = $cachedMatch[$field] ?? null;
            $newValue = $newMatch[$field] ?? null;
            
            if ($cachedValue !== $newValue) {
                return true;
            }
        }

        // Check for new comments/ball-by-ball updates
        if (isset($newMatch['comments']['Live']) && isset($cachedMatch['comments']['Live'])) {
            $newComments = $newMatch['comments']['Live'];
            $cachedComments = $cachedMatch['comments']['Live'];
            
            if (count($newComments) > count($cachedComments)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get live match data for a specific match
     */
    public function getLiveMatchData($matchKey)
    {
        return Cache::get("live_match_{$matchKey}");
    }

    /**
     * Get all live matches
     */
    public function getAllLiveMatches()
    {
        $liveMatches = $this->cricketDataService->getLiveMatches();
        
        // Cache each match
        foreach ($liveMatches as $match) {
            $matchKey = $match['event_key'] ?? null;
            if ($matchKey) {
                Cache::put("live_match_{$matchKey}", $match, now()->addMinutes(5));
            }
        }
        
        return $liveMatches;
    }

    /**
     * Check if a match is live
     */
    public function isMatchLive($matchKey)
    {
        $match = Cache::get("live_match_{$matchKey}");
        return $match && isset($match['event_status']) && 
               strpos(strtolower($match['event_status']), 'live') !== false;
    }
}
