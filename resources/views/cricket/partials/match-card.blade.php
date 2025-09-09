@php
$homeScore = $match['event_home_final_result'] ?? null;
$awayScore = $match['event_away_final_result'] ?? null;

// Extract scores from new API structure if available
if (isset($match['matchScore']['team1Score']['inngs1']['runs'])) {
    $homeRuns = $match['matchScore']['team1Score']['inngs1']['runs'];
    $homeWickets = $match['matchScore']['team1Score']['inngs1']['wickets'] ?? 0;
    $homeScore = $homeRuns . '/' . $homeWickets;
}
if (isset($match['matchScore']['team2Score']['inngs1']['runs'])) {
    $awayRuns = $match['matchScore']['team2Score']['inngs1']['runs'];
    $awayWickets = $match['matchScore']['team2Score']['inngs1']['wickets'] ?? 0;
    $awayScore = $awayRuns . '/' . $awayWickets;
}

// Fallback to old structure
if (!$homeScore) {
    $homeScore = $match['event_home_final_result'] ?? null;
}
if (!$awayScore) {
    $awayScore = $match['event_away_final_result'] ?? null;
}

$homeRuns = $homeScore ? (int) explode('/', $homeScore)[0] : 0;
$awayRuns = $awayScore ? (int) explode('/', $awayScore)[0] : 0;

// Convert decimal overs to proper overs format (19.6 = 20 overs, 1.6 = 2 overs)
if (!function_exists('formatOvers')) {
    function formatOvers($overs) {
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
}

// Extract overs from new API structure
$homeOvers = '';
$awayOvers = '';

// Check for new API structure first
if (isset($match['matchScore']['team1Score']['inngs1']['overs'])) {
    $homeOvers = formatOvers($match['matchScore']['team1Score']['inngs1']['overs']);
}
if (isset($match['matchScore']['team2Score']['inngs1']['overs'])) {
    $awayOvers = formatOvers($match['matchScore']['team2Score']['inngs1']['overs']);
}

// Fallback to old structure
if (!$homeOvers) {
    $homeOvers = formatOvers($match['event_home_overs'] ?? '');
}
if (!$awayOvers) {
    $awayOvers = formatOvers($match['event_away_overs'] ?? '');
}

$matchType = $match['event_type'] ?? '';
$matchFormat = $match['matchFormatDisplay'] ?? $match['matchFormat'] ?? $matchType;
$matchDesc = $match['matchDesc'] ?? '';

$isHundredBall = strpos(strtolower($matchType), 'hundred') !== false;
$isOversBased = strpos(strtolower($matchType), 'odi') !== false || strpos(strtolower($matchType), 't20') !== false || strpos(strtolower($matchType), 'test') !== false;
$isTest = strtolower($matchType) === 'test' || strtolower($matchFormat) === 'test';

// Extract test match specific information
$testDay = '';
$testState = '';
$testStatus = '';

if ($isTest) {
    // Extract test match information from multiple possible sources
    $testDay = $match['matchInfo']['status'] ?? $match['status'] ?? '';
    $testState = $match['matchInfo']['state'] ?? $match['state'] ?? '';
    $testStatus = $match['matchInfo']['stateTitle'] ?? $match['stateTitle'] ?? '';
    
    // For test matches, prioritize showing all available information
    // If we have a status but no day, use status as day
    if (!$testDay && $match['status']) {
        $testDay = $match['status'];
    }
    
    // If we have a state but no status, use state as status
    if (!$testStatus && $match['state']) {
        $testStatus = $match['state'];
    }
    
    // Special handling for rain-affected matches
    if ($match['status'] && strpos(strtolower($match['status']), 'rain') !== false) {
        $testStatus = $match['status'];
    }
    
    // If we have both state and status that are the same, still show them
    if ($testState && $testStatus && $testState === $testStatus) {
        // Keep both values as they might be different in context
    }
}

// Get team images/logos
$homeTeamLogo = $match['event_home_team_logo'] ?? '';
$awayTeamLogo = $match['event_away_team_logo'] ?? '';

// Get start and end dates for completed matches
$startDate = null;
$endDate = null;

// Try to get dates from multiple possible sources
$startDateStr = $match['startDate'] ?? $match['event_date_start'] ?? '';
$endDateStr = $match['endDate'] ?? $match['event_date_end'] ?? '';

// Use human-readable dates if available
$startDate = $match['startDateHuman'] ?? '';
$endDate = $match['endDateHuman'] ?? '';

// If human-readable dates are not available, try to format them
if (!$startDate && $startDateStr) {
try {
if (is_numeric($startDateStr)) {
// This is a timestamp, convert it
$startTimestamp = intval($startDateStr) / 1000;
$startDate = date('M d, Y H:i', $startTimestamp);
} else {
// This is already a formatted date, format it nicely
$startCarbon = \Carbon\Carbon::parse($startDateStr);
$startDate = $startCarbon->format('M d, Y H:i');
}
} catch (\Exception $e) {
$startDate = $startDateStr;
}
}

if (!$endDate && $endDateStr) {
try {
if (is_numeric($endDateStr)) {
// This is a timestamp, convert it
$endTimestamp = intval($endDateStr) / 1000;
$endDate = date('M d, Y H:i', $endTimestamp);
} else {
// This is already a formatted date, format it nicely
$endCarbon = \Carbon\Carbon::parse($endDateStr);
$endDate = $endCarbon->format('M d, Y H:i');
}
} catch (\Exception $e) {
$endDate = $endDateStr;
}
}

$isFirstInningsComplete = false;
$isSecondInningsStarted = false;

$remainingBalls = null;
$remainingOvers = null;
$totalOvers = null;

// Get latest overs from comments if available
$latestOvers = null;
if (!empty($match['comments']['Live'])) {
$lastComment = end($match['comments']['Live']);
if (!empty($lastComment['overs'])) {
$latestOvers = (float) $lastComment['overs'];
}
}

if ($isHundredBall) {
$totalBalls = 100;
$statusInfo = $match['event_status_info'] ?? '';
if (preg_match('/(\d+)\s*(?:balls?|deliveries?)\s*(?:remaining|left|to go)/i', $statusInfo, $matches)) {
$remainingBalls = (int) $matches[1];
}
} elseif ($isOversBased) {
if (strpos(strtolower($matchType), 't20') !== false) {
$totalOvers = 20;
} elseif (strpos(strtolower($matchType), 'odi') !== false) {
$totalOvers = 50;
} elseif (strpos(strtolower($matchType), 'test') !== false) {
$totalOvers = null;
}

if ($latestOvers !== null && $totalOvers !== null) {
$remainingOvers = $totalOvers - $latestOvers;
}
}

// Innings complete check
if ($homeScore && strpos($homeScore, '/') !== false) {
$homeParts = explode('/', $homeScore);
$homeWickets = (int) ($homeParts[1] ?? 0);

if ($isHundredBall) {
$isFirstInningsComplete = $homeWickets >= 10 ||
($remainingBalls !== null && $remainingBalls <= 0) ||
    (isset($match['event_status']) && str_contains(strtolower($match['event_status']), 'innings' )) ||
    (isset($match['event_status']) && str_contains(strtolower($match['event_status']), 'break' ));
    } elseif ($isOversBased) {
    $isFirstInningsComplete=$homeWickets>= 10 ||
    ($totalOvers !== null && $remainingOvers !== null && $remainingOvers <= 0) ||
        (isset($match['event_status']) && str_contains(strtolower($match['event_status']), 'innings' )) ||
        (isset($match['event_status']) && str_contains(strtolower($match['event_status']), 'break' ));
        } else {
        $isFirstInningsComplete=$homeWickets>= 10;
        }
        }

        $isSecondInningsStarted = $awayScore && $awayRuns > 0;
        if ($isSecondInningsStarted) {
        $isFirstInningsComplete = true;
        }

        $target = null;
        if ($isFirstInningsComplete) {
        $target = $homeRuns + 1;
        }

        // Check if match is completed (has final scores for both teams)
        $isCompleted = $homeScore && $awayScore &&
        strpos($homeScore, '/') !== false &&
        strpos($awayScore, '/') !== false;

        // Determine winner for completed matches only
        $winner = null;
        $result = '';
        
        // Only determine winner if match is completed (not live)
        if ($isCompleted && $homeScore && $awayScore) {
        if ($homeRuns > $awayRuns) {
        $winner = 'home';
        $margin = $homeRuns - $awayRuns;

        // Home team won - they were the first team (batting first, setting target)
        // Second team (away) was chasing and got bowled out
        $result = "won by $margin runs";
        } elseif ($awayRuns > $homeRuns) {
        $winner = 'away';
        $margin = $awayRuns - $homeRuns;

        // Away team won - they were the second team (chasing, batting second)
        // Check wickets remaining when they won
        if (strpos($awayScore, '/') !== false) {
        $awayParts = explode('/', $awayScore);
        $awayWickets = (int) ($awayParts[1] ?? 0);
        $wicketsRemaining = 10 - $awayWickets;
        $result = "won by $wicketsRemaining wickets";
        } else {
        $result = "won by $margin runs";
        }
        } else {
        $result = "Match tied";
        }
        }


        // Check if match is upcoming (no scores yet)
        $isUpcoming = !$homeScore && !$awayScore;

        // Format date and time based on match type
        $dateTimeDisplay = '';
        if ($type === 'today') {
        // Today matches: show only time
        $timeStr = $match['event_time'] ?? 'Time TBD';
        if ($timeStr !== 'Time TBD') {
            try {
                $timeCarbon = \Carbon\Carbon::createFromFormat('H:i', $timeStr);
                $dateTimeDisplay = $timeCarbon->format('g:i A');
            } catch (\Exception $e) {
                $dateTimeDisplay = $timeStr;
            }
        } else {
            $dateTimeDisplay = $timeStr;
        }
        } elseif ($type === 'upcoming') {
        // Upcoming matches: show date + time
        $dateStr = $match['event_date_start'] ?? '';
        $timeStr = $match['event_time'] ?? '';
        if ($dateStr && $timeStr) {
        try {
        $date = \Carbon\Carbon::parse($dateStr);
        // Convert time to 12-hour format
        $formattedTime = '';
        try {
            $timeCarbon = \Carbon\Carbon::createFromFormat('H:i', $timeStr);
            $formattedTime = $timeCarbon->format('g:i A');
        } catch (\Exception $e) {
            $formattedTime = $timeStr;
        }
        $dateTimeDisplay = $date->format('M d') . ', ' . $formattedTime;
        } catch (\Exception $e) {
        $dateTimeDisplay = $dateStr . ', ' . $timeStr;
        }
        } else {
        $dateTimeDisplay = $dateStr ?: 'Date TBD';
        }
        } elseif ($type === 'finished') {
        // Completed matches: show only date
        $dateStr = $match['endDate'] ?? $match['event_date_end'] ?? $match['event_date_stop'] ?? $match['event_date'] ?? '';
        if ($dateStr) {
        try {
        $date = \Carbon\Carbon::parse($dateStr);
        $dateTimeDisplay = $date->format('M d, Y');
        } catch (\Exception $e) {
        $dateTimeDisplay = $dateStr;
        }
        } else {
        $dateTimeDisplay = 'Date Unknown';
        }
        }


        // Check if match is upcoming (no scores yet)
        $isUpcoming = !$homeScore && !$awayScore;
        @endphp

        <div class="match-card {{ $type === 'upcoming' ? '' : 'cursor-pointer transform transition-all duration-300 hover:scale-[1.02] hover:shadow-lg' }}"
            data-league="{{ $match['league_name'] ?? '' }}"
            data-match-type="{{ $match['event_type'] ?? '' }}"
            data-home-team="{{ $match['event_home_team'] ?? '' }}"
            data-away-team="{{ $match['event_away_team'] ?? '' }}"
            data-date="{{ $match['event_date_start'] ?? '' }}"
            data-utc-time="{{ $utcTime ?? '' }}"
            @if($type !=='upcoming' ) data-match-url="/cricket/match/{{ $match['event_key'] ?? '' }}" @endif>

            <div class="bg-white rounded-lg shadow-md border border-gray-200 {{ $type === 'upcoming' ? '' : 'hover:shadow-lg' }} transition-all duration-300 relative group">
                <!-- Click indicator - Only show for non-upcoming matches -->
                @if($type !== 'upcoming')
                <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    <div class="bg-green-600 text-white text-xs px-2 py-1 rounded-full flex items-center space-x-1">
                        <span>View</span>
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </div>
                @endif

                <div class="p-2 sm:p-3">
                    <!-- Match Header - Enhanced -->
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex flex-col space-y-1">
                            <span class="text-xs font-semibold px-1 sm:px-2 py-1 rounded-full
                        @if($type === 'live') text-red-700 bg-red-100
                        @elseif($type === 'today') text-blue-700 bg-blue-100
                        @else text-purple-700 bg-purple-100 @endif">
                                @if($type === 'live') <span class="blink-dot"></span> @elseif($type === 'today') 📅 @else ⏰ @endif
                                <span>{{ $matchFormat ?? 'Match' }}</span>
                            </span>
                            @if($matchDesc)
                            <span class="text-xs text-gray-600">{{ $matchDesc }}</span>
                            @endif
                        </div>
                        <div class="text-right text-xs">
                            <div class="text-gray-800 hidden sm:block">{{ $match['venue'] ?? 'Venue TBD' }}</div>
                            <div class="text-gray-500">{{ $match['league_name'] ?? '' }}</div>
                        </div>
                    </div>

                    <!-- Teams - Compact but Detailed -->
                    <div class="space-y-1 sm:space-y-2 mb-2">
                        <!-- Home Team -->
                        <div class="flex items-center justify-between bg-gray-50 rounded p-1 sm:p-2 {{ $winner === 'home' ? 'bg-green-100 border border-green-200' : '' }}">
                            <div class="flex items-center space-x-1 sm:space-x-2">
                                @if($homeTeamLogo)
                                <img src="{{ $homeTeamLogo }}"
                                    alt="{{ $match['event_home_team'] }}"
                                    class="w-6 h-6 sm:w-8 sm:h-8 rounded-full border object-cover">
                                @else
                                <div class="w-6 h-6 sm:w-8 sm:h-8 bg-gray-200 rounded-full flex items-center justify-center text-xs font-bold text-gray-600">
                                    {{ strtoupper(substr($match['event_home_team'] ?? 'T', 0, 1)) }}
                                </div>
                                @endif
                                <div>
                                    <span class="text-xs sm:text-sm font-semibold text-gray-900">{{ $match['event_home_team'] }}</span>
                                    @if($winner === 'home')
                                    <div class="text-xs text-green-700 font-medium">🏆 Winner</div>
                                    @endif
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-base sm:text-lg font-bold text-gray-900">{{ $homeScore ?: '' }}</div>
                                @if($homeOvers)
                                <div class="text-xs text-gray-600">({{ $homeOvers }} ov)</div>
                                @endif
                                @if($type === 'live' && $target)
                                <div class="text-xs text-orange-600 font-semibold">🎯 Target: {{ $target }}</div>
                                @elseif($type === 'live' && $isHundredBall && $remainingBalls !== null && $remainingBalls > 0)
                                <div class="text-xs text-blue-600">⚡ {{ 100 - $remainingBalls }}/100 balls</div>
                                @elseif($type === 'live' && $isHundredBall && $remainingBalls !== null && $remainingBalls <= 0)
                                    <div class="text-xs text-blue-600">⚡ 100 balls completed
                            </div>
                            @elseif($type === 'live' && $isOversBased && $remainingOvers !== null && $remainingOvers > 0)
                            <div class="text-xs text-blue-600">⚡ {{ $totalOvers - $remainingOvers }}/{{ $totalOvers }} overs</div>
                            @elseif($type === 'live' && $isOversBased && $remainingOvers !== null && $remainingOvers <= 0)
                                <div class="text-xs text-blue-600">⚡ {{ $totalOvers }} overs completed
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Away Team -->
                <div class="flex items-center justify-between bg-gray-50 rounded p-1 sm:p-2 {{ $winner === 'away' ? 'bg-green-100 border border-green-200' : '' }}">
                    <div class="flex items-center space-x-1 sm:space-x-2">
                        @if($awayTeamLogo)
                        <img src="{{ $awayTeamLogo }}"
                            alt="{{ $match['event_away_team'] }}"
                            class="w-6 h-6 sm:w-8 sm:h-8 rounded-full border object-cover">
                        @else
                        <div class="w-6 h-6 sm:w-8 sm:h-8 bg-gray-200 rounded-full flex items-center justify-center text-xs font-bold text-gray-600">
                            {{ strtoupper(substr($match['event_away_team'] ?? 'T', 0, 1)) }}
                        </div>
                        @endif
                        <div>
                            <span class="text-xs sm:text-sm font-semibold text-gray-900">{{ $match['event_away_team'] }}</span>
                            @if($winner === 'away')
                            <div class="text-xs text-green-700 font-medium">🏆 Winner</div>
                            @endif
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-base sm:text-lg font-bold text-gray-900">{{ $awayScore ?: '' }}</div>
                        @if($awayOvers)
                        <div class="text-xs text-gray-600">({{ $awayOvers }} ov)</div>
                        @endif
                        @if($type === 'live' && $isHundredBall && $isSecondInningsStarted && $remainingBalls !== null)
                        <div class="text-xs text-blue-600">⚡ {{ 100 - $remainingBalls }}/100 balls</div>
                        @elseif($type === 'live' && $isOversBased && $isSecondInningsStarted && $remainingOvers !== null)
                        <div class="text-xs text-blue-600">⚡ {{ $totalOvers - $remainingOvers }}/{{ $totalOvers }} overs</div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Match Result/Time for Today's Matches -->
            @if($type === 'today')
            <div class="bg-white rounded p-2 mb-2 text-center">
                <div class="text-sm font-semibold text-gray-800">
                    @if($result)
                    <!-- Show result for completed matches -->
                    @if($winner)
                    <span class="text-green-600">{{ $match[$winner === 'home' ? 'event_home_team' : 'event_away_team'] }}</span>
                    <span class="text-gray-600">{{ $result }}</span>
                    @else
                    <span class="text-blue-600">{{ $result }}</span>
                    @endif
                    @else
                    <!-- Show match time for matches that haven't started yet -->
                    <span class="text-blue-600">⏰ {{ $dateTimeDisplay ?: 'Time TBD' }}</span>
                    @if(!$dateTimeDisplay)
                    <div class="text-xs text-gray-500 mt-1">⏰ <span class="local-time" data-utc="{{ $utcTime ?? '' }}">{{ $match['event_time'] ?? 'No time' }}</span></div>
                    @endif
                    @endif
                </div>
            </div>
            @endif

            <!-- Match Result/Date for Finished Matches -->
            @if($type === 'finished')
            <div class="bg-green-50 rounded p-2 mb-2 text-center border border-green-200">
                @if($match['status'] ?? $match['event_status_info'] ?? $match['event_state_title'])
                    <div class="text-sm font-semibold text-green-700 mb-1">
                        🏆 {{ $match['status'] ?? $match['event_status_info'] ?? $match['event_state_title'] }}
                    </div>
                @elseif($result)
                    <div class="text-sm font-semibold text-gray-800">
                        <!-- Show result for completed matches -->
                        @if($winner)
                            <div class="text-sm font-semibold text-green-700 mb-1">
                                🏆 Match Completed
                            </div>
                            <div class="text-sm font-semibold text-gray-800">
                                {{ $match[$winner === 'home' ? 'event_home_team' : 'event_away_team'] }} {{ $result }}
                            </div>
                        @else
                            <div class="text-sm font-semibold text-green-700 mb-1">
                                🏆 Match Completed
                            </div>
                            <span class="text-blue-600">{{ $result }}</span>
                        @endif
                    </div>
                @else
                    <div class="text-sm font-semibold text-green-700 mb-1">
                        🏆 Match Completed
                    </div>
                @endif
                
                @if($isTest)
                    @if($testDay)
                        <div class="text-xs text-green-600 mt-1 font-medium">
                            {{ $testDay }}
                        </div>
                    @endif
                    @if($testState && $testState !== $testDay)
                        <div class="text-xs text-green-600 mt-1">
                            {{ $testState }}
                        </div>
                    @endif
                    @if($testStatus && $testStatus !== $testDay && $testStatus !== $testState)
                        <div class="text-xs text-green-600 mt-1">
                            {{ $testStatus }}
                        </div>
                    @endif
                @endif
                @if($dateTimeDisplay)
                <div class="text-xs text-gray-500 mt-1">
                    📅 {{ $dateTimeDisplay }}
                </div>
                @else
                <div class="text-xs text-gray-500 mt-1">
                    📅 Date Unknown
                </div>
                @endif
            </div>
            @endif

            <!-- Match Info - Different content based on match type -->
            @if($type === 'live')
            <!-- Live Match Info - Show only status -->
            <div class="bg-gray-50 rounded p-2 mb-2 text-center border border-gray-200">
                <div class="text-sm font-semibold text-gray-700">
                    🏏 {{ $match['status'] ?? $match['event_status_info'] ?? $match['event_state_title'] ?? 'Match in Progress' }}
                </div>
                @if($isTest)
                    @if($testDay)
                        <div class="text-xs text-gray-600 mt-1 font-medium">
                            {{ $testDay }}
                        </div>
                    @endif
                    @if($testState && $testState !== $testDay)
                        <div class="text-xs text-gray-600 mt-1">
                            {{ $testState }}
                        </div>
                    @endif
                    @if($testStatus && $testStatus !== $testDay && $testStatus !== $testState)
                        <div class="text-xs text-gray-600 mt-1">
                            {{ $testStatus }}
                        </div>
                    @endif
                @endif
            </div>



            @elseif($type === 'today' && $isCompleted)
            <!-- Completed Match Info - Show status and date/time -->
            <div class="bg-gray-50 rounded p-2 mb-2 text-center border border-gray-200">
                <div class="text-sm font-semibold text-gray-700 mb-1">
                    🏆 {{ $match['status'] ?? $match['event_status_info'] ?? $match['event_state_title'] ?? 'Match Completed' }}
                </div>
                
                @if($isTest)
                    @if($testDay)
                        <div class="text-xs text-gray-600 mt-1 font-medium">
                            {{ $testDay }}
                        </div>
                    @endif
                    @if($testState && $testState !== $testDay)
                        <div class="text-xs text-gray-600 mt-1">
                            {{ $testState }}
                        </div>
                    @endif
                    @if($testStatus && $testStatus !== $testDay && $testStatus !== $testState)
                        <div class="text-xs text-gray-600 mt-1">
                            {{ $testStatus }}
                        </div>
                    @endif
                @endif
                
                <div class="text-xs text-gray-500">⏰ <span class="local-time" data-utc="{{ $utcTime ?? '' }}">{{ $match['event_time'] ?? 'No time' }}</span></div>
            </div>

            @elseif($type === 'upcoming')
            <!-- Upcoming Match Info - Show date and time -->
            <div class="bg-white rounded p-2 mb-2 text-center">
                <div class="text-sm font-semibold text-gray-800">
                    @if($dateTimeDisplay)
                    <span class="text-blue-600">📅 {{ $dateTimeDisplay }}</span>
                    @else
                    <span class="text-blue-600">⏰ <span class="local-time" data-utc="{{ $utcTime ?? '' }}">{{ $match['event_time'] ?? 'Time TBD' }}</span></span>
                    @endif
                </div>
                
                @if($isTest)
                    <div class="mt-2 pt-2 border-t border-gray-200">
                        @if($testDay)
                            <div class="text-xs font-semibold text-blue-600 mb-1">
                                {{ $testDay }}
                            </div>
                        @endif
                        @if($testState && $testState !== $testDay)
                            <div class="text-xs text-blue-600 mb-1">
                                {{ $testState }}
                            </div>
                        @endif
                        @if($testStatus && $testStatus !== $testDay && $testStatus !== $testState)
                            <div class="text-xs text-blue-600">
                                {{ $testStatus }}
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            @endif

            <!-- Click hint - Only show for non-upcoming matches -->
            @if($type !== 'upcoming')
            <div class="text-center text-xs text-gray-500 group-hover:text-green-600 transition-colors duration-300">
                <span class="hidden sm:inline">Click to view full match details</span>
                <span class="sm:hidden">Tap to view details</span>
            </div>
            @endif
        </div>
        </div>
        </div>

        <script>
            // Convert UTC times to local timezone
            document.addEventListener('DOMContentLoaded', function() {
                const timeElements = document.querySelectorAll('.local-time[data-utc]');

                timeElements.forEach(function(element) {
                    const utcTime = element.getAttribute('data-utc');
                    if (utcTime) {
                        try {
                            // Parse UTC time and convert to local time
                            const utcDate = new Date(utcTime + ' UTC');
                            const localTime = utcDate.toLocaleTimeString('en-US', {
                                hour: 'numeric',
                                minute: '2-digit',
                                hour12: true
                            });
                            element.textContent = localTime;
                        } catch (e) {
                            // Fallback to original content if conversion fails
                            console.log('Time conversion failed for:', utcTime);
                        }
                    } else {
                        // If no UTC time, try to format the existing time to 12-hour format
                        const currentTime = element.textContent;
                        if (currentTime && currentTime !== 'No time' && currentTime !== 'Time TBD') {
                            try {
                                // Check if it's already in 12-hour format
                                if (!currentTime.includes('AM') && !currentTime.includes('PM')) {
                                    // Try to parse as 24-hour format and convert
                                    const timeMatch = currentTime.match(/^(\d{1,2}):(\d{2})$/);
                                    if (timeMatch) {
                                        const hours = parseInt(timeMatch[1]);
                                        const minutes = timeMatch[2];
                                        const ampm = hours >= 12 ? 'PM' : 'AM';
                                        const displayHours = hours % 12 || 12;
                                        element.textContent = `${displayHours}:${minutes} ${ampm}`;
                                    }
                                }
                            } catch (e) {
                                // Keep original time if conversion fails
                                console.log('Time format conversion failed:', e);
                            }
                        }
                    }
                });

                // Also convert any upcoming match times that have data-utc-time
                const upcomingTimeElements = document.querySelectorAll('[data-utc-time]');
                upcomingTimeElements.forEach(function(element) {
                    const utcTime = element.getAttribute('data-utc-time');
                    if (utcTime && element.textContent.includes('TBD') === false) {
                        try {
                            // Parse UTC time and convert to local time
                            const utcDate = new Date(utcTime + ' UTC');
                            const localTime = utcDate.toLocaleTimeString('en-US', {
                                hour: 'numeric',
                                minute: '2-digit',
                                hour12: true
                            });
                            // Update the time display
                            const timeDisplay = element.querySelector('.text-xs.text-gray-600');
                            if (timeDisplay) {
                                timeDisplay.textContent = '⏰ ' + localTime;
                            }
                        } catch (e) {
                            // Fallback to original content if conversion fails
                            console.log('Time conversion failed for upcoming match:', utcTime);
                        }
                    }
                });
            });
        </script>

        <style>
            .blink-dot {
                display: inline-block;
                width: 8px;
                height: 8px;
                background-color: #ef4444;
                border-radius: 50%;
                margin-right: 4px;
                animation: blink 1.5s infinite;
            }

            @keyframes blink {

                0%,
                50% {
                    opacity: 1;
                }

                51%,
                100% {
                    opacity: 0.3;
                }
            }
        </style>

        <script>
            // Handle desktop match card clicks
            document.addEventListener('DOMContentLoaded', function() {
                const matchCards = document.querySelectorAll('.match-card[data-match-url]');

                matchCards.forEach(card => {
                    card.addEventListener('click', function() {
                        const url = this.getAttribute('data-match-url');
                        if (url) {
                            window.location.href = url;
                        }
                    });
                });
            });
        </script>