<?php

namespace App\Http\Controllers;

use App\Services\CricketApiService;
use App\Services\NewsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CricketController extends Controller
{
    protected $cricketApi;
    protected $newsService;

    public function __construct(CricketApiService $cricketApi, NewsService $newsService)
    {
        $this->cricketApi = $cricketApi;
        $this->newsService = $newsService;
    }

    public function index(Request $request)
    {
        try {
            // Use the new CricketDataService instead of direct API calls
            $cricketData = app(\App\Services\CricketDataService::class);
            $homePageData = $cricketData->getHomePageData();

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

            // Fetch featured news
            $featuredNews = $this->newsService->getNews(5, 1);

            return view('cricket.index', compact(
                'liveMatches',
                'todayMatches',
                'upcomingMatches',
                'recentCompletedMatches',
                'featuredNews'
            ));
        } catch (\Exception $e) {
            Log::error('Error in cricket index', [
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
     * Filter out cancelled matches from a list of matches
     */
    private function filterCancelledMatches($matches)
    {
        if (empty($matches)) return $matches;

        return array_filter($matches, function ($match) {
            return !isset($match['event_status']) ||
                strtolower($match['event_status']) != 'cancelled';
        });
    }

    public function filterMatches($matches, Request $request)
    {
        $filteredMatches = $matches;

        // Filter by league
        if ($request->filled('league')) {
            $filteredMatches = array_filter($filteredMatches, function ($match) use ($request) {
                return $match['league_name'] === $request->league;
            });
        }

        // Filter by team
        if ($request->filled('team')) {
            $filteredMatches = array_filter($filteredMatches, function ($match) use ($request) {
                return stripos($match['event_home_team'], $request->team) !== false ||
                    stripos($match['event_away_team'], $request->team) !== false;
            });
        }

        // Filter by match type
        if ($request->filled('match_type')) {
            $filteredMatches = array_filter($filteredMatches, function ($match) use ($request) {
                return $match['event_type'] === $request->match_type;
            });
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $filteredMatches = array_filter($filteredMatches, function ($match) use ($request) {
                return $match['event_date_start'] >= $request->date_from;
            });
        }

        if ($request->filled('date_to')) {
            $filteredMatches = array_filter($filteredMatches, function ($match) use ($request) {
                return $match['event_date_start'] <= $request->date_to;
            });
        }

        // Search query
        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $filteredMatches = array_filter($filteredMatches, function ($match) use ($search) {
                return stripos($match['event_home_team'], $search) !== false ||
                    stripos($match['event_away_team'], $search) !== false ||
                    stripos($match['league_name'], $search) !== false ||
                    stripos($match['event_stadium'], $search) !== false;
            });
        }

        return array_values($filteredMatches);
    }

    public function fixtures(Request $request)
    {
        // Debug: Log the date range being used
        $dateStart = null;
        $dateEnd = null;

        // Use the new CricketDataService instead of direct API calls
        $cricketData = app(\App\Services\CricketDataService::class);
        $upcomingMatches = $cricketData->getFixturesData($dateStart, $dateEnd);

        // Ensure we have valid data
        if (!is_array($upcomingMatches)) {
            Log::error('Fixtures: API returned invalid data type', [
                'type' => gettype($upcomingMatches),
                'value' => $upcomingMatches
            ]);
            $upcomingMatches = [];
        }

        // Debug: Log the raw response
        Log::info('Fixtures: Raw API response', [
            'total_matches' => count($upcomingMatches),
            'sample_match' => $upcomingMatches[0] ?? 'No matches found',
            'data_type' => gettype($upcomingMatches)
        ]);

        // Apply filters
        $upcomingMatches = $this->filterMatches($upcomingMatches, $request);


        // Pagination
        $perPage = 24; // Increased to 24 matches per page
        $currentPage = $request->get('page', 1);
        $totalMatches = count($upcomingMatches);
        $totalPages = ceil($totalMatches / $perPage);

        // Get paginated matches
        $paginatedMatches = array_slice($upcomingMatches, ($currentPage - 1) * $perPage, $perPage);

        // Get unique leagues, teams, and formats for filters (from all matches, not just current page)
        $leagues = array_unique(array_column($upcomingMatches, 'league_name'));
        $teams = array_unique(array_merge(
            array_column($upcomingMatches, 'event_home_team'),
            array_column($upcomingMatches, 'event_away_team')
        ));
        $formats = array_unique(array_filter(array_column($upcomingMatches, 'matchFormat')));

        return view('cricket.fixtures', compact(
            'upcomingMatches', // Send all matches for counting and filtering
            'paginatedMatches', // Send paginated matches for display
            'currentPage',
            'totalPages',
            'totalMatches',
            'leagues',
            'teams',
            'formats'
        ));
    }
    /**
     * Process today's matches to fix template placeholders in event_status_info
     */
    private function processTodayMatches($todayMatches)
    {
        if (empty($todayMatches)) return $todayMatches;

        foreach ($todayMatches as &$match) {
            if (isset($match['event_status_info']) && !empty($match['event_status_info'])) {
                $match['event_status_info'] = $this->processEventStatusInfo($match);
            }
        }

        return $todayMatches;
    }

    /**
     * Process event status info to replace template placeholders with actual values
     */
    private function processEventStatusInfo($match)
    {
        $statusInfo = $match['event_status_info'];

        // Check if it contains template placeholders
        if (
            strpos($statusInfo, '{{MATCH_START_HOURS}}') !== false ||
            strpos($statusInfo, '{{MATCH_START_MINS}}') !== false
        ) {

            // Calculate time until match starts
            $matchDateTime = null;
            if (isset($match['event_date_start']) && isset($match['event_time'])) {
                $matchDateTime = \Carbon\Carbon::parse($match['event_date_start'] . ' ' . $match['event_time']);
            } elseif (isset($match['event_date_start'])) {
                $matchDateTime = \Carbon\Carbon::parse($match['event_date_start'] . ' 00:00:00');
            }

            if ($matchDateTime && $matchDateTime->isFuture()) {
                $now = \Carbon\Carbon::now();
                $diff = $now->diff($matchDateTime);

                $hours = $diff->h + ($diff->days * 24);
                $minutes = $diff->i;

                // Replace placeholders with actual values
                $statusInfo = str_replace('{{MATCH_START_HOURS}}', $hours, $statusInfo);
                $statusInfo = str_replace('{{MATCH_START_MINS}}', $minutes, $statusInfo);

                // Add "ago" or "until" context
                if ($hours > 0) {
                    $statusInfo = "Match starts in {$hours}h {$minutes}m";
                } else {
                    $statusInfo = "Match starts in {$minutes}m";
                }
            } else {
                // If match time has passed, show a different message
                $statusInfo = "Match scheduled for today";
            }
        }

        return $statusInfo;
    }

    /**
     * Get match Details
     */
    public function matchDetail($matchId, $iid = null)
    {
        try {

            Log::info('Fetching match match', [
                'match_id' => $matchId,
            ]);

            $cricketData = app(\App\Services\CricketDataService::class);
            $detail = $cricketData->getMatchDetails($matchId, $iid);
            return response()->json([
                'success' => true,
                'data' => $detail,
                'match_id' => $matchId,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading match commentary',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
