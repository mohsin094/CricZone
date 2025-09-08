@php
$homeScore = $match['event_home_final_result'] ?? null;
$awayScore = $match['event_away_final_result'] ?? null;

$homeRuns = $homeScore ? (int) explode('/', $homeScore)[0] : 0;
$awayRuns = $awayScore ? (int) explode('/', $awayScore)[0] : 0;

$matchType = $match['event_type'] ?? '';
$matchFormat = $match['matchFormatDisplay'] ?? $match['matchFormat'] ?? $matchType;
$matchDesc = $match['matchDesc'] ?? '';

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
    
    // Check if match is finished (has final scores for both teams)
    $isFinished = $homeScore && $awayScore && 
                  strpos($homeScore, '/') !== false && 
                  strpos($awayScore, '/') !== false;
    
    // Check if match is upcoming (no scores yet)
    $isUpcoming = !$homeScore && !$awayScore && !$isLive;
}

// Format time display
$timeDisplay = '';
if ($isUpcoming) {
    $dateStr = $match['event_date_start'] ?? '';
    $timeStr = $match['event_time'] ?? '';
    if ($dateStr && $timeStr) {
        try {
            $date = \Carbon\Carbon::parse($dateStr);
            $now = \Carbon\Carbon::now();
            $diffInDays = $date->diffInDays($now);
            
            if ($diffInDays === 0) {
                $timeDisplay = 'Today, ' . $timeStr;
            } elseif ($diffInDays === 1) {
                $timeDisplay = 'Tomorrow, ' . $timeStr;
            } else {
                $timeDisplay = $date->format('M d') . ', ' . $timeStr;
            }
        } catch (\Exception $e) {
            $timeDisplay = $timeStr;
        }
    } else {
        $timeDisplay = 'Time TBD';
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
            } elseif ($diffInDays === 1) {
                $timeDisplay = 'Yesterday';
            } else {
                $timeDisplay = $date->format('M d');
            }
        } catch (\Exception $e) {
            $timeDisplay = 'Date Unknown';
        }
    } else {
        $timeDisplay = 'Date Unknown';
    }
}

// Determine winner for finished matches
$winner = null;
$result = '';
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
@endphp

<div class="match-item {{ $isLive || $isFinished ? 'cursor-pointer' : '' }}" 
     @if($isLive || $isFinished) data-match-url="{{ route('cricket.match-detail', $match['event_key']) }}" @endif>
    
    <!-- Match Type and Description -->
    <div class="flex items-center justify-between mb-3">
        <div class="flex items-center space-x-2">
            <span class="text-xs font-medium text-gray-600">{{ $matchFormat }}</span>
            @if($matchDesc)
                <span class="text-xs text-gray-500">• {{ $matchDesc }}</span>
            @endif
        </div>
        @if($isLive)
            <span class="live-indicator text-xs font-semibold">• Live</span>
        @endif
    </div>

    <!-- Teams -->
    <div class="space-y-2">
        <!-- Home Team -->
        <div class="team-row {{ $winner === 'home' ? 'bg-green-50 rounded-lg p-2' : '' }}">
            <div class="team-info">
                @if($homeTeamLogo)
                    <img src="{{ $homeTeamLogo }}" alt="{{ $match['event_home_team'] }}" class="team-flag">
                @else
                    <div class="team-flag bg-gray-200 flex items-center justify-center text-xs font-bold text-gray-600">
                        {{ strtoupper(substr($match['event_home_team'] ?? 'T', 0, 1)) }}
                    </div>
                @endif
                <span class="team-name">{{ $match['event_home_team'] }}</span>
                @if($winner === 'home')
                    <span class="text-xs text-green-600 font-medium">✓</span>
                @endif
            </div>
            <div class="text-right">
                <div class="team-score">{{ $homeScore ?: '' }}</div>
            </div>
        </div>

        <!-- Away Team -->
        <div class="team-row {{ $winner === 'away' ? 'bg-green-50 rounded-lg p-2' : '' }}">
            <div class="team-info">
                @if($awayTeamLogo)
                    <img src="{{ $awayTeamLogo }}" alt="{{ $match['event_away_team'] }}" class="team-flag">
                @else
                    <div class="team-flag bg-gray-200 flex items-center justify-center text-xs font-bold text-gray-600">
                        {{ strtoupper(substr($match['event_away_team'] ?? 'T', 0, 1)) }}
                    </div>
                @endif
                <span class="team-name">{{ $match['event_away_team'] }}</span>
                @if($winner === 'away')
                    <span class="text-xs text-green-600 font-medium">✓</span>
                @endif
            </div>
            <div class="text-right">
                <div class="team-score">{{ $awayScore ?: '' }}</div>
            </div>
        </div>
    </div>

    <!-- Match Status/Time -->
    <div class="mt-3">
        <div class="match-status">
            @if($isLive)
                <div class="text-red-600 font-medium text-sm">{{ $match['event_status_info'] ?? 'Match in Progress' }}</div>
            @elseif($isFinished && $result)
                <div class="text-green-600 font-medium text-sm">
                    @if($winner)
                        {{ $match[$winner === 'home' ? 'event_home_team' : 'event_away_team'] }} {{ $result }}
                    @else
                        {{ $result }}
                    @endif
                </div>
            @elseif($isUpcoming)
                <div class="text-blue-600 font-medium text-sm">{{ $timeDisplay }}</div>
            @endif
        </div>
        
        <!-- Venue Info -->
        <div class="venue-info">
            {{ $stadium }}{{ $city ? ', ' . $city : '' }}
        </div>
    </div>
</div>

<script>
// Handle mobile match item clicks
document.addEventListener('DOMContentLoaded', function() {
    const matchItems = document.querySelectorAll('.match-item[data-match-url]');
    
    matchItems.forEach(item => {
        item.addEventListener('click', function() {
            const url = this.getAttribute('data-match-url');
            if (url) {
                window.location.href = url;
            }
        });
    });
});
</script>
