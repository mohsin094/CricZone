@php
$homeScore = $match['event_home_final_result'] ?? null;
$awayScore = $match['event_away_final_result'] ?? null;

$homeRuns = $homeScore ? (int) explode('/', $homeScore)[0] : 0;
$awayRuns = $awayScore ? (int) explode('/', $awayScore)[0] : 0;

$matchType = $match['event_type'] ?? '';
$isHundredBall = strpos(strtolower($matchType), 'hundred') !== false;
$isOversBased = strpos(strtolower($matchType), 'odi') !== false || strpos(strtolower($matchType), 't20') !== false || strpos(strtolower($matchType), 'test') !== false;

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

// Determine winner for completed matches
$winner = null;
$result = '';
if ($type === 'today' && $homeScore && $awayScore) {
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

// Check if match is completed (has final scores for both teams)
$isCompleted = $homeScore && $awayScore && 
               strpos($homeScore, '/') !== false && 
               strpos($awayScore, '/') !== false;

// Check if match is upcoming (no scores yet)
$isUpcoming = !$homeScore && !$awayScore;

// Format match date and time
$matchDateTime = '';
$matchDate = '';
$matchTime = '';

// For upcoming matches, use event_date_start to get both date and time
if ($type === 'upcoming' && isset($match['event_date_start'])) {
    try {
        $dateString = $match['event_date_start'];
        
        // If it's already a timestamp or ISO format
        if (is_numeric($dateString)) {
            $date = new DateTime('@' . $dateString);
        } else {
            $date = new DateTime($dateString);
        }
        
        // Format both date and time for upcoming matches
        $matchDate = $date->format('M d');
        $matchTime = $date->format('g:i A');
        $matchDateTime = $matchDate . ', ' . $matchTime;
        
        // Store UTC time for JavaScript conversion to local time
        $utcTime = $date->format('Y-m-d H:i:s');
        
    } catch (Exception $e) {
        // Fallback to original string if parsing fails
        $matchDateTime = $dateString;
    }
} elseif (isset($match['event_time'])) {
    try {
        // Handle different time formats for other match types
        $timeString = $match['event_time'];
        
        // If it's already a timestamp or ISO format
        if (is_numeric($timeString)) {
            $date = new DateTime('@' . $timeString);
        } else {
            $date = new DateTime($timeString);
        }
        
        // Format the time only (since it's event_time)
        $matchDateTime = $date->format('g:i A');
        
        // Store UTC time for JavaScript conversion to local time
        $utcTime = $date->format('Y-m-d H:i:s');
    } catch (Exception $e) {
        // Fallback to original string if parsing fails
        $matchDateTime = $timeString;
    }
}
@endphp

<div class="match-card {{ $type === 'upcoming' ? '' : 'cursor-pointer transform transition-all duration-300 hover:scale-[1.02] hover:shadow-lg' }}"
     data-league="{{ $match['league_name'] ?? '' }}"
     data-match-type="{{ $match['event_type'] ?? '' }}"
     data-home-team="{{ $match['event_home_team'] ?? '' }}"
     data-away-team="{{ $match['event_away_team'] ?? '' }}"
     data-date="{{ $match['event_date_start'] ?? '' }}"
     data-utc-time="{{ $utcTime ?? '' }}"
     @if($type !== 'upcoming') onclick="window.location.href='{{ route('cricket.match-detail', $match['event_key']) }}'" @endif>
    
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
            <!-- Match Header - Compact -->
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold px-1 sm:px-2 py-1 rounded-full
                    @if($type === 'live') text-red-700 bg-red-100
                    @elseif($type === 'today') text-blue-700 bg-blue-100
                    @else text-purple-700 bg-purple-100 @endif">
                    @if($type === 'live') <span class="blink-dot"></span> @elseif($type === 'today') 📅 @else ⏰ @endif
                    <span>{{ $match['event_type'] ?? 'Match' }}</span>
                </span>
                <div class="text-right text-xs">
                    <div class="text-gray-800 hidden sm:block">{{ $match['event_stadium'] ?? 'Venue TBD' }}</div>
                    <div class="text-gray-500">{{ $match['league_name'] ?? '' }}</div>
                </div>
            </div>

            <!-- Teams - Compact but Detailed -->
            <div class="space-y-1 sm:space-y-2 mb-2">
                <!-- Home Team -->
                <div class="flex items-center justify-between bg-gray-50 rounded p-1 sm:p-2 {{ $winner === 'home' ? 'bg-green-100 border border-green-200' : '' }}">
                    <div class="flex items-center space-x-1 sm:space-x-2">
                        @if(isset($match['event_home_team_logo']))
                        <img src="{{ $match['event_home_team_logo'] }}"
                            alt="{{ $match['event_home_team'] }}"
                            class="w-6 h-6 sm:w-8 sm:h-8 rounded-full border">
                        @else
                        <div class="w-6 h-6 sm:w-8 sm:h-8 bg-gray-200 rounded-full flex items-center justify-center text-xs">🏏</div>
                        @endif
                        <div>
                            <span class="text-xs sm:text-sm font-semibold text-gray-900">{{ $match['event_home_team'] }}</span>
                            @if($winner === 'home')
                            <div class="text-xs text-green-700 font-medium">🏆 Winner</div>
                            @endif
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-base sm:text-lg font-bold text-gray-900">{{ $match['event_home_final_result'] ?? $match['event_service_home'] ?? '0/0' }}</div>
                        @if($type === 'live' && $target)
                        <div class="text-xs text-orange-600 font-semibold">🎯 Target: {{ $target }}</div>
                        @elseif($type === 'live' && $isHundredBall && $remainingBalls !== null && $remainingBalls > 0)
                        <div class="text-xs text-blue-600">⚡ {{ 100 - $remainingBalls }}/100 balls</div>
                        @elseif($type === 'live' && $isHundredBall && $remainingBalls !== null && $remainingBalls <= 0)
                        <div class="text-xs text-blue-600">⚡ 100 balls completed</div>
                        @elseif($type === 'live' && $isOversBased && $remainingOvers !== null && $remainingOvers > 0)
                        <div class="text-xs text-blue-600">⚡ {{ $totalOvers - $remainingOvers }}/{{ $totalOvers }} overs</div>
                        @elseif($type === 'live' && $isOversBased && $remainingOvers !== null && $remainingOvers <= 0)
                        <div class="text-xs text-blue-600">⚡ {{ $totalOvers }} overs completed</div>
                        @endif
                    </div>
                </div>

                <!-- Away Team -->
                <div class="flex items-center justify-between bg-gray-50 rounded p-1 sm:p-2 {{ $winner === 'away' ? 'bg-green-100 border border-green-200' : '' }}">
                    <div class="flex items-center space-x-1 sm:space-x-2">
                        @if(isset($match['event_away_team_logo']))
                        <img src="{{ $match['event_away_team_logo'] }}"
                            alt="{{ $match['event_away_team'] }}"
                            class="w-6 h-6 sm:w-8 sm:h-8 rounded-full border">
                        @else
                        <div class="w-6 h-6 sm:w-8 sm:h-8 bg-gray-200 rounded-full flex items-center justify-center text-xs">🏏</div>
                        @endif
                        <div>
                            <span class="text-xs sm:text-sm font-semibold text-gray-900">{{ $match['event_away_team'] }}</span>
                            @if($winner === 'away')
                            <div class="text-xs text-green-700 font-medium">🏆 Winner</div>
                            @endif
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-base sm:text-lg font-bold text-gray-900">{{ $match['event_away_final_result'] ?? $match['event_service_away'] ?? '0/0' }}</div>
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
        <span class="text-blue-600">⏰ {{ $matchDateTime ?: 'Time TBD' }}</span>
        @if(!$matchDateTime)
                                <div class="text-xs text-gray-500 mt-1">⏰ <span class="local-time" data-utc="{{ $utcTime ?? '' }}">{{ $match['event_time'] ?? 'No time' }}</span></div>
        @endif
                    @endif
                </div>
            </div>
            @endif

            <!-- Match Info - Different content based on match type -->
            @if($type === 'live')
            <!-- Live Match Info - Show progress and match details -->
            <div class="hidden sm:block bg-gray-50 rounded p-1 sm:p-2 border border-gray-200 mb-2">
                <div class="grid grid-cols-3 gap-1 text-xs">
                    <!-- Match Detail / Current Balls/Overs -->
                    @if($isHundredBall && !$isFirstInningsComplete)
                    <div class="text-center">
                        <div class="font-semibold text-gray-500">Balls</div>
                        <div class="text-gray-400">
                            @if($remainingBalls !== null)
                            {{ 100 - $remainingBalls }}
                            @else
                            In Progress
                            @endif
                        </div>
                    </div>
                    @elseif($isOversBased && !$isFirstInningsComplete)
                    <div class="text-center">
                        <div class="font-semibold text-gray-500">Overs</div>
                        <div class="text-gray-400">
                            @if($remainingOvers !== null)
                            {{ $totalOvers - $remainingOvers }}
                            @else
                            In Progress
                            @endif
                        </div>
                    </div>
                    @elseif($target || (!$isHundredBall && !$isOversBased))
                    <div class="text-center">
                        <div class="font-semibold text-gray-500">Detail</div>
                        <div class="text-gray-400">{{ $match['event_status_info'] ?? '-' }}</div>
                    </div>
                    @endif

                    <!-- Runs Needed / Balls/Overs Remaining / Second Team Progress -->
                    @if($awayScore && $homeScore && $target)
                    <div class="text-center">
                        <div class="font-semibold text-gray-500">Req RR</div>
                        <div class="text-orange-400 font-bold">
                            @php
                            $runsNeeded = max(0, $target - $awayRuns);
                            $oversLeft = $isHundredBall ? ($remainingBalls / 6) : ($remainingOvers ?? 0);
                            $requiredRR = $oversLeft > 0 ? round($runsNeeded / $oversLeft, 2) : 0;
                            @endphp
                            {{ $requiredRR }}
                        </div>
                    </div>
                    @elseif($isHundredBall && $isSecondInningsStarted && $remainingBalls !== null)
                    <div class="text-center">
                        <div class="font-semibold text-gray-500">2nd Team</div>
                        <div class="text-blue-400 font-bold">{{ 100 - $remainingBalls }}/100</div>
                    </div>
                    @elseif($isOversBased && $isSecondInningsStarted && $remainingOvers !== null)
                    <div class="text-center">
                        <div class="font-semibold text-gray-500">2nd Team</div>
                        <div class="text-blue-400 font-bold">{{ $totalOvers - $remainingOvers }}/{{ $totalOvers }}</div>
                    </div>
                    @elseif($isHundredBall && $remainingBalls !== null)
                    <div class="text-center">
                        <div class="font-semibold text-gray-500">Left</div>
                        <div class="text-blue-400 font-bold">{{ $remainingBalls }}</div>
                    </div>
                    @elseif($isOversBased && $remainingOvers !== null)
                    <div class="text-center">
                        <div class="font-semibold text-gray-500">Left</div>
                        <div class="text-blue-400 font-bold">{{ $remainingOvers }}</div>
                    </div>
                    @endif

                    <!-- Toss Info -->
                    @if(isset($match['event_toss']))
                    <div class="text-center">
                        <div class="font-semibold text-gray-500">Toss</div>
                        <div class="text-gray-400">{{ $match['event_toss'] }}</div>
                    </div>
                    @endif
                </div>
            </div>
            @elseif($type === 'today' && $isCompleted)
            <!-- Completed Match Info - Show only date and time -->
                   <div class="text-xs text-gray-500 mt-1">⏰ <span class="local-time" data-utc="{{ $utcTime ?? '' }}">{{ $match['event_time'] ?? 'No time' }}</span></div>

            @elseif($type === 'upcoming')
            <!-- Upcoming Match Info - Show date and time -->
            <div class="bg-white rounded p-2 mb-2 text-center">
                <div class="text-sm font-semibold text-gray-800">
                    @if($matchDateTime)
                        <span class="text-blue-600">📅 {{ $matchDate }}</span>
                        <div class="text-xs text-gray-600 mt-1">⏰ {{ $matchTime }}</div>
                    @else
                        <span class="text-blue-600">⏰ <span class="local-time" data-utc="{{ $utcTime ?? '' }}">{{ $match['event_time'] ?? 'Time TBD' }}</span></span>
                    @endif
                </div>
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
