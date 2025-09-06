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
    protected $useMock = false;

    public function __construct(CricketApiService $cricketApi)
    {
        $this->cricketApi = $cricketApi;
    }

    /**
     * Get all matches with comprehensive data (cached to minimize API calls)
     */
    public function getAllMatches($dateStart = null, $dateEnd = null)
    {
        // If mock data is enabled, return transformed mock data
        if ($this->useMock) {
            $mockData = $this->getMockData('all');
            $allMatches = [];
            
            // Transform and combine all mock data
            if (!empty($mockData['live'])) {
                $allMatches = array_merge($allMatches, $this->transformCricbuzzData($mockData['live']));
            }
            if (!empty($mockData['upcoming'])) {
                $allMatches = array_merge($allMatches, $this->transformCricbuzzData($mockData['upcoming']));
            }
            if (!empty($mockData['recent'])) {
                $allMatches = array_merge($allMatches, $this->transformCricbuzzData($mockData['recent']));
            }
            
            Log::info('CricketDataService: Using mock data', [
                'total_matches' => count($allMatches),
                'live_count' => count($this->transformCricbuzzData($mockData['live'] ?? [])),
                'upcoming_count' => count($this->transformCricbuzzData($mockData['upcoming'] ?? [])),
                'recent_count' => count($this->transformCricbuzzData($mockData['recent'] ?? []))
            ]);
            
            return $allMatches;
        }

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

                Log::info('CricketDataService: Fetched and transformed matches from API', [
                    'total_matches' => count($transformedMatches),
                    'transformation_errors' => $transformationErrors,
                    'date_range' => [$dateStart, $dateEnd]
                ]);

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
        usort($allMatches, function($a, $b) {
            // First priority: international matches
            $aPriority = $a['match_priority'] ?? 2;
            $bPriority = $b['match_priority'] ?? 2;
            
            if ($aPriority !== $bPriority) {
                return $aPriority - $bPriority;
            }
            
            // Second priority: date (earlier dates first)
            $aDate = $a['event_date_start'] ?? '';
            $bDate = $b['event_date_start'] ?? '';
            
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
            $eventDate = $match['event_date_start'] ?? $match['startDate'] ?? '';
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
        usort($finishedMatches, function($a, $b) {
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
        $fixturesMatches = array_filter($allMatches, function($match) {
            $status = strtolower($match['event_status'] ?? '');
            $eventDate = $match['event_date_start'] ?? '';
            $isToday = $eventDate === now()->format('Y-m-d');
            
            return $status === '' || 
                   $status === 'scheduled' || 
                   $status === 'not started' || 
                   $isToday;
        });
        
        return array_values($fixturesMatches);
    }

    /**
     * Get matches for results page
     */
    public function getResultsData($days = 30)
    {
        $matches = $this->getRecentCompletedMatches($days);
        
        // Enhance each match with better date formatting
        foreach ($matches as &$match) {
            $match = $this->enhanceMatchDates($match);
        }
        
        return $matches;
    }
    
    /**
     * Enhance match with better date formatting
     */
    private function enhanceMatchDates($match)
    {
        // Add human-readable dates if not already present
        if (!isset($match['startDateHuman']) && isset($match['startDate'])) {
            try {
                if (is_numeric($match['startDate'])) {
                    $timestamp = intval($match['startDate']) / 1000;
                    $match['startDateHuman'] = date('M d, Y H:i', $timestamp);
                } else {
                    $carbon = \Carbon\Carbon::parse($match['startDate']);
                    $match['startDateHuman'] = $carbon->format('M d, Y H:i');
                }
            } catch (\Exception $e) {
                $match['startDateHuman'] = $match['startDate'];
            }
        }
        
        if (!isset($match['endDateHuman']) && isset($match['endDate'])) {
            try {
                if (is_numeric($match['endDate'])) {
                    $timestamp = intval($match['endDate']) / 1000;
                    $match['endDateHuman'] = date('M d, Y H:i', $timestamp);
                } else {
                    $carbon = \Carbon\Carbon::parse($match['endDate']);
                    $match['endDateHuman'] = $carbon->format('M d, Y H:i');
                }
            } catch (\Exception $e) {
                $match['endDateHuman'] = $match['endDate'];
            }
        }
        
        return $match;
    }

    /**
     * Get matches for home page
     */
    public function getHomePageData()
    {
        $dateStart = now()->subDays(7)->format('Y-m-d');
        $dateEnd = now()->addDays(7)->format('Y-m-d');
        
        $categorized = $this->getMatchesByStatus($dateStart, $dateEnd);
        
        return [
            'liveMatches' => $categorized['live'],
            'todayMatches' => $categorized['today'],
            'upcomingMatches' => array_slice($categorized['upcoming'], 0, 12), // Limit to 12
            'recentCompletedMatches' => array_slice($categorized['finished'], 0, 12) // Limit to 12
        ];
    }

    /**
     * Get mock data for testing and development
     */
    public function getMockData($type = 'all')
    {
        $mockDataPath = base_path('mock_data');
        
        try {
            $liveMatches = [];
            $upcomingMatches = [];
            $recentMatches = [];
            
            // Read live matches from file
            if (file_exists($mockDataPath . '/matchesv1live.json')) {
                $liveMatches = json_decode(file_get_contents($mockDataPath . '/matchesv1live.json'), true);
            }
            
            // Read upcoming matches from file
            if (file_exists($mockDataPath . '/matchesv1upcoming.json')) {
                $upcomingMatches = json_decode(file_get_contents($mockDataPath . '/matchesv1upcoming.json'), true);
            }
            
            // Read recent matches from file
            if (file_exists($mockDataPath . '/matchesv1recent.json')) {
                $recentMatches = json_decode(file_get_contents($mockDataPath . '/matchesv1recent.json'), true);
            }
            
            switch ($type) {
                case 'live':
                    return $liveMatches;
                case 'upcoming':
                    return $upcomingMatches;
                case 'recent':
                    return $recentMatches;
                case 'all':
                default:
                    return [
                        'live' => $liveMatches,
                        'upcoming' => $upcomingMatches,
                        'recent' => $recentMatches
                    ];
            }
        } catch (\Exception $e) {
            Log::error('CricketDataService: Error reading mock data files', [
                'error' => $e->getMessage()
            ]);
            
            // Fallback to empty data
            return [
                'live' => [],
                'upcoming' => [],
                'recent' => []
            ];
        }
    }

    /**
     * Get team matches
     */
    public function getTeamMatches($teamName, $dateStart = null, $dateEnd = null)
    {
        if (!$dateStart) $dateStart = now()->format('Y-m-d');
        if (!$dateEnd) $dateEnd = now()->addDays(30)->format('Y-m-d');
        
        $allMatches = $this->getAllMatches($dateStart, $dateEnd);
        
        // Filter matches for this team
        $teamMatches = array_filter($allMatches, function($match) use ($teamName) {
            $homeTeamName = $match['event_home_team'] ?? '';
            $awayTeamName = $match['event_away_team'] ?? '';
            
            return stripos($homeTeamName, $teamName) !== false || 
                   stripos($awayTeamName, $teamName) !== false;
        });
        
        return array_values($teamMatches);
    }

    /**
     * Clear cache (useful for testing or when data needs refresh)
     */
    public function clearCache()
    {
        Cache::forget($this->cacheKey);
        Log::info('CricketDataService: Cache cleared');
    }

    /**
     * Get match by key with full details
     */
    public function getMatchByKey($matchKey, $dateStart = null, $dateEnd = null)
    {
        $allMatches = $this->getAllMatches($dateStart, $dateEnd);
        
        foreach ($allMatches as $match) {
            if (($match['event_key'] ?? '') === $matchKey) {
                return $match;
            }
        }
        
        return null;
    }

    /**
     * Get comprehensive match details including live data
     */
    public function getComprehensiveMatchDetails($eventKey)
    {
        $match = $this->getMatchByKey($eventKey);
        
        if (!$match) {
            return null;
        }
        
        // Get additional live data if match is live
        if (($match['isLive'] ?? false) || ($match['event_live'] ?? '0') == '1') {
            try {
                $liveData = app(\App\Services\CricketApiService::class)->getLiveScores(null, $eventKey);
                if (!empty($liveData)) {
                    $match['live_data'] = $liveData;
                }
            } catch (\Exception $e) {
                Log::warning('Could not fetch live data for match', [
                    'event_key' => $eventKey,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // Enhance match data with additional information
        $match['comprehensive_info'] = [
            'match_format_display' => $this->formatMatchType($match['matchFormat'] ?? ''),
            'is_international' => $match['is_international'] ?? false,
            'venue_full' => $match['venue'] ?? '',
            'start_time_formatted' => $match['startTime'] ?? '',
            'match_duration' => $this->calculateMatchDuration($match),
            'series_info' => [
                'name' => $match['seriesName'] ?? '',
                'description' => $match['matchDesc'] ?? '',
                'start_date' => $match['seriesStartDt'] ?? '',
                'end_date' => $match['seriesEndDt'] ?? ''
            ]
        ];
        
        // Add raw API data for debugging and comprehensive display
        $match['raw_api_data'] = [
            'matchInfo' => $match['matchInfo'] ?? [],
            'matchScore' => $match['matchScore'] ?? [],
            'original_state' => $match['state'] ?? '',
            'original_status' => $match['status'] ?? '',
            'original_startDate' => $match['startDate'] ?? '',
            'original_endDate' => $match['endDate'] ?? ''
        ];
        
        return $match;
    }

    /**
     * Calculate match duration
     */
    private function calculateMatchDuration($match)
    {
        if (empty($match['startDate']) || empty($match['endDate'])) {
            return '';
        }
        
        try {
            $start = \Carbon\Carbon::createFromTimestamp(intval($match['startDate']) / 1000);
            $end = \Carbon\Carbon::createFromTimestamp(intval($match['endDate']) / 1000);
            
            $duration = $start->diffForHumans($end, true);
            return $duration;
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Transform Cricbuzz API data to frontend-expected format
     */
    private function transformCricbuzzData($cricbuzzData, $type = 'matches')
    {
        if (empty($cricbuzzData) || !isset($cricbuzzData['typeMatches'])) {
            return [];
        }

        $transformedMatches = [];
        
        foreach ($cricbuzzData['typeMatches'] as $typeMatch) {
            $matchType = $typeMatch['matchType'] ?? '';
            
            if (isset($typeMatch['seriesMatches'])) {
                foreach ($typeMatch['seriesMatches'] as $seriesMatch) {
                    if (isset($seriesMatch['seriesAdWrapper']['matches'])) {
                        foreach ($seriesMatch['seriesAdWrapper']['matches'] as $match) {
                            $transformedMatch = $this->transformSingleMatch($match, $matchType);
                            if ($transformedMatch) {
                                $transformedMatches[] = $transformedMatch;
                            }
                        }
                    }
                }
            }
        }
        
        return $transformedMatches;
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
        
        // Map Cricbuzz states to our internal status
        $isLive = in_array($state, ['In Progress', 'Live']);
        $isCompleted = in_array($state, ['Complete', 'Finished']);
        $isUpcoming = in_array($state, ['Upcoming', 'Not Started']);
        $isCancelled = in_array($state, ['Abandon', 'Cancelled', 'No Result']);
        
        // Create unique event key
        $eventKey = 'match_' . ($matchInfo['matchId'] ?? uniqid());
        
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
            'event_status_info' => $status,
            'event_date_start' => $startDate,
            'event_date_end' => $endDate,
            'event_time' => $startTime,
            'event_datetime' => $startDateTime,
            'event_live' => $isLive ? '1' : '0',
            'event_live_status' => $isLive ? '1' : '0',
            'league_name' => $matchInfo['seriesName'] ?? '',
            'venue' => $venue,
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
     * Enable or disable mock data
     */
    public function useMockData($enabled = true)
    {
        $this->useMock = $enabled;
        Log::info('CricketDataService: Mock data ' . ($enabled ? 'enabled' : 'disabled'));
    }

    /**
     * Check if mock data is enabled
     */
    public function isMockEnabled()
    {
        return $this->useMock;
    }

    /**
     * Determine if a match is international
     */
    private function isInternationalMatch($seriesName, $homeTeam, $awayTeam)
    {
        // Check series name for international indicators
        $internationalKeywords = [
            'tour', 'series', 'cup', 'championship', 'world cup', 't20 world cup', 'odi world cup',
            'test championship', 'ashes', 'border gavaskar', 'pakistan tour', 'india tour',
            'england tour', 'australia tour', 'south africa tour', 'west indies tour',
            'new zealand tour', 'sri lanka tour', 'bangladesh tour', 'afghanistan tour'
        ];
        
        $seriesNameLower = strtolower($seriesName);
        foreach ($internationalKeywords as $keyword) {
            if (strpos($seriesNameLower, $keyword) !== false) {
                return true;
            }
        }
        
        // Check team names for country indicators
        $countryTeams = [
            'india', 'pakistan', 'australia', 'england', 'south africa', 'west indies',
            'new zealand', 'sri lanka', 'bangladesh', 'afghanistan', 'ireland', 'zimbabwe',
            'netherlands', 'scotland', 'namibia', 'oman', 'uae', 'hong kong', 'singapore'
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
                $score .= ' (' . $overs . ' ov)';
            }
            return $score;
        }
        
        return '';
    }
}
