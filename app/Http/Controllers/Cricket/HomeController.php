<?php

namespace App\Http\Controllers\Cricket;

use App\Http\Controllers\Controller;
use App\Services\CricketDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    protected $cricketData;

    public function __construct(CricketDataService $cricketData)
    {
        $this->cricketData = $cricketData;
    }

    /**
     * Display cricket home page
     */
    public function index(Request $request)
    {
        try {
            // Get all data from centralized service
            $homePageData = $this->cricketData->getHomePageData();
            $liveMatches = $homePageData['liveMatches'];
            $todayMatches = $homePageData['todayMatches'];
            $upcomingMatches = $homePageData['upcomingMatches'];
            $recentCompletedMatches = $homePageData['recentCompletedMatches'];
            
            // Filter out cancelled matches from live matches
            $liveMatches = $this->filterCancelledMatches($liveMatches);
            $todayMatches = $this->filterCancelledMatches($todayMatches);
            
            // Apply search filters if provided
            if ($request->filled('search')) {
                $liveMatches = $this->filterMatches($liveMatches, $request);
                $todayMatches = $this->filterMatches($todayMatches, $request);
                $upcomingMatches = $this->filterMatches($upcomingMatches, $request);
                $recentCompletedMatches = $this->filterMatches($recentCompletedMatches, $request);
            }
            
            // Process today's matches to fix template placeholders
            $todayMatches = $this->processTodayMatches($todayMatches);
            
            return view('cricket.index', compact(
                'liveMatches', 
                'todayMatches', 
                'upcomingMatches',
                'recentCompletedMatches'
            ));
            
        } catch (\Exception $e) {
            Log::error('Error in cricket home page', [
                'error' => $e->getMessage()
            ]);
            
            return view('cricket.index', [
                'liveMatches' => [],
                'todayMatches' => [],
                'upcomingMatches' => [],
                'recentCompletedMatches' => []
            ]);
        }
    }

    /**
     * Refresh data endpoint
     */
    public function refreshData()
    {
        try {
            // Clear cache to force refresh
            $this->cricketData->clearCache();
            
            return redirect()->back()->with('success', 'Data refreshed successfully!');
            
        } catch (\Exception $e) {
            Log::error('Error refreshing data', [
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Failed to refresh data');
        }
    }

    /**
     * Filter out cancelled matches from a list of matches
     */
    private function filterCancelledMatches($matches)
    {
        if (empty($matches)) return $matches;
        
        return array_filter($matches, function($match) {
            $status = strtolower($match['status'] ?? '');
            return $status !== 'cancelled' && $status !== 'abandoned' && $status !== 'no result';
        });
    }

    /**
     * Filter for completed matches only
     */
    private function filterCompletedMatches($matches)
    {
        if (empty($matches)) return $matches;
        
        return array_filter($matches, function($match) {
            $status = strtolower($match['event_status'] ?? '');
            return in_array($status, ['finished', 'completed', 'ended', 'final']);
        });
    }

    /**
     * Filter matches based on request parameters
     */
    private function filterMatches($matches, Request $request)
    {
        $filteredMatches = $matches;
        
        // Filter by series/tournament
        if ($request->filled('series')) {
            $filteredMatches = array_filter($filteredMatches, function($match) use ($request) {
                return ($match['seriesName'] ?? '') === $request->series;
            });
        }
        
        // Filter by team
        if ($request->filled('team')) {
            $filteredMatches = array_filter($filteredMatches, function($match) use ($request) {
                $homeTeam = $match['teamInfo'][0]['name'] ?? '';
                $awayTeam = $match['teamInfo'][1]['name'] ?? '';
                return stripos($homeTeam, $request->team) !== false ||
                       stripos($awayTeam, $request->team) !== false;
            });
        }
        
        // Filter by match type
        if ($request->filled('match_type')) {
            $filteredMatches = array_filter($filteredMatches, function($match) use ($request) {
                return ($match['matchType'] ?? '') === $request->match_type;
            });
        }
        
        // Search query
        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $filteredMatches = array_filter($filteredMatches, function($match) use ($search) {
                $homeTeam = $match['teamInfo'][0]['name'] ?? '';
                $awayTeam = $match['teamInfo'][1]['name'] ?? '';
                $seriesName = $match['seriesName'] ?? '';
                $venue = $match['venue'] ?? '';
                
                return stripos($homeTeam, $search) !== false ||
                       stripos($awayTeam, $search) !== false ||
                       stripos($seriesName, $search) !== false ||
                       stripos($venue, $search) !== false;
            });
        }
        
        return array_values($filteredMatches);
    }

    /**
     * Process today's matches to add status information
     */
    private function processTodayMatches($todayMatches)
    {
        if (empty($todayMatches)) return $todayMatches;
        
        foreach ($todayMatches as &$match) {
            // Add status info for today's matches
            $match['status_info'] = $this->processMatchStatusInfo($match);
        }
        
        return $todayMatches;
    }
    
    /**
     * Process match status info for display
     */
    private function processMatchStatusInfo($match)
    {
        $status = $match['status'] ?? '';
        $matchStarted = $match['matchStarted'] ?? false;
        $matchEnded = $match['matchEnded'] ?? false;
        
        if ($matchEnded) {
            return 'Match finished';
        } elseif ($matchStarted) {
            return 'Match in progress';
        } elseif ($status === 'Match not started') {
            // Calculate time until match starts
            $matchDateTime = null;
            if (isset($match['dateTimeGMT'])) {
                $matchDateTime = \Carbon\Carbon::parse($match['dateTimeGMT']);
            } elseif (isset($match['date'])) {
                $matchDateTime = \Carbon\Carbon::parse($match['date'] . ' 00:00:00');
            }
            
            if ($matchDateTime && $matchDateTime->isFuture()) {
                $now = \Carbon\Carbon::now();
                $diff = $now->diff($matchDateTime);
                
                $hours = $diff->h + ($diff->days * 24);
                $minutes = $diff->i;
                
                if ($hours > 0) {
                    return "Match starts in {$hours}h {$minutes}m";
                } else {
                    return "Match starts in {$minutes}m";
                }
            } else {
                return "Match scheduled for today";
            }
        }
        
        return $status;
    }
}
