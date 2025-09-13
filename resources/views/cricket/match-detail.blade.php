@extends('layouts.app')

@section('title', $match['event_home_team'] . ' vs ' . $match['event_away_team'] . ' - Match Details - CricZone.pk')

@section('content')
<!-- Page Loading Overlay - Shows until content is fully loaded -->
<div id="pageLoader" class="fixed inset-0 bg-gradient-to-br from-green-50 to-blue-50 z-50 flex items-center justify-center">
    <div class="text-center">
        <div class="inline-flex flex-col items-center px-8 py-8 bg-white rounded-2xl shadow-2xl border border-gray-100">
            <!-- Logo-style loader -->
            <div class="relative mb-4">
                <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-blue-600 rounded-full flex items-center justify-center shadow-lg">
                    <div class="text-white text-2xl font-bold">🏏</div>
                </div>
                <!-- Animated ring around logo -->
                <div class="absolute inset-0 w-16 h-16 border-4 border-transparent border-t-green-500 border-r-blue-600 rounded-full animate-spin"></div>
            </div>
            
            <!-- Site name with cricket theme -->
            <div class="mb-2">
                <div class="text-2xl font-bold bg-gradient-to-r from-green-600 to-blue-600 bg-clip-text text-transparent">
                    CricZone
                </div>
                <div class="text-xs text-gray-500 mt-1">Cricket Live Scores & Updates</div>
            </div>
            
            <!-- Loading text -->
            <div class="text-gray-600 text-base font-medium">Loading match details...</div>
        </div>
    </div>
</div>

<style>
    /* Custom styles for match detail page */
    .tab-content {
        display: none;
    }
    
    .tab-content.active {
        display: block;
        animation: fadeIn 0.3s ease-in;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .live-pulse {
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.7; }
        100% { opacity: 1; }
    }
    
    .commentary-entry {
        transition: all 0.3s ease;
    }
    
    .commentary-entry:hover {
        transform: translateY(-2px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .player-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .player-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 12px -3px rgba(0, 0, 0, 0.1);
    }
    
    .scroll-container {
        scrollbar-width: thin;
        scrollbar-color: #c5d5e4 #f1f5f9;
    }
    
    .scroll-container::-webkit-scrollbar {
        width: 4px;
    }
    
    .scroll-container::-webkit-scrollbar-track {
        background: #f1f5f9;
    }
    
    .scroll-container::-webkit-scrollbar-thumb {
        background-color: #c5d5e4;
        border-radius: 20px;
    }
    
    .tab-button {
        transition: all 0.2s ease;
        border-bottom: 2px solid transparent;
        font-size: 0.875rem;
        padding: 0.5rem 0.75rem;
    }
    
    .tab-button.active {
        border-bottom: 2px solid #10b981;
        color: #10b981;
        font-weight: 600;
    }
    
    .stat-card {
        transition: transform 0.3s ease;
    }
    
    .stat-card:hover {
        transform: scale(1.02);
    }

    /* Match info styles */
    .match-info-card {
        background: white;
        border-radius: 0.75rem;
        border: 1px solid #e5e7eb;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        margin-bottom: 1rem;
    }
    
    .match-info-header {
        background: #f8fafc;
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #e5e7eb;
        border-radius: 0.75rem 0.75rem 0 0;
        font-weight: 600;
        color: #374151;
        font-size: 0.875rem;
    }
    
    .match-info-content {
        padding: 1rem;
    }
    
    /* Scorecard styles */
    .scorecard-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 1rem;
    }
    
    .scorecard-table th {
        background: #f8fafc;
        padding: 0.5rem;
        text-align: left;
        font-weight: 600;
        color: #374151;
        border-bottom: 1px solid #e5e7eb;
        font-size: 0.75rem;
    }
    
    .scorecard-table td {
        padding: 0.5rem;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.75rem;
    }
    
    .scorecard-table tr:hover {
        background: #f8fafc;
    }
    
    /* Commentary styles */
    .commentary-tabs {
        display: flex;
        border-bottom: 1px solid #e5e7eb;
        margin-bottom: 1rem;
        overflow-x: auto;
    }
    
    .commentary-tab {
        padding: 0.5rem 1rem;
        border: none;
        background: none;
        color: #6b7280;
        font-size: 0.75rem;
        cursor: pointer;
        white-space: nowrap;
        border-bottom: 2px solid transparent;
    }
    
    .commentary-tab.active {
        color: #10b981;
        border-bottom-color: #10b981;
        font-weight: 600;
    }
    
    .commentary-entry {
        padding: 0.75rem;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .ball-indicator {
        width: 1.5rem;
        height: 1.5rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.625rem;
        font-weight: 600;
        color: white;
        animation: ballBounce 0.5s ease-out;
    }
    
    @keyframes ballBounce {
        0% { transform: scale(0.5); opacity: 0; }
        50% { transform: scale(1.2); }
        100% { transform: scale(1); opacity: 1; }
    }
    
    .ball-0 { background: #6b7280; }
    .ball-1 { background: #6b7280; }
    .ball-2 { background: #6b7280; }
    .ball-3 { background: #6b7280; }
    .ball-4 { background: #3b82f6; }
    .ball-6 { background: #10b981; }
    .ball-w { background: #ef4444; }
    .ball-nb { background: #f59e0b; }
    .ball-lb { background: #8b5cf6; }
    .ball-b { background: #8b5cf6; }
    
    /* Partnership styles */
    .partnership-bar {
        height: 0.5rem;
        background: #e5e7eb;
        border-radius: 0.25rem;
        overflow: hidden;
        margin: 0.25rem 0;
    }
    
    .partnership-fill {
        height: 100%;
        background: linear-gradient(90deg, #10b981, #3b82f6);
        border-radius: 0.25rem;
        transition: width 0.8s ease-out;
    }
    
    /* Live match header styles */
    .live-match-header {
        background: linear-gradient(135deg, #1e40af, #1e3a8a);
        color: white;
        border-radius: 0.75rem;
        padding: 1rem;
        margin-bottom: 1rem;
    }
    
    .live-score-display {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }
    
    .team-score {
        text-align: center;
        flex: 1;
    }
    
    .team-score h3 {
        font-size: 1.25rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    
    .team-score .score {
        font-size: 2rem;
        font-weight: 900;
        margin-bottom: 0.25rem;
    }
    
    .team-score .overs {
        font-size: 0.875rem;
        opacity: 0.9;
    }
    
    .match-status {
        text-align: center;
        flex: 1;
    }
    
    .match-status .status-text {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    
    .match-status .status-info {
        font-size: 0.875rem;
        opacity: 0.9;
    }
    
    .run-rates {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 0.5rem;
        padding: 0.75rem;
        text-align: center;
    }
    
    .run-rates .rates {
        font-size: 1.125rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    
    .run-rates .target-info {
        font-size: 0.875rem;
        opacity: 0.9;
    }
    
    /* Power play indicator */
    .power-play {
        background: #ef4444;
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.75rem;
        font-weight: 600;
        margin-left: 0.5rem;
    }
    
    /* Team form indicators */
    .team-form {
        display: flex;
        gap: 0.25rem;
        margin-top: 0.5rem;
    }
    
    .form-indicator {
        width: 1rem;
        height: 1rem;
        border-radius: 0.125rem;
        font-size: 0.625rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        color: white;
    }
    
    .form-win { background: #10b981; }
    .form-loss { background: #ef4444; }
    
    /* Venue stats circular chart */
    .venue-chart {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: conic-gradient(#ef4444 0deg 151deg, #10b981 151deg 360deg);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        position: relative;
    }
    
    .venue-chart::before {
        content: '';
        position: absolute;
        width: 80px;
        height: 80px;
        background: white;
        border-radius: 50%;
    }
    
    .venue-chart .chart-text {
        position: relative;
        z-index: 1;
        text-align: center;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    /* Pace vs Spin chart */
    .pace-spin-chart {
        background: #f8fafc;
        border-radius: 0.5rem;
        padding: 1rem;
        margin: 1rem 0;
    }
    
    .chart-bar {
        height: 2rem;
        background: #e5e7eb;
        border-radius: 0.25rem;
        overflow: hidden;
        margin: 0.5rem 0;
        position: relative;
    }
    
    .chart-fill {
        height: 100%;
        border-radius: 0.25rem;
        transition: width 1s ease-out;
    }
    
    .pace-fill { background: #10b981; }
    .spin-fill { background: #ef4444; }
    
    .chart-label {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: white;
        font-size: 0.75rem;
        font-weight: 600;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
    }
    
    /* Player lineup styles */
    .player-lineup {
        background: white;
        border-radius: 0.75rem;
        border: 1px solid #e5e7eb;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        margin-bottom: 1rem;
    }
    
    .lineup-header {
        background: #f8fafc;
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #e5e7eb;
        border-radius: 0.75rem 0.75rem 0 0;
        font-weight: 600;
        color: #374151;
        font-size: 0.875rem;
    }
    
    .lineup-content {
        padding: 1rem;
    }
    
    .player-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.5rem 0;
        border-bottom: 1px solid #f1f5f9;
    }
    
    .player-item:last-child {
        border-bottom: none;
    }
    
    .player-avatar {
        width: 2rem;
        height: 2rem;
        border-radius: 50%;
        background: #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        color: #6b7280;
    }
    
    .player-info {
        flex: 1;
    }
    
    .player-name {
        font-weight: 600;
        color: #374151;
        font-size: 0.875rem;
    }
    
    .player-role {
        font-size: 0.75rem;
        color: #6b7280;
    }
    
    /* Toss information */
    .toss-info {
        background: #fef3c7;
        border: 1px solid #f59e0b;
        border-radius: 0.5rem;
        padding: 0.75rem;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .toss-icon {
        width: 1.5rem;
        height: 1.5rem;
        background: #f59e0b;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 0.75rem;
    }
    
    .toss-text {
        color: #92400e;
        font-weight: 600;
        font-size: 0.875rem;
    }

    /* Ball result animations */
    .ball-result {
        animation: ballBounce 0.6s ease-out;
    }
    
    .ball-result:nth-child(1) { animation-delay: 0.1s; }
    .ball-result:nth-child(2) { animation-delay: 0.2s; }
    .ball-result:nth-child(3) { animation-delay: 0.3s; }
    .ball-result:nth-child(4) { animation-delay: 0.4s; }
    .ball-result:nth-child(5) { animation-delay: 0.5s; }
    .ball-result:nth-child(6) { animation-delay: 0.6s; }
    
    @keyframes ballBounce {
        0% { transform: scale(0.3); opacity: 0; }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); opacity: 1; }
    }
    
    /* Ball result colors based on runs */
    .ball-result[data-runs="0"] .w-8 { background: #f3f4f6; color: #6b7280; }
    .ball-result[data-runs="1"] .w-8 { background: #dbeafe; color: #1d4ed8; }
    .ball-result[data-runs="2"] .w-8 { background: #dbeafe; color: #1d4ed8; }
    .ball-result[data-runs="3"] .w-8 { background: #dbeafe; color: #1d4ed8; }
    .ball-result[data-runs="4"] .w-8 { background: #10b981; color: white; }
    .ball-result[data-runs="6"] .w-8 { background: #ef4444; color: white; }
    .ball-result[data-runs="w"] .w-8 { background: #ef4444; color: white; }
    .ball-result[data-runs="nb"] .w-8 { background: #f59e0b; color: white; }
    .ball-result[data-runs="lb"] .w-8 { background: #8b5cf6; color: white; }
    .ball-result[data-runs="b"] .w-8 { background: #8b5cf6; color: white; }
</style>

<div class="min-h-screen bg-gray-50 pt-4 pb-6">
    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6">
        <!-- Breadcrumb -->
        @include('cricket.partials.breadcrumb', [
            'items' => [
                ['url' => route('cricket.index'), 'label' => 'Home'],
                ['url' => '#', 'label' => 'Match Details']
            ]
        ])

        @php
            // Calculate match statistics from API data
            $homeTeam = $match['event_home_team'];
            $awayTeam = $match['event_away_team'];
            $homeScore = $match['event_home_final_result'] ?? '0/0';
            $awayScore = $match['event_away_final_result'] ?? '0/0';
            $homeOvers = null;
            $awayOvers = null;
            
            // Extract overs from scores if available
            if (preg_match('/(\d+)\/(\d+)/', $homeScore, $matches)) {
                $homeRuns = $matches[1];
                $homeWickets = $matches[2];
            }
            if (preg_match('/(\d+)\/(\d+)/', $awayScore, $matches)) {
                $awayRuns = $matches[1];
                $awayWickets = $matches[2];
            }
            
            // Calculate overs from extra data if available
            if (isset($match['extra'][$homeTeam . ' 1 INN'][0]['total_overs'])) {
                $homeOvers = $match['extra'][$homeTeam . ' 1 INN'][0]['total_overs'];
            }
            if (isset($match['extra'][$awayTeam . ' 1 INN'][0]['total_overs'])) {
                $awayOvers = $match['extra'][$awayTeam . ' 1 INN'][0]['total_overs'];
            }
            
            // Calculate required run rate and remaining balls
            $target = null;
            $remainingBalls = null;
            $requiredRR = null;
            
            if (isset($homeRuns) && isset($awayRuns) && $homeOvers) {
                $target = $homeRuns + 1;
                $remainingOvers = 50 - $awayOvers;
                $remainingBalls = $remainingOvers * 6;
                if ($remainingBalls > 0) {
                    $requiredRR = round(($target - $awayRuns) / $remainingOvers, 2);
                }
            }
            
            // Get current run rate
            $currentRR = $match['event_away_rr'] ?? 0;
            
            // Get scorecard data
            $homeScorecard = $match['scorecard'][$homeTeam . ' 1 INN'] ?? [];
            $awayScorecard = $match['scorecard'][$awayTeam . ' 1 INN'] ?? [];
            
            // Get live commentary
            $liveCommentary = $match['comments']['Live'] ?? [];
            $inningsCommentary = $match['comments'][$homeTeam . ' 1 INN'] ?? [];
            
            // Get wickets data
            $homeWickets = $match['wickets'][$homeTeam . ' 1 INN'] ?? [];
            $awayWickets = $match['wickets'][$awayTeam . ' 1 INN'] ?? [];
            
            // Get lineups
            $homeLineup = $match['lineups']['home_team']['starting_lineups'] ?? [];
            $awayLineup = $match['lineups']['away_team']['starting_lineups'] ?? [];
            
            // Get team keys for H2H API calls
            $homeTeamKey = $match['home_team_key'] ?? null;
            $awayTeamKey = $match['away_team_key'] ?? null;
        @endphp

        <!-- Live Match Header -->
            @if(isset($match['event_live']) && $match['event_live'] == '1')
        <div class="live-match-header">
            <div class="live-score-display">
                <div class="team-score">
                    <h3>{{ $awayTeam }}</h3>
                    <div class="score">{{ $awayScore }}</div>
                    <div class="overs">
                        @if($awayOvers)
                            ({{ $awayOvers }} overs)
                            @if($awayOvers <= 10)<span class="power-play">PP</span>@endif
                        @else
                            (In Progress)
                        @endif
                </div>
                </div>
                
                <div class="match-status">
                    <div class="status-text">
                        @if(isset($homeRuns) && isset($awayRuns))
                            {{ $awayTeam }} need {{ $target - $awayRuns }} runs
                        @else
                            Match in Progress
                @endif
            </div>
                    <div class="status-info">{{ $match['event_status_info'] ?? 'Live match' }}</div>
                </div>
                
                <div class="team-score">
                    <h3>{{ $homeTeam }}</h3>
                    <div class="score">{{ $homeScore }}</div>
                    <div class="overs">
                        @if($homeOvers)
                            ({{ $homeOvers }} overs)
                    @else
                            (Completed)
                    @endif
                </div>
                </div>
            </div>
            
            <div class="run-rates">
                <div class="rates">CRR: {{ $currentRR }} RRR: {{ $requiredRR ?? 'N/A' }}</div>
                @if($remainingBalls)
                <div class="target-info">{{ $awayTeam }} need {{ $target - $awayRuns }} runs in {{ $remainingBalls }} balls</div>
                @endif
                </div>
            </div>
            @endif

        <!-- Tab Navigation -->
        <div class="bg-white rounded-lg shadow-md border border-gray-200 mb-6">
            <div class="border-b border-gray-200">
                <nav class="flex space-x-8 px-6" aria-label="Tabs">
                    <button class="tab-button" data-tab="match-info">
                        📊 Match Info
                    </button>
                    <button class="tab-button active" data-tab="live">
                        🔴 Live
                    </button>
                    <button class="tab-button" data-tab="scorecard">
                        📈 Scorecard
                    </button>
                </nav>
                            </div>
                    </div>

        <!-- Tab Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content Area -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-lg border border-gray-200 mb-6">
                    <!-- Match Info Tab -->
                    <div id="match-info-content" class="tab-content p-4">
                        <div class="space-y-4">
                            <!-- Match Overview -->
                            <div class="match-info-card">
                                <div class="match-info-header">Match Overview</div>
                                <div class="match-info-content">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                        <div>
                                            <span class="text-gray-500">Series:</span>
                                            <span class="font-medium ml-2">{{ $match['league_name'] ?? 'N/A' }}</span>
                    </div>
                                        <div>
                                            <span class="text-gray-500">Date:</span>
                                            <span class="font-medium ml-2">{{ \Carbon\Carbon::parse($match['event_date_start'])->format('l, d F, g:i A') }}</span>
                </div>
                                        <div>
                                            <span class="text-gray-500">Venue:</span>
                                            <span class="font-medium ml-2">{{ $match['event_stadium'] ?? 'TBD' }}</span>
            </div>
                                        @if(isset($match['event_toss']))
                                        <div>
                                            <span class="text-gray-500">Toss:</span>
                                            <span class="font-medium ml-2">{{ $match['event_toss'] }}</span>
                                </div>
                            @endif
                        </div>
                            </div>
                    </div>

                            <!-- Debug Information (Remove in production) -->
                            @if(config('app.debug'))
                            <div class="match-info-card">
                                <div class="match-info-header">Debug Info</div>
                                <div class="match-info-content">
                                    <div class="text-xs text-gray-600 space-y-1">
                                        <div><strong>Scorecard Keys:</strong> 
                                            @if(isset($match['scorecard']))
                                                {{ implode(', ', array_keys($match['scorecard'])) }}
                                @else
                                                No scorecard data
                            @endif
                                </div>
                                        <div><strong>Lineups Keys:</strong> 
                                            @if(isset($match['lineups']))
                                                {{ implode(', ', array_keys($match['lineups'])) }}
                                            @else
                                                No lineups data
                                @endif
                        </div>
                                        <div><strong>Comments Keys:</strong> 
                                            @if(isset($match['comments']))
                                                {{ implode(', ', array_keys($match['comments'])) }}
                            @else
                                                No comments data
                                            @endif
                                </div>
                                        <div><strong>Extra Keys:</strong> 
                                            @if(isset($match['extra']))
                                                {{ implode(', ', array_keys($match['extra'])) }}
                                            @else
                                                No extra data
                            @endif
                        </div>
                                        <div><strong>Wickets Keys:</strong> 
                                            @if(isset($match['wickets']))
                                                {{ implode(', ', array_keys($match['wickets'])) }}
                                            @else
                                                No wickets data
                        @endif
                            </div>
                    </div>
                </div>
                            </div>
                            @endif

                            <!-- Team Form (Last 5 matches) -->
                            <div class="match-info-card">
                                <div class="match-info-header">Team Form (Last 5 matches)</div>
                                <div class="match-info-content">
                                    @php
                                        // Check if we have team keys for form data
                                        $canShowForm = $homeTeamKey && $awayTeamKey;
                                    @endphp
                                    
                                    @if($canShowForm)
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="text-center">
                                            <div class="text-sm font-semibold text-gray-700 mb-2">{{ $homeTeam }}</div>
                                            <div class="text-xs text-gray-500 mb-2">Form from H2H API</div>
                                            <div class="team-form justify-center">
                                                <div class="text-xs text-gray-500">firstTeamResults</div>
                            </div>
                            </div>
                                        <div class="text-center">
                                            <div class="text-sm font-semibold text-gray-700 mb-2">{{ $awayTeam }}</div>
                                            <div class="text-xs text-gray-500 mb-2">Form from H2H API</div>
                                            <div class="team-form justify-center">
                                                <div class="text-xs text-gray-500">secondTeamResults</div>
                </div>
            </div>
        </div>

                                    <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                        <div class="text-sm text-blue-800">
                                            <strong>Team Form Data Available:</strong>
                                            <div class="text-xs mt-1">
                                                • Recent match results (W/L/D)<br>
                                                • Match scores and venues<br>
                                                • Performance trends<br>
                                                • League and season info
                </div>
            </div>
                                    </div>
                                    @else
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="text-center">
                                            <div class="text-sm font-semibold text-gray-700 mb-2">{{ $homeTeam }}</div>
                                            <div class="text-xs text-gray-500 mb-2">Form data not available</div>
                                            <div class="team-form justify-center">
                                                <div class="text-gray-400">-</div>
                                </div>
                                </div>
                                        <div class="text-center">
                                            <div class="text-sm font-semibold text-gray-700 mb-2">{{ $awayTeam }}</div>
                                            <div class="text-xs text-gray-500 mb-2">Form data not available</div>
                                            <div class="team-form justify-center">
                                                <div class="text-gray-400">-</div>
                                </div>
                                </div>
                                    </div>
                            
                                    <div class="mt-4 text-center text-gray-500 text-sm">
                                        <p>Team form requires team keys for H2H API call.</p>
                                        <div class="mt-2 p-2 bg-gray-50 rounded text-xs">
                                            <strong>Missing:</strong> home_team_key and/or away_team_key<br>
                                            <strong>API:</strong> apiv2.api-cricket.com/cricket/?method=get_H2H
                                </div>
                                </div>
                                @endif
                            </div>
                        </div>

                            <!-- Head to Head -->
                            <div class="match-info-card">
                                <div class="match-info-header">Head to Head</div>
                                <div class="match-info-content">
                                    @php
                                        // Try to get H2H data from API if team keys are available
                                        $h2hData = null;
                                        $firstTeamResults = null;
                                        $secondTeamResults = null;
                                        
                                        if ($homeTeamKey && $awayTeamKey) {
                                            try {
                                                // This would be a real API call in production
                                                // For now, we'll show the structure and prepare for real data
                                                $h2hData = [
                                                    'homeTeamKey' => $homeTeamKey,
                                                    'awayTeamKey' => $awayTeamKey,
                                                    'note' => 'H2H API endpoint: get_H2H with team keys'
                                                ];
                                            } catch (Exception $e) {
                                                $h2hData = null;
                                            }
                                        }
                                    @endphp
                                    
                                    @if($h2hData)
                                    <div class="text-center mb-3">
                                        <span class="text-lg font-bold text-gray-900">{{ $homeTeam }} vs {{ $awayTeam }}</span>
                                        <div class="text-sm text-gray-500 mt-1">Team Keys: {{ $homeTeamKey }} vs {{ $awayTeamKey }}</div>
                                    </div>
                                
                                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                                        <div class="text-sm text-blue-800">
                                            <strong>API Endpoint:</strong> get_H2H<br>
                                            <strong>Parameters:</strong> first_team_key={{ $homeTeamKey }}, second_team_key={{ $awayTeamKey }}
                                    </div>
                                    </div>
                                    
                                    <div class="text-center text-gray-600 text-sm">
                                        <p>H2H data would be fetched from: <code>apiv2.api-cricket.com/cricket/?method=get_H2H</code></p>
                                        <p class="mt-2">This would show:</p>
                                        <ul class="text-left mt-2 space-y-1">
                                            <li>• Last matches between {{ $homeTeam }} and {{ $awayTeam }}</li>
                                            <li>• Recent results for {{ $homeTeam }}</li>
                                            <li>• Recent results for {{ $awayTeam }}</li>
                                        </ul>
                        </div>
                        @else
                                    <div class="text-center mb-3">
                                        <span class="text-lg font-bold text-gray-900">Head to head data not available</span>
                            </div>
                                    <div class="text-center text-gray-500 text-sm">
                                        <p>Team keys not found in match data for H2H API call.</p>
                                        <div class="mt-2 p-2 bg-gray-50 rounded text-xs">
                                            <strong>Required:</strong> home_team_key and away_team_key from match data<br>
                                            <strong>API:</strong> apiv2.api-cricket.com/cricket/?method=get_H2H
                            </div>
                        </div>
                        @endif
                                    </div>
                                    </div>

                            <!-- Team Comparison -->
                            <div class="match-info-card">
                                <div class="match-info-header">Team Comparison</div>
                                <div class="match-info-content">
                                    @php
                                        // Check if we have team keys for comparison data
                                        $canCompare = $homeTeamKey && $awayTeamKey;
                                    @endphp
                                    
                                    @if($canCompare)
                                    <div class="text-center mb-3">
                                        <span class="text-lg font-bold text-gray-900">Team Comparison Data</span>
                                        <div class="text-sm text-gray-500 mt-1">Based on H2H API data</div>
                            </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                                        <div class="bg-gray-50 rounded p-3">
                                            <div class="text-xs text-gray-500 mb-1">H2H Matches</div>
                                            <div class="text-sm font-semibold text-gray-900">From API</div>
                                            <div class="text-xs text-gray-500">get_H2H result</div>
                        </div>
                                        <div class="bg-gray-50 rounded p-3">
                                            <div class="text-xs text-gray-500 mb-1">{{ $homeTeam }} Form</div>
                                            <div class="text-sm font-semibold text-gray-900">From API</div>
                                            <div class="text-xs text-gray-500">firstTeamResults</div>
                                </div>
                                        <div class="bg-gray-50 rounded p-3">
                                            <div class="text-xs text-gray-500 mb-1">{{ $awayTeam }} Form</div>
                                            <div class="text-sm font-semibold text-gray-900">From API</div>
                                            <div class="text-xs text-gray-500">secondTeamResults</div>
                                </div>
                            </div>
                                    
                                    <div class="mt-4 p-3 bg-green-50 border border-green-200 rounded-lg">
                                        <div class="text-sm text-green-800">
                                            <strong>Available Data from H2H API:</strong>
                                            <ul class="mt-2 space-y-1 text-xs">
                                                <li>• Match results and scores</li>
                                                <li>• Win/loss records</li>
                                                <li>• Recent form (last 5-10 matches)</li>
                                                <li>• Head-to-head statistics</li>
                                                <li>• Match dates and venues</li>
                                            </ul>
                    </div>
                    </div>
                                        @else
                                    <div class="text-center text-gray-500 text-sm">
                                        <p>Team comparison data requires team keys for H2H API call.</p>
                                        <div class="mt-2 p-2 bg-gray-50 rounded text-xs">
                                            <strong>Missing:</strong> home_team_key and/or away_team_key<br>
                                            <strong>API:</strong> apiv2.api-cricket.com/cricket/?method=get_H2H
                                    </div>
                                    </div>
                                    @endif
                            </div>
                        </div>
                        
                            <!-- Current Match Target -->
                            <div class="match-info-card">
                                <div class="match-info-header">Current Match Target</div>
                                <div class="match-info-content">
                                    @php
                                        $targetRuns = 0;
                                        $currentRuns = 0;
                                        $remainingOvers = 0;
                                        $requiredRR = 0;
                                        
                                        if (isset($homeRuns) && isset($awayRuns)) {
                                            $targetRuns = $homeRuns + 1;
                                            $currentRuns = $awayRuns ?? 0;
                                            $remainingOvers = 50 - ($awayOvers ?? 0);
                                            if ($remainingOvers > 0) {
                                                $requiredRR = round(($targetRuns - $currentRuns) / $remainingOvers, 2);
                                            }
                                        }
                                    @endphp
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                                        <div class="bg-blue-50 rounded-lg p-4">
                                            <div class="text-sm text-gray-600 mb-2">Target</div>
                                            <div class="text-2xl font-bold text-blue-900">{{ $targetRuns }}</div>
                                    </div>
                                        <div class="bg-green-50 rounded-lg p-4">
                                            <div class="text-sm text-gray-600 mb-2">Current</div>
                                            <div class="text-2xl font-bold text-green-900">{{ $currentRuns }}</div>
                                    </div>
                                        <div class="bg-red-50 rounded-lg p-4">
                                            <div class="text-sm text-gray-600 mb-2">Need</div>
                                            <div class="text-2xl font-bold text-red-900">{{ $targetRuns - $currentRuns }}</div>
                                    </div>
                                </div>
                                    
                                    @if($remainingOvers > 0)
                                    <div class="mt-4 text-center">
                                        <div class="text-sm text-gray-600 mb-2">Required Run Rate</div>
                                        <div class="text-xl font-bold text-purple-600">{{ $requiredRR }}</div>
                                        <div class="text-xs text-gray-500 mt-1">{{ $awayTeam }} need {{ $targetRuns - $currentRuns }} runs in {{ $remainingOvers }} overs</div>
                    </div>
                    @endif
                                </div>
                </div>

                            <!-- Venue Information -->
                            <div class="match-info-card">
                                <div class="match-info-header">{{ $match['event_stadium'] ?? 'Venue Information' }}</div>
                                <div class="match-info-content">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <div class="text-sm text-gray-600 mb-2">Venue details not available</div>
                                            <div class="text-xs text-gray-500">Weather and pitch information is not provided in the current API response.</div>
                            </div>
                                        <div class="text-center">
                                            <div class="text-4xl mb-2">🏟️</div>
                                            <div class="text-xs text-gray-600">
                                                Venue statistics not available
                                                        </div>
                                                    </div>
                                                    </div>
                                    
                                    @if(isset($match['event_stadium']))
                                    <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                                        <div class="text-sm font-semibold text-gray-700 mb-2">Match Venue</div>
                                        <div class="text-sm text-gray-600">{{ $match['event_stadium'] }}</div>
                                                </div>
                                    @endif
                            </div>
                        </div>
                        
                            <!-- Pace vs Spin -->
                            <div class="match-info-card">
                                <div class="match-info-header">Bowling Analysis</div>
                                <div class="match-info-content">
                                    <div class="text-center text-gray-500 text-sm">
                                        <p>Detailed bowling analysis data is not available in the current API response.</p>
                            </div>
                                            </div>
                                            </div>

                            <!-- Recent Matches on Venue -->
                            <div class="match-info-card">
                                <div class="match-info-header">Recent Matches</div>
                                <div class="match-info-content">
                                    <div class="text-center text-gray-500 text-sm">
                                        <p>Recent match data for this venue is not available in the current API response.</p>
                                        </div>
                                        </div>
                                    </div>

                            <!-- Umpires -->
                            <div class="match-info-card">
                                <div class="match-info-header">Match Officials</div>
                                <div class="match-info-content">
                                    <div class="text-center text-gray-500 text-sm">
                                        <p>Umpire and referee information is not available in the current API response.</p>
                                </div>
                            </div>
                                        </div>
                            </div>
                                    </div>

                    <!-- Live Tab -->
                    <div id="live-content" class="tab-content p-4 active">
                        <div class="space-y-4">
                            <!-- Current Match Status -->
                            @if(isset($match['event_live']) && $match['event_live'] == '1')
                            <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                                <!-- Current Batsmen and Bowler -->
                                <div class="grid grid-cols-3 gap-6 mb-6">
                                    <!-- Batsman 1 -->
                                    <div class="text-center">
                                        <div class="w-16 h-16 bg-gradient-to-br from-red-400 to-yellow-400 rounded-full flex items-center justify-center mx-auto mb-3 shadow-lg">
                                            <span class="text-white text-xl font-bold">
                                                @php
                                                    $currentBatsman1 = null;
                                                    $batsman1Name = 'Batsman 1';
                                                    $batsman1Runs = 0;
                                                    $batsman1Balls = 0;
                                                    
                                                    // Try to get current batsman from scorecard
                                                    if (isset($match['scorecard'][$awayTeam . ' 1 INN'])) {
                                                        foreach ($match['scorecard'][$awayTeam . ' 1 INN'] as $player) {
                                                            if (isset($player['status']) && $player['status'] == 'NOT OUT' && isset($player['player'])) {
                                                                $currentBatsman1 = $player;
                                                                $batsman1Name = $player['player'];
                                                                $batsman1Runs = $player['R'] ?? $player['runs'] ?? 0;
                                                                $batsman1Balls = $player['B'] ?? $player['balls'] ?? 0;
                                                                break;
                                                            }
                                                        }
                                                    }
                                                    
                                                    // If no batsman found in scorecard, try to get from lineups
                                                    if (!$currentBatsman1 && isset($match['lineups']['away_team']['starting_lineups'])) {
                                                        $lineup = $match['lineups']['away_team']['starting_lineups'];
                                                        if (!empty($lineup)) {
                                                            $batsman1Name = $lineup[0]['name'] ?? $lineup[0]['player'] ?? 'Batsman 1';
                                                        }
                                                    }
                                                @endphp
                                                {{ substr($batsman1Name, 0, 1) }}
                                            </span>
                                    </div>
                                        <div class="font-semibold text-gray-900 text-sm mb-1">{{ $batsman1Name }}</div>
                                        <div class="text-lg font-bold text-gray-900 mb-1">
                                            {{ $batsman1Runs }} ({{ $batsman1Balls }})
                                    </div>
                                        <div class="flex items-center justify-center">
                                            <span class="text-2xl">🏏</span>
                                    </div>
                                </div>

                                    <!-- Partnership -->
                                    <div class="text-center flex flex-col items-center justify-center">
                                        <div class="text-sm text-gray-500 mb-2">P'ship</div>
                                        <div class="text-lg font-bold text-blue-600">
                                            @php
                                                $partnershipRuns = 0;
                                                $partnershipBalls = 0;
                                                
                                                // Calculate partnership from current batsmen
                                                if ($currentBatsman1) {
                                                    $partnershipRuns = $batsman1Runs;
                                                    $partnershipBalls = $batsman1Balls;
                                                    
                                                    // Add second batsman if available
                                                    if (isset($currentBatsman2) && $currentBatsman2) {
                                                        $partnershipRuns += $batsman2Runs;
                                                        $partnershipBalls += $batsman2Balls;
                                                    }
                                                }
                                                
                                                // Try to get from extra data if available
                                                if (isset($match['extra'][$awayTeam . ' 1 INN'][0]['partnership_runs'])) {
                                                    $partnershipRuns = $match['extra'][$awayTeam . ' 1 INN'][0]['partnership_runs'];
                                                }
                                                if (isset($match['extra'][$awayTeam . ' 1 INN'][0]['partnership_balls'])) {
                                                    $partnershipBalls = $match['extra'][$awayTeam . ' 1 INN'][0]['partnership_balls'];
                                                }
                                                
                                                // If still 0, try to calculate from current score
                                                if ($partnershipRuns == 0 && isset($awayRuns)) {
                                                    $partnershipRuns = $awayRuns;
                                                }
                                            @endphp
                                            {{ $partnershipRuns }} ({{ $partnershipBalls }})
                                </div>
                                        <div class="text-2xl text-blue-500">+</div>
                                </div>

                                    <!-- Batsman 2 -->
                                <div class="text-center">
                                        <div class="w-16 h-16 bg-gradient-to-br from-red-400 to-yellow-400 rounded-full flex items-center justify-center mx-auto mb-3 shadow-lg">
                                            <span class="text-white text-xl font-bold">
                                                @php
                                                    $currentBatsman2 = null;
                                                    $batsman2Name = 'Batsman 2';
                                                    $batsman2Runs = 0;
                                                    $batsman2Balls = 0;
                                                    
                                                    // Try to get second batsman from scorecard
                                                    if (isset($match['scorecard'][$awayTeam . ' 1 INN'])) {
                                                        $foundFirst = false;
                                                        foreach ($match['scorecard'][$awayTeam . ' 1 INN'] as $player) {
                                                            if (isset($player['status']) && $player['status'] == 'NOT OUT' && isset($player['player'])) {
                                                                if (!$foundFirst) {
                                                                    $foundFirst = true;
                                                                    continue;
                                                                }
                                                                $currentBatsman2 = $player;
                                                                $batsman2Name = $player['player'];
                                                                $batsman2Runs = $player['R'] ?? $player['runs'] ?? 0;
                                                                $batsman2Balls = $player['B'] ?? $player['balls'] ?? 0;
                                                                break;
                                                            }
                                                        }
                                                    }
                                                    
                                                    // If no second batsman found, try to get from lineups
                                                    if (!$currentBatsman2 && isset($match['lineups']['away_team']['starting_lineups'])) {
                                                        $lineup = $match['lineups']['away_team']['starting_lineups'];
                                                        if (count($lineup) > 1) {
                                                            $batsman2Name = $lineup[1]['name'] ?? $lineup[1]['player'] ?? 'Batsman 2';
                                                        }
                                                    }
                                                @endphp
                                                {{ substr($batsman2Name, 0, 1) }}
                                            </span>
                                    </div>
                                        <div class="font-semibold text-gray-900 text-sm mb-1">{{ $batsman2Name }}</div>
                                        <div class="text-lg font-bold text-gray-900 mb-1">
                                            {{ $batsman2Runs }} ({{ $batsman2Balls }})
                                        </div>
                                        <div class="flex items-center justify-center">
                                            <span class="text-2xl">🏏</span>
                                </div>
                            </div>
                        </div>
                        
                                <!-- Bowler Section -->
                                <div class="border-t border-gray-200 pt-4">
                                    <div class="flex items-center justify-center space-x-4">
                                        <div class="text-2xl">⚾</div>
                                        <div class="text-center">
                                            <div class="w-16 h-16 bg-gradient-to-br from-blue-400 to-yellow-400 rounded-full flex items-center justify-center mx-auto mb-3 shadow-lg">
                                                <span class="text-white text-xl font-bold">
                                                    @php
                                                        $currentBowler = null;
                                                        $bowlerName = 'Bowler';
                                                        $bowlerWickets = 0;
                                                        $bowlerRuns = 0;
                                                        $bowlerOvers = 0;
                                                        
                                                        // Try to get current bowler from scorecard
                                                        if (isset($match['scorecard'][$homeTeam . ' 1 INN'])) {
                                                            foreach ($match['scorecard'][$homeTeam . ' 1 INN'] as $player) {
                                                                if (isset($player['type']) && $player['type'] == 'Bowler' && isset($player['player'])) {
                                                                    $currentBowler = $player;
                                                                    $bowlerName = $player['player'];
                                                                    $bowlerWickets = $player['W'] ?? $player['wickets'] ?? 0;
                                                                    $bowlerRuns = $player['R'] ?? $player['runs'] ?? 0;
                                                                    $bowlerOvers = $player['O'] ?? $player['overs'] ?? 0;
                                                                    break;
                                                                }
                                                            }
                                                        }
                                                        
                                                        // If no bowler found in scorecard, try to get from lineups
                                                        if (!$currentBowler && isset($match['lineups']['home_team']['starting_lineups'])) {
                                                            $lineup = $match['lineups']['home_team']['starting_lineups'];
                                                            if (!empty($lineup)) {
                                                                $bowlerName = $lineup[0]['name'] ?? $lineup[0]['player'] ?? 'Bowler';
                                                            }
                                                        }
                                                    @endphp
                                                    {{ substr($bowlerName, 0, 1) }}
                                                </span>
                            </div>
                                            <div class="font-semibold text-gray-900 text-sm mb-1">{{ $bowlerName }}</div>
                                            <div class="text-lg font-bold text-gray-900">
                                                {{ $bowlerWickets }}-{{ $bowlerRuns }} ({{ $bowlerOvers }})
                                                        </div>
                                                    </div>
                                                    </div>
                                                </div>

                                <!-- Current Match Status -->
                                <div class="mt-4 p-3 bg-green-50 border border-green-200 rounded-lg">
                                    <div class="text-center">
                                        <div class="text-sm text-green-600 mb-1">Current Status</div>
                                        <div class="text-lg font-bold text-green-800">
                                            @if(isset($match['event_status_info']))
                                                {{ $match['event_status_info'] }}
                                            @else
                                                {{ $awayTeam }} need {{ $targetRuns - $currentRuns }} runs
                                            @endif
                            </div>
                                        @if(isset($remainingOvers) && $remainingOvers > 0)
                                        <div class="text-sm text-green-600 mt-1">
                                            {{ $remainingOvers }} overs remaining
                                        </div>
                                    @endif
                                                            </div>
                                                    </div>

                                <!-- Partnership and Last Wicket -->
                                <div class="grid grid-cols-2 gap-4 mt-4 text-center">
                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <div class="text-sm text-gray-600 mb-1">P'ship</div>
                                        <div class="font-semibold text-gray-900">{{ $partnershipRuns }} ({{ $partnershipBalls }})</div>
                                                </div>
                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <div class="text-sm text-gray-600 mb-1">Last Wkt</div>
                                        @php
                                            $lastWicket = null;
                                            $lastWicketPlayer = 'Player';
                                            $lastWicketRuns = 0;
                                            $lastWicketBalls = 0;
                                            
                                            if (isset($match['wickets'][$awayTeam . ' 1 INN']) && !empty($match['wickets'][$awayTeam . ' 1 INN'])) {
                                                $lastWicket = end($match['wickets'][$awayTeam . ' 1 INN']);
                                                if (isset($lastWicket['player'])) {
                                                    $lastWicketPlayer = $lastWicket['player'];
                                                }
                                                if (isset($lastWicket['runs'])) {
                                                    $lastWicketRuns = $lastWicket['runs'];
                                                }
                                                if (isset($lastWicket['balls'])) {
                                                    $lastWicketBalls = $lastWicket['balls'];
                                                }
                                            }
                                            
                                            // If no wicket data, try to get from scorecard
                                            if ($lastWicketPlayer == 'Player' && isset($match['scorecard'][$awayTeam . ' 1 INN'])) {
                                                foreach ($match['scorecard'][$awayTeam . ' 1 INN'] as $player) {
                                                    if (isset($player['status']) && $player['status'] != 'NOT OUT' && isset($player['player'])) {
                                                        $lastWicketPlayer = $player['player'];
                                                        $lastWicketRuns = $player['R'] ?? $player['runs'] ?? 0;
                                                        $lastWicketBalls = $player['B'] ?? $player['balls'] ?? 0;
                                                        break;
                                                    }
                                                }
                                            }
                                        @endphp
                                        @if($lastWicket && $lastWicketPlayer != 'Player')
                                        <div class="font-semibold text-gray-900">
                                            {{ $lastWicketPlayer }} {{ $lastWicketRuns }}({{ $lastWicketBalls }})
                                        </div>
                                    @else
                                        <div class="font-semibold text-gray-900">-</div>
                                            @endif
                                </div>
                            </div>
                        </div>
                        @endif

                            <!-- Over-by-Over Commentary -->
                            <div class="match-info-card">
                                <div class="match-info-header">Recent Overs</div>
                                <div class="match-info-content">
                                    <div class="space-y-4">
                                        @php
                                            $recentOvers = [];
                                            if (isset($match['comments']['Live']) && !empty($match['comments']['Live'])) {
                                                $liveComments = $match['comments']['Live'];
                                                $overGroups = [];
                                                
                                                // Group comments by over
                                                foreach ($liveComments as $comment) {
                                                    $over = $comment['overs'] ?? 0;
                                                    if (!isset($overGroups[$over])) {
                                                        $overGroups[$over] = [];
                                                    }
                                                    $overGroups[$over][] = $comment;
                                                }
                                                
                                                // Get last 3 overs
                                                $overNumbers = array_keys($overGroups);
                                                rsort($overNumbers);
                                                $last3Overs = array_slice($overNumbers, 0, 3);
                                                
                                                foreach ($last3Overs as $overNum) {
                                                    $overBalls = $overGroups[$overNum];
                                                    $recentOvers[] = [
                                                        'over' => $overNum,
                                                        'balls' => $overBalls
                                                    ];
                                                }
                                            }
                                        @endphp

                                        @if(!empty($recentOvers))
                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                                @foreach($recentOvers as $overData)
                                                <div class="border border-gray-200 rounded-lg p-4">
                                                    <div class="flex items-center justify-between mb-3">
                                                        <h4 class="font-semibold text-gray-900">Over {{ $overData['over'] }}</h4>
                                                        <div class="text-sm text-gray-500">
                                                            @php
                                                                $overTotal = 0;
                                                                foreach ($overData['balls'] as $ball) {
                                                                    $runs = $ball['runs'] ?? 0;
                                                                    if (is_numeric($runs)) {
                                                                        $overTotal += $runs;
                                                                    } elseif ($runs === 'w') {
                                                                        // Wicket - no runs
                                                                        $overTotal += 0;
                                                                    } elseif (in_array($runs, ['nb', 'lb', 'b'])) {
                                                                        // Extras - usually 1 run
                                                                        $overTotal += 1;
                                                                    }
                                                                }
                                                            @endphp
                                                            Total: {{ $overTotal }}
                    </div>
                </div>
                                                    <div class="flex flex-wrap gap-2 justify-center">
                                                        @foreach($overData['balls'] as $ball)
                                                        <div class="ball-result" data-runs="{{ $ball['runs'] ?? 0 }}">
                                                            <div class="w-8 h-8 rounded-full border-2 border-gray-300 bg-white flex items-center justify-center text-sm font-semibold text-gray-700 transition-all duration-300 hover:scale-110">
                                                                @php
                                                                    $ballRuns = $ball['runs'] ?? 0;
                                                                    $ballDisplay = $ballRuns;
                                                                    
                                                                    // Handle special cases
                                                                    if ($ballRuns === 'w') $ballDisplay = 'W';
                                                                    elseif ($ballRuns === 'nb') $ballDisplay = 'NB';
                                                                    elseif ($ballRuns === 'lb') $ballDisplay = 'LB';
                                                                    elseif ($ballRuns === 'b') $ballDisplay = 'B';
                                                                @endphp
                                                                {{ $ballDisplay }}
                                    </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                        @endforeach
                    </div>
                    @else
                                            <div class="text-center py-8 text-gray-500">
                                                <div class="text-4xl mb-2">🏏</div>
                                                <p>Over-by-over data not available</p>
                                                <div class="text-xs mt-2">
                                                    @if(isset($match['comments']['Live']))
                                                        Live comments: {{ count($match['comments']['Live']) }} entries
                                                    @else
                                                        No live comments found
                                            @endif
                </div>
                                        </div>
                                    @endif
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Live Commentary Section -->
                            <div class="match-info-card">
                                <div class="match-info-header">Live Commentary</div>
                                <div class="match-info-content">
                                    <!-- Commentary Tabs -->
                                    <div class="commentary-tabs">
                                        <button class="commentary-tab active">All</button>
                                        <button class="commentary-tab">Highlights</button>
                                        <button class="commentary-tab">Overs</button>
                                        <button class="commentary-tab">W</button>
                                        <button class="commentary-tab">6s</button>
                                        <button class="commentary-tab">4s</button>
                                    </div>

                                    <!-- Commentary Content -->
                                    <div class="space-y-2 max-h-96 overflow-y-auto scroll-container">
                                        @if(!empty($liveCommentary))
                                            @foreach(array_reverse($liveCommentary) as $comment)
                                            <div class="commentary-entry">
                                                <div class="ball-indicator ball-{{ $comment['runs'] ?? 0 }}">
                                                    {{ $comment['runs'] ?? '0' }}
                                    </div>
                                                <div class="flex-1">
                                                    <div class="text-sm text-gray-900">
                                                        {{ $comment['overs'] ?? 'N/A' }}.{{ $comment['balls'] ?? '0' }} - 
                                                        @if($comment['runs'] == 0)
                                                            Dot ball
                                                        @elseif($comment['runs'] == 4)
                                                            FOUR!
                                                        @elseif($comment['runs'] == 6)
                                                            SIX!
                                                        @elseif($comment['runs'] == 'w')
                                                            WICKET!
                                                        @else
                                                            {{ $comment['runs'] }} runs
                                                        @endif
                                </div>
                                                    <div class="text-xs text-gray-500">
                                                        Over {{ $comment['overs'] ?? 'N/A' }}
                        </div>
                        </div>
                                            </div>
                                            @endforeach
                    @else
                                            <div class="text-center py-8 text-gray-500">
                                                <div class="text-4xl mb-2">🏏</div>
                                                <p>Live commentary not available</p>
                        </div>
                    @endif
                </div>
                                    </div>
                                    </div>
                                </div>
                            </div>

                    <!-- Scorecard Tab -->
                    <div id="scorecard-content" class="tab-content p-4">
                        <div class="space-y-4">
                            <!-- Team Scores Overview -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                    <h3 class="font-semibold text-blue-900 mb-2">{{ $homeTeam }}</h3>
                                    <div class="text-2xl font-bold text-blue-900">{{ $homeScore }}</div>
                                    @if($homeOvers)
                                    <div class="text-sm text-blue-600">({{ $homeOvers }} overs)</div>
                                                    @endif
                                                </div>
                                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                    <h3 class="font-semibold text-green-900 mb-2">{{ $awayTeam }}</h3>
                                    <div class="text-2xl font-bold text-green-900">{{ $awayScore }}</div>
                                    @if($awayOvers)
                                    <div class="text-sm text-green-600">({{ $awayOvers }} overs)</div>
                                            @endif
                                </div>
                </div>

                            <!-- Batting Scorecard -->
                            <div class="match-info-card">
                                <div class="match-info-header">Batting - {{ $homeTeam }}</div>
                                <div class="match-info-content">
                                    @if(!empty($homeScorecard))
                                    <table class="scorecard-table">
                                        <thead>
                                            <tr>
                                                <th>Batter</th>
                                                <th>R</th>
                                                <th>B</th>
                                                <th>4s</th>
                                                <th>6s</th>
                                                <th>SR</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($homeScorecard as $player)
                                            <tr>
                                                <td class="text-gray-900">
                                                    {{ $player['player'] ?? 'Player' }}
                                                    @if(isset($player['status']) && $player['status'] != 'NOT OUT')
                                                        <div class="text-xs text-gray-500">{{ $player['status'] }}</div>
                                                    @endif
                                                </td>
                                                <td>{{ $player['R'] ?? $player['runs'] ?? '0' }}</td>
                                                <td>{{ $player['B'] ?? $player['balls'] ?? '0' }}</td>
                                                <td>{{ $player['4s'] ?? $player['fours'] ?? '0' }}</td>
                                                <td>{{ $player['6s'] ?? $player['sixes'] ?? '0' }}</td>
                                                <td>{{ $player['SR'] ?? $player['strike_rate'] ?? '0.00' }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    @else
                                    <div class="text-center py-4 text-gray-500">
                                        <p>Batting data not available</p>
                                 </div>
                                                    @endif
                                </div>
                                                </div>
                                 
                            <!-- Bowling Scorecard -->
                            <div class="match-info-card">
                                <div class="match-info-header">Bowling - {{ $awayTeam }}</div>
                                <div class="match-info-content">
                                    @if(!empty($awayScorecard))
                                    <table class="scorecard-table">
                                        <thead>
                                            <tr>
                                                <th>Bowler</th>
                                                <th>O</th>
                                                <th>M</th>
                                                <th>R</th>
                                                <th>W</th>
                                                <th>ER</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($awayScorecard as $player)
                                            @if(isset($player['type']) && $player['type'] == 'Bowler')
                                            <tr>
                                                <td class="text-gray-900">{{ $player['player'] ?? 'Player' }}</td>
                                                <td>{{ $player['O'] ?? $player['overs'] ?? '0' }}</td>
                                                <td>{{ $player['M'] ?? $player['maidens'] ?? '0' }}</td>
                                                <td>{{ $player['R'] ?? $player['runs'] ?? '0' }}</td>
                                                <td>{{ $player['W'] ?? $player['wickets'] ?? '0' }}</td>
                                                <td>{{ $player['ER'] ?? $player['economy'] ?? '0.00' }}</td>
                                            </tr>
                                            @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                    @else
                                    <div class="text-center py-4 text-gray-500">
                                        <p>Bowling data not available</p>
                                                </div>
                                            @endif
                                        </div>
                                 </div>
                                    </div>
                                </div>
                        </div>
                             </div>

            <!-- Right Sidebar -->
            <div class="lg:col-span-1">
                <!-- Toss Information -->
                @if(isset($match['event_toss']))
                <div class="toss-info">
                    <div class="toss-icon">🎯</div>
                    <div class="toss-text">{{ $match['event_toss'] }}</div>
                        </div>
                    @endif

                <!-- Player Lineups -->
                <div class="player-lineup">
                    <div class="lineup-header">{{ $homeTeam }} Playing XI</div>
                    <div class="lineup-content">
                        @if(!empty($homeLineup))
                            @foreach($homeLineup as $player)
                            <div class="player-item">
                                <div class="player-avatar">
                                    @php
                                        $playerName = $player['name'] ?? $player['player'] ?? 'P';
                                    @endphp
                                    {{ substr($playerName, 0, 1) }}
                                                </div>
                                <div class="player-info">
                                    <div class="player-name">{{ $playerName }}</div>
                                    <div class="player-role">{{ $player['position'] ?? $player['role'] ?? 'Player' }}</div>
                                     </div>
                                     </div>
                            @endforeach
                        @else
                            <div class="text-center py-4 text-gray-500">
                                <p>Lineup data not available</p>
                                     </div>
                                                    @endif
                                                </div>
                                            </div>

                <div class="player-lineup">
                    <div class="lineup-header">{{ $awayTeam }} Playing XI</div>
                    <div class="lineup-content">
                        @if(!empty($awayLineup))
                            @foreach($awayLineup as $player)
                            <div class="player-item">
                                <div class="player-avatar">
                                    @php
                                        $playerName = $player['name'] ?? $player['player'] ?? 'P';
                                    @endphp
                                    {{ substr($playerName, 0, 1) }}
                                    </div>
                                <div class="player-info">
                                    <div class="player-name">{{ $playerName }}</div>
                                    <div class="player-role">{{ $player['position'] ?? $player['role'] ?? 'Player' }}</div>
                                </div>
                        </div>
                                    @endforeach
                    @else
                            <div class="text-center py-4 text-gray-500">
                                <p>Lineup data not available</p>
                            </div>
                    @endif
                                </div>
                            </div>

                <!-- Current Partnership -->
                <div class="match-info-card">
                    <div class="match-info-header">Current Partnership</div>
                    <div class="match-info-content">
                        <div class="text-center">
                            <div class="text-lg font-bold text-gray-900">5 runs</div>
                            <div class="text-sm text-gray-600">from 12 balls</div>
                            <div class="partnership-bar mt-2">
                                <div class="partnership-fill" style="width: 42%"></div>
                                                        </div>
                                                    </div>
                                                    </div>
                                            </div>

                <!-- Recent Overs -->
                <div class="match-info-card">
                    <div class="match-info-header">Recent Overs</div>
                    <div class="match-info-content">
                        <div class="flex justify-center space-x-2">
                            <span class="px-2 py-1 bg-gray-100 rounded text-sm">1 1 0 1 W 0</span>
                            <span class="px-2 py-1 bg-gray-100 rounded text-sm">4 0 1 1 0 1</span>
                            <span class="px-2 py-1 bg-gray-100 rounded text-sm">0 1 0 0 1 0</span>
                                    </div>
                                </div>
                                            </div>
                                    </div>
                                </div>
            </div>
        </div>

<script>
    // Page Loader - Hide when content is fully loaded
    document.addEventListener('DOMContentLoaded', function() {
        // Hide page loader after a short delay to ensure content is rendered
        setTimeout(() => {
            const pageLoader = document.getElementById('pageLoader');
            if (pageLoader) {
                pageLoader.style.opacity = '0';
                pageLoader.style.transition = 'opacity 0.5s ease-out';
                setTimeout(() => {
                    pageLoader.style.display = 'none';
                }, 500);
            }
        }, 1000); // Wait 1 second for content to load
    });
    
    // Also hide page loader when window is fully loaded
    window.addEventListener('load', function() {
        const pageLoader = document.getElementById('pageLoader');
        if (pageLoader) {
            pageLoader.style.opacity = '0';
            pageLoader.style.transition = 'opacity 0.5s ease-out';
            setTimeout(() => {
                pageLoader.style.display = 'none';
            }, 500);
        }
    });

    // Tab functionality
document.addEventListener('DOMContentLoaded', function() {
            const tabButtons = document.querySelectorAll('.tab-button');
            const tabContents = document.querySelectorAll('.tab-content');
            
        // Tab switching
            tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                const targetTab = this.dataset.tab;
                    
                    // Remove active class from all buttons and contents
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    tabContents.forEach(content => content.classList.remove('active'));
                    
                    // Add active class to clicked button and corresponding content
                this.classList.add('active');
                document.getElementById(targetTab + '-content').classList.add('active');
        });
    });

        // Ensure Live tab is active by default
        const liveTab = document.querySelector('[data-tab="live"]');
        if (liveTab) {
            liveTab.click();
        }
    });

    // Team lineup switching
    function showTeam(team) {
        const homeLineup = document.getElementById('home-team-lineup');
        const awayLineup = document.getElementById('away-team-lineup');
        const buttons = document.querySelectorAll('.player-lineup button');
        
        if (team === 'home') {
            homeLineup.classList.remove('hidden');
            awayLineup.classList.add('hidden');
            buttons[0].classList.remove('bg-gray-100', 'text-gray-600');
            buttons[0].classList.add('bg-blue-100', 'text-blue-700');
            buttons[1].classList.remove('bg-blue-100', 'text-blue-700');
            buttons[1].classList.add('bg-gray-100', 'text-gray-600');
        } else {
            homeLineup.classList.add('hidden');
            awayLineup.classList.remove('hidden');
            buttons[0].classList.remove('bg-blue-100', 'text-blue-700');
            buttons[0].classList.add('bg-gray-100', 'text-gray-600');
            buttons[1].classList.remove('bg-gray-100', 'text-gray-600');
            buttons[1].classList.add('bg-blue-100', 'text-blue-700');
        }
    }

    // Ball result animations
    function animateBallResults() {
        const ballResults = document.querySelectorAll('.ball-result');
        ballResults.forEach((ball, index) => {
            ball.style.animationDelay = `${index * 0.1}s`;
        });
    }

    // Initialize animations when page loads
    document.addEventListener('DOMContentLoaded', function() {
        animateBallResults();
        
        // Add hover effects to ball results
        const ballResults = document.querySelectorAll('.ball-result');
        ballResults.forEach(ball => {
            ball.addEventListener('mouseenter', function() {
                this.style.transform = 'scale(1.1)';
                this.style.boxShadow = '0 4px 8px rgba(0, 0, 0, 0.2)';
            });
            
            ball.addEventListener('mouseleave', function() {
                this.style.transform = 'scale(1)';
                this.style.boxShadow = 'none';
            });
        });
});
</script>
@endsection