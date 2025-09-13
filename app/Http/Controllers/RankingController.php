<?php

namespace App\Http\Controllers;

use App\Services\RankingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RankingController extends Controller
{
    protected $rankingService;

    public function __construct(RankingService $rankingService)
    {
        $this->rankingService = $rankingService;
    }

    /**
     * Show rankings index page
     */
    public function index(Request $request)
    {
        $category = $request->get('category', 'men');
        $type = $request->get('type', 'team');

        // Validate inputs
        $category = in_array($category, ['men', 'women']) ? $category : 'men';
        $type = in_array($type, ['team', 'batter', 'bowler', 'all_rounder']) ? $type : 'team';

        try {
            // Fetch rankings data for all formats
            $rankingsData = [];
            $formats = ['odi', 't20', 'test'];

            foreach ($formats as $format) {
                if ($type === 'team') {
                    $rankings = $this->rankingService->getTeamRankings($category, $format, 50);
                    $topRanking = $this->rankingService->getTopTeam($category, $format);
                } else {
                    $rankings = $this->rankingService->getPlayerRankings($category, $type, $format, 50);
                    $topRanking = $this->rankingService->getTopPlayer($category, $type, $format);
                }

                $rankingsData[$format] = [
                    'rankings' => $rankings,
                    'topRanking' => $topRanking
                ];
            }

            return view('cricket.rankings.index', [
                'category' => $category,
                'type' => $type,
                'formats' => $formats,
                'categories' => ['men', 'women'],
                'types' => ['team', 'batter', 'bowler', 'all_rounder'],
                'rankingsData' => $rankingsData
            ]);
        } catch (\Exception $e) {
            Log::error('Error in ranking index', [
                'category' => $category,
                'type' => $type,
                'error' => $e->getMessage()
            ]);

            return view('cricket.rankings.index', [
                'category' => $category,
                'type' => $type,
                'formats' => ['odi', 't20', 'test'],
                'categories' => ['men', 'women'],
                'types' => ['team', 'batter', 'bowler', 'all_rounder'],
                'rankingsData' => [],
                'error' => 'Failed to load rankings data'
            ]);
        }
    }

    /**
     * Manually trigger rankings update (for testing)
     */
    public function updateRankings()
    {
        try {
             // Update all rankings
            $this->rankingService->updateAllRankings();
            $message = "Updated all rankings successfully";

            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating rankings', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update rankings: ' . $e->getMessage()
            ], 500);
        }
    }

}