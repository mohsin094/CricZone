@php
use App\Models\Image;

$homeScore = $match['event_home_final_result'] ?? null;
$awayScore = $match['event_away_final_result'] ?? null;

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

// Format score with proper overs (convert decimal overs in score string)
if (!function_exists('formatScore')) {
    function formatScore($score, $overs) {
        if (!$score || $score === '-') return $score;
        
        // If score already contains overs info in parentheses, reformat it
        if (preg_match('/^(.+)\s+\(([0-9.]+)\s+ov\)$/', $score, $matches)) {
            $scorePart = $matches[1];
            $oversPart = $matches[2];
            $formattedOvers = formatOvers($oversPart);
            return $scorePart . ' (' . $formattedOvers . ' ov)';
        }
        
        // If no overs in score but we have separate overs data, add it
        if ($overs && $overs !== '0.0') {
            $formattedOvers = formatOvers($overs);
            return $score . ' (' . $formattedOvers . ' ov)';
        }
        
        return $score;
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

// Format the scores with proper overs
$formattedHomeScore = formatScore($homeScore, $homeOvers);
$formattedAwayScore = formatScore($awayScore, $awayOvers);

$matchType = $match['event_type'] ?? '';
$matchFormat = $match['matchFormatDisplay'] ?? $match['matchFormat'] ?? $matchType;
$matchDesc = $match['matchDesc'] ?? '';
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

// Determine match status
$type = $type ?? 'mixed';
$isLive = $type === 'live';
$isUpcoming = $type === 'upcoming';
$isFinished = $type === 'finished';

// For mixed type, determine status based on match data
if ($type === 'mixed') {
    // Check if match is live (has live status)
    $isLive = !empty($match['event_status']) && strpos(strtolower($match['event_status']), 'live') !== false;
    
    // Check if match is finished - look for completion indicators
    $isFinished = false;
    
    // Check for explicit completion status
    if (!empty($match['state']) && strtolower($match['state']) === 'complete') {
        $isFinished = true;
    }
    
    // Check for status indicating completion
    if (!empty($match['status']) && (
        strpos(strtolower($match['status']), 'complete') !== false ||
        strpos(strtolower($match['status']), 'finished') !== false ||
        strpos(strtolower($match['status']), 'result') !== false ||
        strpos(strtolower($match['status']), 'rain') !== false ||
        strpos(strtolower($match['status']), 'abandoned') !== false
    )) {
        $isFinished = true;
    }
    
    // Check if match has final scores for both teams
    if (!$isFinished && $homeScore && $awayScore && 
        strpos($homeScore, '/') !== false && 
        strpos($awayScore, '/') !== false) {
        $isFinished = true;
    }
    
    // Check if match is upcoming (no scores yet and not finished)
    $isUpcoming = !$homeScore && !$awayScore && !$isLive && !$isFinished;
}

// Format time display
$timeDisplay = '';
$timeClass = '';
if ($isUpcoming) {
    $dateStr = $match['event_date_start'] ?? '';
    $timeStr = $match['event_time'] ?? '';
    if ($dateStr && $timeStr) {
        try {
            $date = \Carbon\Carbon::parse($dateStr);
            $now = \Carbon\Carbon::now();
            $diffInDays = $date->diffInDays($now);
            
            // Convert time to 12-hour format
            $formattedTime = '';
            if ($timeStr) {
                try {
                    // Handle different time formats
                    if (preg_match('/^(\d{1,2}):(\d{2})$/', $timeStr, $matches)) {
                        // Format like "14:30" or "1:00"
                        $timeCarbon = \Carbon\Carbon::createFromFormat('H:i', $timeStr);
                        $formattedTime = $timeCarbon->format('g:i A');
                    } elseif (preg_match('/^(\d{1,2}):(\d{2})\s+(AM|PM)$/i', $timeStr, $matches)) {
                        // Format like "1:00 PM" or "2:30 AM"
                        $formattedTime = $timeStr;
                    } else {
                        // Try to parse as is
                        $timeCarbon = \Carbon\Carbon::parse($timeStr);
                        $formattedTime = $timeCarbon->format('g:i A');
                    }
                } catch (\Exception $e) {
                    $formattedTime = $timeStr;
                }
            }
            
            if ($diffInDays === 0) {
                $timeDisplay = 'Today ' . $formattedTime;
                $timeClass = 'text-blue-600';
            } elseif ($diffInDays === 1) {
                $timeDisplay = 'Tomorrow ' . $formattedTime;
                $timeClass = 'text-blue-600';
            } else {
                $timeDisplay = $date->format('M d') . ', ' . $formattedTime;
                $timeClass = 'text-gray-600';
            }
        } catch (\Exception $e) {
            $timeDisplay = $timeStr;
            $timeClass = 'text-gray-600';
        }
    } else {
        $timeDisplay = 'Time TBD';
        $timeClass = 'text-gray-500';
    }
} elseif ($isFinished) {
    $dateStr = $match['endDate'] ?? $match['event_date_end'] ?? $match['event_date'] ?? '';
    if ($dateStr) {
        try {
            $date = \Carbon\Carbon::parse($dateStr);
            $now = \Carbon\Carbon::now();
            $diffInDays = $date->diffInDays($now);
            
            if ($diffInDays === 0) {
                $timeDisplay = 'Today';
                $timeClass = 'text-gray-600';
            } elseif ($diffInDays === 1) {
                $timeDisplay = 'Yesterday';
                $timeClass = 'text-gray-600';
            } else {
                $timeDisplay = $date->format('M d');
                $timeClass = 'text-gray-600';
            }
        } catch (\Exception $e) {
            $timeDisplay = 'Date Unknown';
            $timeClass = 'text-gray-500';
        }
    } else {
        $timeDisplay = 'Date Unknown';
        $timeClass = 'text-gray-500';
    }
}

// Determine winner for finished matches only
$winner = null;
$result = '';

// Only determine winner if match is finished (not live)
if ($isFinished && $homeScore && $awayScore) {
    if ($homeRuns > $awayRuns) {
        $winner = 'home';
        $margin = $homeRuns - $awayRuns;
        $result = "won by $margin runs";
    } elseif ($awayRuns > $homeRuns) {
        $winner = 'away';
        $margin = $awayRuns - $homeRuns;
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

// Get venue info
$venue = $match['venue'] ?? 'Venue TBD';
$venueParts = explode(',', $venue);
$stadium = trim($venueParts[0] ?? '');
$city = trim($venueParts[1] ?? '');

// Get team flags from images table
$homeTeamFlag = null;
$awayTeamFlag = null;

// Get team flags if image IDs are available
if (!empty($match['event_home_team_image_id'])) {
    $homeTeamFlag = Image::getTeamImageUrl($match['event_home_team_image_id'], $match['event_home_team'] ?? '');
}

if (!empty($match['event_away_team_image_id'])) {
    $awayTeamFlag = Image::getTeamImageUrl($match['event_away_team_image_id'], $match['event_away_team'] ?? '');
}
@endphp

<div class="mobile-match-card bg-white rounded-lg shadow-sm border border-gray-200 mb-1 overflow-hidden {{ $isLive || $isFinished ? 'cursor-pointer hover:shadow-md transition-shadow' : '' }}" 
     @if($isLive || $isFinished) data-match-url="{{ route('cricket.match-detail', $match['event_key']) }}" @endif>
    
    <!-- Match Header -->
    <div class="px-2 py-1 bg-gray-50 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <span class="text-xs font-medium text-gray-600 bg-white px-2 py-1 rounded-full border">
                    {{ $matchFormat }}
                </span>
                @if($matchDesc)
                    <span class="text-xs text-gray-500">• {{ $matchDesc }}</span>
                @endif
            </div>
            @if($isLive)
                <div class="flex items-center space-x-1 bg-red-50 px-2 py-1 rounded-full">
                    <div class="w-1.5 h-1.5 bg-red-500 rounded-full animate-pulse"></div>
                    <span class="text-xs font-semibold text-red-600">Live</span>
                </div>
            @endif
        </div>
    </div>

        <!-- Match Content -->
        <div class="p-2">
        <!-- Teams -->
        <div class="space-y-2">
            @if($isUpcoming)
                <!-- Upcoming Match with Time Display -->
                <div class="text-center mb-3">
                    <div class="inline-flex items-center space-x-2 bg-blue-50 rounded-full px-3 py-1">
                        <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-xs font-semibold text-blue-600">{{ $timeDisplay }}</span>
                    </div>
                </div>
                
                <!-- Teams with VS separator -->
                <div class="flex items-center justify-between">
                    <!-- Home Team -->
                    <div class="flex items-center space-x-2 flex-1">
                        @if($homeTeamFlag)
                            <img src="{{ $homeTeamFlag }}" 
                                 alt="{{ $match['event_home_team'] }}" 
                                 class="w-6 h-6 rounded-full border border-gray-200 object-cover">
                        @elseif($homeTeamLogo)
                            <img src="{{ $homeTeamLogo }}" 
                                 alt="{{ $match['event_home_team'] }}" 
                                 class="w-6 h-6 rounded-full border border-gray-200 object-cover">
                        @else
                            <div class="w-6 h-6 bg-gradient-to-br from-gray-200 to-gray-300 rounded-full flex items-center justify-center text-xs font-bold text-gray-600 border border-gray-200">
                                {{ strtoupper(substr($match['event_home_team'] ?? 'T', 0, 1)) }}
                            </div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <div class="font-medium text-gray-900 text-xs truncate">{{ $match['event_home_team'] }}</div>
                        </div>
                    </div>
                    
                    <!-- VS Separator -->
                    <div class="flex items-center mx-3">
                        <div class="w-8 h-0.5 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full"></div>
                        <span class="mx-2 text-xs font-bold text-gray-600">VS</span>
                        <div class="w-8 h-0.5 bg-gradient-to-r from-purple-500 to-blue-500 rounded-full"></div>
                    </div>
                    
                    <!-- Away Team -->
                    <div class="flex items-center space-x-2 flex-1 justify-end">
                        <div class="min-w-0 flex-1 text-right">
                            <div class="font-medium text-gray-900 text-xs truncate">{{ $match['event_away_team'] }}</div>
                        </div>
                        @if($awayTeamFlag)
                            <img src="{{ $awayTeamFlag }}" 
                                 alt="{{ $match['event_away_team'] }}" 
                                 class="w-6 h-6 rounded-full border border-gray-200 object-cover">
                        @elseif($awayTeamLogo)
                            <img src="{{ $awayTeamLogo }}" 
                                 alt="{{ $match['event_away_team'] }}" 
                                 class="w-6 h-6 rounded-full border border-gray-200 object-cover">
                        @else
                            <div class="w-6 h-6 bg-gradient-to-br from-gray-200 to-gray-300 rounded-full flex items-center justify-center text-xs font-bold text-gray-600 border border-gray-200">
                                {{ strtoupper(substr($match['event_away_team'] ?? 'T', 0, 1)) }}
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <!-- Regular Match Display -->
                <!-- Home Team -->
                <div class="flex items-center justify-between {{ $winner === 'home' ? 'bg-green-50 rounded p-1.5 border border-green-200' : '' }}">
                    <div class="flex items-center space-x-2">
                        @if($homeTeamFlag)
                            <img src="{{ $homeTeamFlag }}" 
                                 alt="{{ $match['event_home_team'] }}" 
                                 class="w-6 h-6 rounded-full border border-gray-200 object-cover">
                        @elseif($homeTeamLogo)
                            <img src="{{ $homeTeamLogo }}" 
                                 alt="{{ $match['event_home_team'] }}" 
                                 class="w-6 h-6 rounded-full border border-gray-200 object-cover">
                        @else
                            <div class="w-6 h-6 bg-gradient-to-br from-gray-200 to-gray-300 rounded-full flex items-center justify-center text-xs font-bold text-gray-600 border border-gray-200">
                                {{ strtoupper(substr($match['event_home_team'] ?? 'T', 0, 1)) }}
                            </div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <div class="font-medium text-gray-900 text-xs truncate">{{ $match['event_home_team'] }}</div>
                        </div>
                        @if($winner === 'home')
                            <div class="text-green-600 text-sm font-bold ml-1">✓</div>
                        @endif
                    </div>
                    <div class="text-right ml-2">
                        @if($isUpcoming)
                            <div class="text-sm font-bold text-gray-400">-</div>
                        @else
                            <div class="text-sm font-bold text-gray-900">{{ $formattedHomeScore ?: '-' }}</div>
                        @endif
                    </div>
                </div>

                <!-- Away Team -->
                <div class="flex items-center justify-between {{ $winner === 'away' ? 'bg-green-50 rounded p-1.5 border border-green-200' : '' }}">
                    <div class="flex items-center space-x-2">
                        @if($awayTeamFlag)
                            <img src="{{ $awayTeamFlag }}" 
                                 alt="{{ $match['event_away_team'] }}" 
                                 class="w-6 h-6 rounded-full border border-gray-200 object-cover">
                        @elseif($awayTeamLogo)
                            <img src="{{ $awayTeamLogo }}" 
                                 alt="{{ $match['event_away_team'] }}" 
                                 class="w-6 h-6 rounded-full border border-gray-200 object-cover">
                        @else
                            <div class="w-6 h-6 bg-gradient-to-br from-gray-200 to-gray-300 rounded-full flex items-center justify-center text-xs font-bold text-gray-600 border border-gray-200">
                                {{ strtoupper(substr($match['event_away_team'] ?? 'T', 0, 1)) }}
                            </div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <div class="font-medium text-gray-900 text-xs truncate">{{ $match['event_away_team'] }}</div>
                        </div>
                        @if($winner === 'away')
                            <div class="text-green-600 text-sm font-bold ml-1">✓</div>
                        @endif
                    </div>
                    <div class="text-right ml-2">
                        @if($isUpcoming)
                            <div class="text-sm font-bold text-gray-400">-</div>
                        @else
                            <div class="text-sm font-bold text-gray-900">{{ $formattedAwayScore ?: '-' }}</div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- Match Status/Result -->
        <div class="mt-2 pt-2 border-t border-gray-100">
            @if($isLive)
                <div class="text-center bg-red-50 rounded p-2">
                    <div class="text-xs font-semibold text-red-600">
                        {{ $match['status'] ?? $match['event_status_info'] ?? $match['event_state_title'] ?? 'Match in Progress' }}
                    </div>
                    @if($isTest)
                        @if($testDay)
                            <div class="text-xs text-red-500 mt-1 font-medium">
                                {{ $testDay }}
                            </div>
                        @endif
                        @if($testState && $testState !== $testDay)
                            <div class="text-xs text-red-500 mt-1">
                                {{ $testState }}
                            </div>
                        @endif
                        @if($testStatus && $testStatus !== $testDay && $testStatus !== $testState)
                            <div class="text-xs text-red-500 mt-1">
                                {{ $testStatus }}
                            </div>
                        @endif
                        @if(!$testDay && !$testState && !$testStatus && !empty($match['status']))
                            <div class="text-xs text-red-500 mt-1">
                                {{ $match['status'] }}
                            </div>
                        @endif
                    @elseif(!empty($match['event_status_info']))
                        <div class="text-xs text-red-500 mt-1">
                            {{ $match['event_status_info'] }}
                        </div>
                    @endif
                    @if($timeDisplay)
                        <div class="text-xs {{ $timeClass }}">{{ $timeDisplay }}</div>
                    @endif
                </div>
            @elseif($isFinished)
                <div class="text-center bg-green-50 rounded p-2">
                    @if($match['status'] ?? $match['event_status_info'] ?? $match['event_state_title'])
                        <div class="text-xs font-semibold text-green-600 mb-1">
                            🏆 {{ $match['status'] ?? $match['event_status_info'] ?? $match['event_state_title'] }}
                        </div>
                    @elseif($result)
                        <div class="text-xs font-semibold text-green-600 mb-1">
                            🏆 Match Completed
                        </div>
                        <div class="text-xs font-semibold text-gray-800">
                            @if($winner)
                                {{ $match[$winner === 'home' ? 'event_home_team' : 'event_away_team'] }} {{ $result }}
                            @else
                                {{ $result }}
                            @endif
                        </div>
                    @else
                        <div class="text-xs font-semibold text-green-600 mb-1">
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
                        @if(!$testDay && !$testState && !$testStatus && !empty($match['status']))
                            <div class="text-xs text-green-600 mt-1">
                                {{ $match['status'] }}
                            </div>
                        @endif
                    @endif
                    @if($timeDisplay)
                        <div class="text-xs {{ $timeClass }}">{{ $timeDisplay }}</div>
                    @endif
                </div>
            @elseif($isUpcoming)
                @if($isTest)
                    <div class="text-center bg-blue-50 rounded p-2">
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
                        @if(!$testDay && !$testState && !$testStatus && !empty($match['status']))
                            <div class="text-xs text-blue-600">
                                {{ $match['status'] }}
                            </div>
                        @endif
                    </div>
                @elseif($stadium)
                    <div class="text-center">
                        <div class="text-xs text-gray-500">{{ $stadium }}{{ $city ? ', ' . $city : '' }}</div>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>

<script>
// Handle mobile match card clicks
document.addEventListener('DOMContentLoaded', function() {
    const matchCards = document.querySelectorAll('.mobile-match-card[data-match-url]');
    
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
