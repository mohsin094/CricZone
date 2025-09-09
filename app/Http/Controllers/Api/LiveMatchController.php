<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LiveMatchService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LiveMatchController extends Controller
{
    protected $liveMatchService;

    public function __construct(LiveMatchService $liveMatchService)
    {
        $this->liveMatchService = $liveMatchService;
    }

    /**
     * Get all live matches
     */
    public function index(): JsonResponse
    {
        try {
            $liveMatches = $this->liveMatchService->getAllLiveMatches();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'live_matches' => $liveMatches,
                    'count' => count($liveMatches),
                    'timestamp' => now()->toISOString()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching live matches',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific live match
     */
    public function show(string $matchKey): JsonResponse
    {
        try {
            $match = $this->liveMatchService->getLiveMatchData($matchKey);
            
            if (!$match) {
                return response()->json([
                    'success' => false,
                    'message' => 'Match not found or not live'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'match' => $match,
                    'timestamp' => now()->toISOString()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching match data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if match is live
     */
    public function isLive(string $matchKey): JsonResponse
    {
        try {
            $isLive = $this->liveMatchService->isMatchLive($matchKey);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'is_live' => $isLive,
                    'match_key' => $matchKey,
                    'timestamp' => now()->toISOString()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error checking match status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update live matches (for cron job)
     */
    public function update(): JsonResponse
    {
        try {
            $this->liveMatchService->updateLiveMatches();
            
            return response()->json([
                'success' => true,
                'message' => 'Live matches updated successfully',
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating live matches',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
