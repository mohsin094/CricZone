<?php

namespace App\Services;

use App\Services\CricketApiService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class CricketDataService
{
    protected $cricketApi;
    protected $cacheKey = 'cricket_all_matches';
    protected $cacheDuration = 28800; // 30 seconds

    public function __construct(CricketApiService $cricketApi)
    {
        $this->cricketApi = $cricketApi;
    }

    /**
     * Get all matches with comprehensive data (cached to minimize API calls)
     */
    public function getAllMatches($dateStart = null, $dateEnd = null)
    {
        // Use cache to avoid multiple API calls
        $cacheKey = $this->cacheKey . '_' . ($dateStart ?? 'default') . '_' . ($dateEnd ?? 'default');

        return Cache::remember($cacheKey, $this->cacheDuration, function () use ($dateStart, $dateEnd) {
            try {

                // Fetch all matches from API with comprehensive data
                $matches = $this->cricketApi->getEvents($dateStart, $dateEnd);

                if (!is_array($matches)) {
                    Log::error('CricketDataService: API returned invalid data type', [
                        'type' => gettype($matches),
                        'value' => $matches
                    ]);
                    return [];
                }

                // Transform the Cricbuzz API data to frontend format
                $transformedMatches = [];
                $transformationErrors = 0;
                $transformationWarnings = 0;

                foreach ($matches as $index => $match) {
                    try {
                        $transformedMatch = $this->transformSingleMatch($match, 'api');
                        if ($transformedMatch) {
                            $transformedMatches[] = $transformedMatch;
                        } else {
                            $transformationErrors++;
                            Log::warning('CricketDataService: Failed to transform match', [
                                'index' => $index,
                                'match_structure' => array_keys($match),
                                'has_matchInfo' => isset($match['matchInfo']),
                                'match_sample' => array_slice($match, 0, 3) // Show first 3 keys for debugging
                            ]);
                        }
                    } catch (\Exception $e) {
                        $transformationErrors++;
                        Log::error('CricketDataService: Error transforming match', [
                            'index' => $index,
                            'error' => $e->getMessage(),
                            'match_structure' => array_keys($match),
                            'match_sample' => array_slice($match, 0, 3) // Show first 3 keys for debugging
                        ]);
                    }
                }

                return $transformedMatches;
            } catch (\Exception $e) {
                Log::error('CricketDataService: Error fetching matches', [
                    'error' => $e->getMessage(),
                    'date_range' => [$dateStart, $dateEnd]
                ]);
                return [];
            }
        });
    }

    /**
     * Get matches categorized by status
     */
    public function getMatchesByStatus($dateStart = null, $dateEnd = null)
    {
        $allMatches = $this->getAllMatches($dateStart, $dateEnd);

        // Sort matches by priority (international first, then by date)
        usort($allMatches, function ($a, $b) {
            // First priority: international matches
            $aPriority = $a['match_priority'] ?? 2;
            $bPriority = $b['match_priority'] ?? 2;

            if ($aPriority !== $bPriority) {
                return $aPriority - $bPriority;
            }

            // Second priority: date (earlier dates first)
            $aDate = $a['startDate'] ?? '';
            $bDate = $b['startDate'] ?? '';

            if ($aDate && $bDate) {
                return strtotime($aDate) - strtotime($bDate);
            }

            return 0;
        });

        $categorized = [
            'live' => [],
            'today' => [],
            'upcoming' => [],
            'finished' => [],
            'cancelled' => []
        ];

        foreach ($allMatches as $match) {
            // Handle both old API format and new Cricbuzz format
            $status = strtolower($match['event_status'] ?? $match['status'] ?? '');
            $state = strtolower($match['state'] ?? '');
            $eventDate = $match['startDate'] ?? $match['startDate'] ?? '';
            $isToday = $eventDate === now()->format('Y-m-d');

            // Check if this is a live match
            $isLive = $status === 'live' ||
                ($match['event_live'] ?? '0') == '1' ||
                $state === 'in progress' ||
                ($match['isLive'] ?? false) === true;

            // Check if this is a finished match
            $isFinished = $status === 'finished' ||
                $status === 'completed' ||
                $status === 'ended' ||
                $status === 'final' ||
                $state === 'complete' ||
                ($match['isCompleted'] ?? false) === true;

            // Check if this is a cancelled match
            $isCancelled = $status === 'cancelled' ||
                $status === 'abandoned' ||
                $state === 'abandon' ||
                ($match['isCancelled'] ?? false) === true;

            // Check if this is an upcoming match
            $isUpcoming = $state === 'upcoming' ||
                ($match['isUpcoming'] ?? false) === true;

            if ($isLive) {
                $categorized['live'][] = $match;
            } elseif ($isFinished) {
                $categorized['finished'][] = $match;
            } elseif ($isCancelled) {
                $categorized['cancelled'][] = $match;
            } elseif ($isToday) {
                $categorized['today'][] = $match;
            } elseif ($isUpcoming) {
                $categorized['upcoming'][] = $match;
            } else {
                // Default to upcoming if we can't determine
                $categorized['upcoming'][] = $match;
            }
        }

        return $categorized;
    }

    /**
     * Get live matches
     */
    public function getLiveMatches($dateStart = null, $dateEnd = null)
    {
        $categorized = $this->getMatchesByStatus($dateStart, $dateEnd);
        return $categorized['live'];
    }

    /**
     * Get today's matches
     */
    public function getTodayMatches($dateStart = null, $dateEnd = null)
    {
        $categorized = $this->getMatchesByStatus($dateStart, $dateEnd);
        return $categorized['today'];
    }

    /**
     * Get upcoming matches
     */
    public function getUpcomingMatches($dateStart = null, $dateEnd = null)
    {
        $categorized = $this->getMatchesByStatus($dateStart, $dateEnd);
        return $categorized['upcoming'];
    }

    /**
     * Get finished matches
     */
    public function getFinishedMatches($dateStart = null, $dateEnd = null)
    {
        $categorized = $this->getMatchesByStatus($dateStart, $dateEnd);
        return $categorized['finished'];
    }

    /**
     * Get recent completed matches (last N days)
     */
    public function getRecentCompletedMatches($days = 30)
    {
        $dateStart = now()->subDays($days)->format('Y-m-d');
        $dateEnd = now()->format('Y-m-d');

        $finishedMatches = $this->getFinishedMatches($dateStart, $dateEnd);

        // Sort by date descending (most recent first)
        usort($finishedMatches, function ($a, $b) {
            // Try multiple date fields for sorting
            $dateA = $a['endDate'] ?? $a['event_date_end'] ?? $a['event_date_stop'] ?? $a['event_date'] ?? '';
            $dateB = $b['endDate'] ?? $b['event_date_end'] ?? $b['event_date_stop'] ?? $b['event_date'] ?? '';

            if ($dateA && $dateB) {
                return strtotime($dateB) - strtotime($dateA);
            }
            return 0;
        });

        return $finishedMatches;
    }

    /**
     * Get matches for fixtures page
     */
    public function getFixturesData($dateStart = null, $dateEnd = null)
    {
        if (!$dateStart) $dateStart = now()->format('Y-m-d');
        if (!$dateEnd) $dateEnd = now()->addDays(30)->format('Y-m-d');

        $allMatches = $this->getAllMatches($dateStart, $dateEnd);

        // Filter for upcoming and today's matches
        $fixturesMatches = array_filter($allMatches, function ($match) {
            // Check multiple possible status fields
            $status = strtolower($match['event_status'] ?? $match['status'] ?? '');
            $state = strtolower($match['state'] ?? '');

            // Check multiple possible date fields
            $eventDate = $match['startDate'] ?? '';
            $isToday = false;

            if ($eventDate) {
                try {
                    if (is_numeric($eventDate)) {
                        // This is a timestamp, convert it
                        $timestamp = intval($eventDate) / 1000;
                        $eventDate = date('Y-m-d', $timestamp);
                    }
                    $isToday = $eventDate === now()->format('Y-m-d');
                } catch (\Exception $e) {
                    // If date parsing fails, check if it's today by other means
                    $isToday = false;
                }
            }

            // Include matches that are:
            // 1. Upcoming (no status or scheduled/not started)
            // 2. Today's matches
            // 3. Live matches (for fixtures page, we might want to show live matches too)
            return $status === '' ||
                $status === 'scheduled' ||
                $status === 'not started' ||
                $status === 'upcoming' ||
                $isToday ||
                strpos($status, 'live') !== false;
        });

        return array_values($fixturesMatches);
    }
    /**
     * Get matches for home page
     */
    public function getHomePageData()
    {
        $dateStart = null;
        $dateEnd = null;

        $categorized = $this->getMatchesByStatus($dateStart, $dateEnd);

        return [
            'liveMatches' => $categorized['live'],
            'todayMatches' => $categorized['today'],
            'upcomingMatches' => array_slice($categorized['upcoming'], 0, 15), // Limit to 15
            'recentCompletedMatches' => array_slice($categorized['finished'], 0, 15) // Limit to 15
        ];
    }

    /**
     * Transform a single Cricbuzz match to frontend format
     */
    private function transformSingleMatch($match, $matchType)
    {
        if (!isset($match['matchInfo'])) {
            Log::warning('CricketDataService: Match missing matchInfo', [
                'match_keys' => array_keys($match),
                'match_type' => $matchType
            ]);
            return null;
        }

        $matchInfo = $match['matchInfo'];
        $matchScore = $match['matchScore'] ?? [];

        // Extract team information from the real API structure
        $homeTeam = $matchInfo['team1']['teamName'] ?? '';
        $awayTeam = $matchInfo['team2']['teamName'] ?? '';
        $homeTeamShort = $matchInfo['team1']['teamSName'] ?? '';
        $awayTeamShort = $matchInfo['team2']['teamSName'] ?? '';

        // Extract scores from matchScore structure with safe field access
        $homeScore = '';
        $awayScore = '';

        // Extract scores from matchScore structure with safe field access
        $homeScore = $this->extractTeamScore($matchScore['team1Score'] ?? []);
        $awayScore = $this->extractTeamScore($matchScore['team2Score'] ?? []);

        // Determine match status from the real API
        $state = $matchInfo['state'] ?? '';
        $status = $matchInfo['status'] ?? '';
        $stateTitle = $matchInfo['stateTitle'] ?? '';

        // Map Cricbuzz states to our internal status
        $isLive = in_array($state, ['In Progress', 'Live']);
        $isCompleted = in_array($state, ['Complete', 'Finished']);
        $isUpcoming = in_array($state, ['Upcoming', 'Not Started']);
        $isCancelled = in_array($state, ['Abandon', 'Cancelled', 'No Result']);

        // Create unique event key
        $eventKey = $matchInfo['matchId'] ?? uniqid();

        // Convert timestamp to date format with proper timezone handling
        $startDate = '';
        $startDateTime = '';
        $startTime = '';
        $startDateHuman = '';

        if (isset($matchInfo['startDate'])) {
            $timestamp = intval($matchInfo['startDate']) / 1000;
            $startDate = date('Y-m-d', $timestamp);
            $startDateTime = date('Y-m-d H:i:s', $timestamp);
            $startTime = date('H:i', $timestamp);
            $startDateHuman = date('M d, Y', $timestamp);
        }

        $endDate = '';
        $endDateTime = '';
        $endDateHuman = '';
        if (isset($matchInfo['endDate'])) {
            $timestamp = intval($matchInfo['endDate']) / 1000;
            $endDate = date('Y-m-d', $timestamp);
            $endDateTime = date('Y-m-d H:i:s', $timestamp);
            $endDateHuman = date('M d, Y', $timestamp);
        }

        // Extract venue information
        $venue = '';
        $venueInfo = $matchInfo['venueInfo'] ?? [];
        if (!empty($venueInfo)) {
            $venue = ($venueInfo['ground'] ?? '') . ', ' . ($venueInfo['city'] ?? '');
            if (isset($venueInfo['country'])) {
                $venue .= ', ' . $venueInfo['country'];
            }
        }

        // Determine if this is an international match
        $isInternational = $this->isInternationalMatch($matchInfo['seriesName'] ?? '', $homeTeam, $awayTeam);

        // Determine match priority (international matches first)
        $matchPriority = $isInternational ? 1 : 2;

        // Transform to frontend format
        return [
            // Old API fields (for frontend compatibility)
            'event_key' => $eventKey,
            'event_home_team' => $homeTeam,
            'event_away_team' => $awayTeam,
            'event_home_final_result' => $homeScore,
            'event_away_final_result' => $awayScore,
            'event_type' => $matchType,
            'event_status' => $state,
            'event_status_info' => $stateTitle ?: $status,
            'event_state_title' => $stateTitle,
            'event_date_start' => $startDate,
            'event_date_end' => $endDate,
            'event_time' => $startTime,
            'event_datetime' => $startDateTime,
            'event_live' => $isLive ? '1' : '0',
            'event_live_status' => $isLive ? '1' : '0',
            'league_name' => $matchInfo['seriesName'] ?? '',
            'venue' => $venue,
            'event_stadium' => $venueInfo['ground'] ?? '',
            'event_venue' => $venue,
            'event_city' => $venueInfo['city'] ?? '',
            'event_country' => $venueInfo['country'] ?? '',
            'event_ground' => $venueInfo['ground'] ?? '',
            'event_toss' => '', // Toss information not available in current API structure
            'outcome' => $status,
            'is_international' => $isInternational,
            'match_priority' => $matchPriority,

            // New Cricbuzz fields (for future use)
            'matchId' => $matchInfo['matchId'] ?? '',
            'seriesId' => $matchInfo['seriesId'] ?? '',
            'seriesName' => $matchInfo['seriesName'] ?? '',
            'matchDesc' => $matchInfo['matchDesc'] ?? '',
            'matchFormat' => $matchInfo['matchFormat'] ?? '',
            'matchFormatDisplay' => $this->formatMatchType($matchInfo['matchFormat'] ?? ''),
            'startDate' => $startDate,
            'endDate' => $endDate,
            'startDateHuman' => $startDateHuman,
            'endDateHuman' => $endDateHuman,
            'startDateTime' => $startDateTime,
            'startTime' => $startTime,
            'state' => $state,
            'status' => $status,
            'stateTitle' => $stateTitle,
            'isLive' => $isLive,
            'isCompleted' => $isCompleted,
            'isUpcoming' => $isUpcoming,
            'isCancelled' => $isCancelled,
            'matchStarted' => $isLive,
            'matchEnded' => $isCompleted,
            'dateTimeGMT' => $startDateTime,
            'date' => $startDate,

            // Team info array (for new frontend features)
            'teamInfo' => [
                [
                    'name' => $homeTeam,
                    'shortName' => $homeTeamShort,
                    'id' => $matchInfo['team1']['teamId'] ?? 1
                ],
                [
                    'name' => $awayTeam,
                    'shortName' => $awayTeamShort,
                    'id' => $matchInfo['team2']['teamId'] ?? 2
                ]
            ],

            // Match score array (for new frontend features)
            'matchScore' => $matchScore,

            // Status categorization
            'isLive' => $isLive,
            'isCompleted' => $isCompleted,
            'isUpcoming' => $isUpcoming,
            'isCancelled' => $isCancelled,

            // Comments placeholder (for live updates)
            'comments' => [
                'Live' => []
            ]
        ];
    }

    /**
     * Determine if a match is international
     */
    private function isInternationalMatch($seriesName, $homeTeam, $awayTeam)
    {
        // Check series name for international indicators
        $internationalKeywords = [
            'tour',
            'series',
            'cup',
            'championship',
            'world cup',
            't20 world cup',
            'odi world cup',
            'test championship',
            'ashes',
            'border gavaskar',
            'pakistan tour',
            'india tour',
            'england tour',
            'australia tour',
            'south africa tour',
            'west indies tour',
            'new zealand tour',
            'sri lanka tour',
            'bangladesh tour',
            'afghanistan tour'
        ];

        $seriesNameLower = strtolower($seriesName);
        foreach ($internationalKeywords as $keyword) {
            if (strpos($seriesNameLower, $keyword) !== false) {
                return true;
            }
        }

        // Check team names for country indicators
        $countryTeams = [
            'india',
            'pakistan',
            'australia',
            'england',
            'south africa',
            'west indies',
            'new zealand',
            'sri lanka',
            'bangladesh',
            'afghanistan',
            'ireland',
            'zimbabwe',
            'netherlands',
            'scotland',
            'namibia',
            'oman',
            'uae',
            'hong kong',
            'singapore'
        ];

        $homeTeamLower = strtolower($homeTeam);
        $awayTeamLower = strtolower($awayTeam);

        foreach ($countryTeams as $country) {
            if (strpos($homeTeamLower, $country) !== false || strpos($awayTeamLower, $country) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Format match type for display
     */
    private function formatMatchType($matchFormat)
    {
        if (empty($matchFormat)) return '';

        $format = strtolower($matchFormat);

        switch ($format) {
            case 't20':
            case 't20i':
                return 'T20';
            case 'odi':
            case 'one day':
                return 'ODI';
            case 'test':
                return 'Test';
            case 't10':
                return 'T10';
            case 'hundred':
                return 'The Hundred';
            default:
                return ucfirst($format);
        }
    }

    /**
     * Extract team score from matchScore structure
     */
    private function extractTeamScore($teamScore)
    {
        if (empty($teamScore)) {
            return '';
        }

        // Try different innings structures
        $innings = null;

        // Check for inngs1 (first innings)
        if (isset($teamScore['inngs1'])) {
            $innings = $teamScore['inngs1'];
        }
        // Check for inngs2 (second innings)
        elseif (isset($teamScore['inngs2'])) {
            $innings = $teamScore['inngs2'];
        }
        // Check for any innings
        elseif (isset($teamScore['innings'])) {
            $innings = $teamScore['innings'];
        }
        // Check if teamScore is directly the innings data
        elseif (isset($teamScore['runs']) || isset($teamScore['wickets'])) {
            $innings = $teamScore;
        }

        if (!$innings) {
            return '';
        }

        // Extract score components safely
        $runs = $innings['runs'] ?? 0;
        $wickets = $innings['wickets'] ?? 0;
        $overs = $innings['overs'] ?? 0;

        // Only show score if there are runs or wickets
        if ($runs > 0 || $wickets > 0) {
            $score = $runs . '/' . $wickets;
            if ($overs > 0) {
                // Convert decimal overs to proper overs format
                $formattedOvers = $this->formatOvers($overs);
                if ($formattedOvers) {
                    $score .= ' (' . $formattedOvers . ' ov)';
                }
            }
            return $score;
        }

        return '';
    }

    /**
     * Convert decimal overs to proper overs format (19.6 = 20 overs, 1.6 = 2 overs)
     */
    private function formatOvers($overs)
    {
        if (!$overs || $overs === '0.0') return '';

        $decimalOvers = floatval($overs);
        $fullOvers = floor($decimalOvers);
        $balls = ($decimalOvers - $fullOvers) * 10;

        // If we have 6 balls, that's a complete over
        if ($balls >= 6) {
            $fullOvers += 1;
            $balls = 0;
        }

        // Return the total overs (19.6 becomes 20, 1.6 becomes 2)
        return $fullOvers;
    }


    /**
     * Get match commentary
     * return match details including commentary, squads
     */
    public function getMatchDetails($matchId, $iid)
    {
        try {
            // Fetch commentary from API
            $commentary = $this->cricketApi->getMatchCommentary($matchId, $iid);
            $scoreCard = $this->cricketApi->getMatchScorecard($matchId);
            $squads = $this->cricketApi->getMatchSquads($matchId);

            if (!is_array($commentary)) {
                Log::error('CricketDataService: Invalid commentary data');
                return [];
            }

            return [
                'commentary' => $commentary,
                'scoreCard' => $scoreCard,
                'squads' => $squads,
            ];
        } catch (\Exception $e) {
            Log::error('CricketDataService: Error fetching match commentary', [
                'match_id' => $matchId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
}
