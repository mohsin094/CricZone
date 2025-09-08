@extends('layouts.app')

@section('title', 'CricZone.pk - Live Cricket Scores & Updates')
@section('description', 'Get live cricket scores, match updates, fixtures, and results from around the world. Stay updated with real-time cricket information.')

@section('content')

<style>
@keyframes blink {
    0%, 50% { opacity: 1; }
    51%, 100% { opacity: 0.3; }
}

.blink-dot {
    animation: blink 1.5s infinite;
    display: inline-block;
    width: 8px;
    height: 8px;
    background-color: #ef4444;
        border-radius: 50%;
    margin-right: 4px;
}

/* Mobile-first responsive design */
@media (max-width: 1024px) {
    .mobile-tab-container {
        position: fixed;
        top: 64px; /* Below the top navbar */
        left: 0;
        right: 0;
        z-index: 40;
        background: white;
        border-bottom: 1px solid #e5e7eb;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        height: 48px; /* Reduced height */
    }
    
    /* Mobile content padding adjustments */
    @media (max-width: 1023px) {
        body {
            padding-top: 0; /* Let main handle padding */
            padding-bottom: 64px; /* Height of bottom navbar */
        }
        
        main {
            padding-top: 112px; /* 64px for top navbar + 48px for tabs */
            padding-bottom: 80px; /* Extra space for bottom navbar */
        }
    }
    
    /* Desktop padding */
    @media (min-width: 1024px) {
        body {
            padding-top: 0;
            padding-bottom: 0;
        }
    }
    
    /* Ensure content doesn't go behind fixed tabs */
    .mobile-content {
        margin-top: 0;
        padding-top: 0;
    }
    
    .mobile-tab-scroll {
        height: 48px; /* Reduced height for tab container */
    }
    
    .mobile-tab-scroll nav {
        height: 100%;
        display: grid;
        grid-template-columns: 1fr 1fr 1fr 1fr;
        width: 100%;
    }
    
    .mobile-match-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        margin-bottom: 12px;
        overflow: hidden;
        transition: all 0.2s ease;
    }
    
    .mobile-match-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        transform: translateY(-1px);
    }
    
    .series-header {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        padding: 8px 12px;
        border-bottom: 1px solid #e5e7eb;
        font-weight: 600;
        color: #374151;
        font-size: 12px;
        position: relative;
        transition: background-color 0.2s ease;
    }
    
    .series-header:hover {
        background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%);
    }
    
    .series-header::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: linear-gradient(90deg, #3b82f6, #1d4ed8);
    }
    
    .series-arrow {
        transition: transform 0.3s ease;
    }
    
    .series-matches {
        transition: all 0.3s ease;
    }
    
    .match-item {
        padding: 16px;
        border-bottom: 1px solid #f3f4f6;
    }
    
    .match-item:last-child {
        border-bottom: none;
    }
    
    .team-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 8px;
    }
    
    .team-row:last-child {
        margin-bottom: 0;
    }
    
    .team-info {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .team-flag {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        object-fit: cover;
    }
    
    .team-name {
        font-weight: 500;
        color: #111827;
        font-size: 14px;
    }
    
    .team-score {
        font-weight: 600;
        color: #111827;
        font-size: 14px;
    }
    
    .match-status {
        text-align: right;
        font-size: 12px;
        color: #6b7280;
    }
    
    .live-indicator {
        color: #ef4444;
        font-weight: 600;
    }
    
    .venue-info {
        font-size: 12px;
        color: #6b7280;
        margin-top: 4px;
    }
    
    /* Enhanced mobile card styling */
    .mobile-card-container {
        padding: 0 12px 16px 12px;
    }
    
    .mobile-card-container .space-y-3 > * + * {
        margin-top: 8px;
    }
    
    .mobile-card-container .space-y-2 > * + * {
        margin-top: 6px;
    }
    
    /* Mobile card hover effects */
    .mobile-match-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }
    
    /* Mobile tab improvements */
    .mobile-tab-container {
        backdrop-filter: blur(10px);
        background: rgba(255, 255, 255, 0.95);
    }
    
    /* Mobile content improvements */
    .mobile-content {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        min-height: calc(100vh - 112px);
        padding-top: 0;
    }
    
    /* Reduce space between navbar and first card */
    .mobile-card-container {
        padding-top: 0;
        padding-bottom: 4px;
    }
    
    /* Reduce padding for all mobile tabs */
    .mobile-content .px-4 {
        padding-left: 6px;
        padding-right: 6px;
    }
    
    .mobile-content .py-3 {
        padding-top: 1px;
        padding-bottom: 1px;
    }
    
    /* Remove extra spacing from tab headers */
    .mobile-content .px-4.py-3 {
        padding-top: 2px;
        padding-bottom: 2px;
    }
    
    /* Remove margin from first mobile card */
    .mobile-card-container > *:first-child {
        margin-top: 0;
    }
    
    /* Reduce spacing in mobile tabs */
    .mobile-content .space-y-1 > * + * {
        margin-top: 0.125rem;
    }
    
    /* Reduce padding in mobile match cards */
    .mobile-match-card {
        margin-bottom: 0.125rem;
    }
    
    /* Remove extra spacing from mobile content */
    .mobile-content {
        padding-top: 0;
    }
    
    /* Minimize spacing in mobile card containers */
    .mobile-card-container {
        margin-top: 0;
        padding-top: 0;
    }
    
    /* Live match special styling */
    .live-match-card {
        border-left: 4px solid #ef4444;
        background: linear-gradient(135deg, #fef2f2 0%, #ffffff 100%);
    }
    
    .live-match-card .series-header {
        background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
        color: #dc2626;
    }
    
    /* Finished match special styling */
    .finished-match-card {
        border-left: 4px solid #10b981;
    }
    
    /* Upcoming match special styling */
    .upcoming-match-card {
        border-left: 4px solid #3b82f6;
    }
    
    .mobile-tab-button {
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        white-space: nowrap;
        font-size: 12px;
        padding: 0 8px;
    }
    
    /* Mobile pagination improvements */
    .mobile-pagination {
        background: #f9fafb;
        border-top: 1px solid #e5e7eb;
    }
    
    .mobile-pagination .pagination-button {
        min-width: 44px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
    }
    
    .mobile-pagination .page-numbers {
        gap: 2px;
    }
    
    .mobile-pagination .page-number {
        min-width: 32px;
        height: 32px;
        font-size: 11px;
    }
}
</style>
    <!-- Page Loading Overlay - Shows until content is fully loaded -->
    <div id="pageLoader" class="fixed inset-0 bg-gradient-to-br from-green-50 to-blue-50 z-50 flex items-center justify-center">
    <div class="text-center">
        <div class="inline-flex flex-col items-center px-12 py-10 bg-white rounded-2xl shadow-2xl border border-gray-100">
            <!-- Logo-style loader -->
            <div class="relative mb-6">
                <div class="w-20 h-20 bg-gradient-to-br from-green-500 to-blue-600 rounded-full flex items-center justify-center shadow-lg">
                    <div class="text-white text-3xl font-bold">🏏</div>
                </div>
                <!-- Animated ring around logo -->
                <div class="absolute inset-0 w-20 h-20 border-4 border-transparent border-t-green-500 border-r-blue-600 rounded-full animate-spin"></div>
            </div>
            
            <!-- Site name with cricket theme -->
            <div class="mb-3">
                <div class="text-3xl font-bold bg-gradient-to-r from-green-600 to-blue-600 bg-clip-text text-transparent">
                    CricZone
                </div>
                <div class="text-sm text-gray-500 mt-1">Cricket Live Scores & Updates</div>
            </div>
            
            <!-- Loading text -->
            <div class="text-gray-600 text-lg font-medium">Loading live scores...</div>
            
            <!-- Animated dots -->
            <div class="flex space-x-1 mt-3">
                <div class="w-2 h-2 bg-green-500 rounded-full animate-bounce" style="animation-delay: 0ms;"></div>
                <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce" style="animation-delay: 150ms;"></div>
                <div class="w-2 h-2 bg-green-500 rounded-full animate-bounce" style="animation-delay: 300ms;"></div>
            </div>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-0 lg:px-6 pt-0 lg:pt-2">

    <!-- Mobile Navigation Tabs -->
    <div class="mobile-tab-container lg:hidden">
        <div class="mobile-tab-scroll">
            <nav aria-label="Tabs">
                <button id="tab-live" class="tab-button mobile-tab-button text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent hover:border-gray-300">
                    <span class="blink-dot"></span> Live ({{ count($liveMatches ?? []) }})
                </button>
                <button id="tab-for-you" class="tab-button mobile-tab-button active text-sm font-medium text-red-600 border-b-2 border-red-500">
                    For You
                </button>
                <button id="tab-upcoming" class="tab-button mobile-tab-button text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent hover:border-gray-300">
                    Upcoming
                </button>
                <button id="tab-finished" class="tab-button mobile-tab-button text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent hover:border-gray-300">
                    Finished
                </button>
            </nav>
        </div>
    </div>

    <!-- Desktop Navigation Tabs -->
    <div class="hidden lg:block mb-8">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button id="tab-all" class="tab-button active border-b-2 border-green-500 py-2 px-3 text-sm font-medium text-green-600">
                    All
                </button>
                <button id="tab-live" class="tab-button border-b-2 border-transparent py-2 px-3 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    <span class="blink-dot"></span> Live
                </button>
                <button id="tab-today" class="tab-button border-b-2 border-transparent py-2 px-3 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    📅 Today
                </button>
                <button id="tab-upcoming" class="tab-button border-b-2 border-transparent py-2 px-3 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    ⏰ Upcoming
                </button>
                <button id="tab-recent" class="tab-button border-b-2 border-transparent py-2 px-3 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    🏆 Recent Completed
                </button>
            </nav>
        </div>
    </div>

    <!-- Search Filter (Desktop only) -->
    <div class="hidden lg:block mb-6">
        <div class="bg-white rounded-lg shadow-md border border-gray-200 p-4">
            <div class="grid grid-cols-1 gap-4">
                <div class="relative">
                    <input type="text" id="searchInput" placeholder="🔍 Search teams, leagues, venues..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-sm">
                </div>
            </div>
            <div id="searchStatus" class="text-sm text-gray-600 mt-2" style="display: none;"></div>
        </div>
        
        <!-- Mock Data Controls -->
        <div class="mt-4">
            <div class="bg-yellow-50 rounded-lg border border-yellow-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-yellow-800">🔄 Mock Data Controls</h3>
                        <p class="text-xs text-yellow-700 mt-1">Enable/disable mock data for testing</p>
                    </div>
                    <div class="flex space-x-2">
                        <a href="{{ route('cricket.mock-enable') }}" 
                           class="inline-flex items-center px-3 py-2 border border-transparent text-xs font-medium rounded-md text-yellow-800 bg-yellow-100 hover:bg-yellow-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                            ✅ Enable Mock
                        </a>
                        <a href="{{ route('cricket.mock-disable') }}" 
                           class="inline-flex items-center px-3 py-2 border border-transparent text-xs font-medium rounded-md text-gray-800 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            ❌ Disable Mock
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
        
    <!-- Mobile Tab Content -->
    <div class="lg:hidden mobile-content" style="margin-top: 0; padding-top: 0;">
        <!-- For You Tab (Mobile) -->
        <div id="tab-content-for-you" class="tab-content active">
            <div class="px-4 py-3 bg-white border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-semibold text-gray-900">Matches for you</h2>
                    <button id="toggle-series-grouping" class="flex items-center space-x-1 text-xs text-gray-600 hover:text-gray-800">
                        <span id="grouping-text">Grouped</span>
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="mobile-card-container space-y-2" id="mobile-matches-container">
                @php
                    // Use the same sorting logic as desktop for consistency
                    $allMatches = collect();
                    
                    // Add live matches first (highest priority)
                    if (!empty($liveMatches)) {
                        $allMatches = $allMatches->merge($liveMatches);
                    }
                    
                    // Add today's matches (excluding those that are already live)
                    if (!empty($todayMatches)) {
                        $todayNonLiveMatches = collect($todayMatches)->filter(function($match) use ($liveMatches) {
                            $matchKey = $match['event_key'] ?? '';
                            return !collect($liveMatches)->contains('event_key', $matchKey);
                        });
                        $allMatches = $allMatches->merge($todayNonLiveMatches);
                    }
                    
                    // Add recent completed matches (most recent first)
                    if (!empty($recentCompletedMatches)) {
                        $sortedRecentMatches = collect($recentCompletedMatches)->sortByDesc(function($match) {
                            $date = $match['event_date'] ?? $match['event_date_start'] ?? $match['endDate'] ?? '';
                            if ($date) {
                                try {
                                    return \Carbon\Carbon::parse($date)->timestamp;
                                } catch (\Exception $e) {
                                    return 0;
                                }
                            }
                            return 0;
                        });
                        $allMatches = $allMatches->merge($sortedRecentMatches);
                    }
                    
                    // Add upcoming matches
                    if (!empty($upcomingMatches)) {
                        $nextWeekDate = now()->addDays(7)->format('Y-m-d');
                        $homePageUpcomingMatches = collect($upcomingMatches)->filter(function($match) use ($nextWeekDate) {
                            $matchDate = $match['event_date_start'] ?? '';
                            return $matchDate && $matchDate <= $nextWeekDate;
                        });
                        $allMatches = $allMatches->merge($homePageUpcomingMatches);
                    }
                    
                    // Sort matches with the same priority logic as desktop
                    $sortedMatches = $allMatches->sortByDesc(function($match) use ($liveMatches) {
                        $leagueName = strtolower($match['league_name'] ?? '');
                        $matchType = strtolower($match['event_type'] ?? '');
                        $homeTeam = strtolower($match['event_home_team'] ?? '');
                        $awayTeam = strtolower($match['event_away_team'] ?? '');
                        
                        // Check if this is a live match (highest priority)
                        $isLiveMatch = collect($liveMatches)->contains('event_key', $match['event_key'] ?? '');
                        if ($isLiveMatch) {
                            return 1000; // Live matches get highest priority
                        }
                        
                        // Priority teams with specific scores
                        $priorityTeams = [
                            'england' => 100, 'australia' => 100, 'india' => 100, 'pakistan' => 100,
                            'south africa' => 100, 'west indies' => 100, 'new zealand' => 100,
                            'sri lanka' => 100, 'bangladesh' => 100, 'afghanistan' => 100,
                            'ireland' => 100, 'zimbabwe' => 100
                        ];
                        
                        // Calculate priority score
                        $priorityScore = 0;
                        
                        // Check home team
                        foreach ($priorityTeams as $team => $score) {
                            if (str_contains($homeTeam, $team)) {
                                $priorityScore += $score;
                                break;
                            }
                        }
                        
                        // Check away team
                        foreach ($priorityTeams as $team => $score) {
                            if (str_contains($awayTeam, $team)) {
                                $priorityScore += $score;
                                break;
                            }
                        }
                        
                        // Add bonus for international tournaments
                        $internationalKeywords = [
                            'international' => 50, 'test' => 40, 'odi' => 30, 't20i' => 25,
                            'world cup' => 60, 'champions trophy' => 55, 'asia cup' => 45,
                            'european cricket' => 35, 'icc' => 40
                        ];
                        
                        foreach ($internationalKeywords as $keyword => $bonus) {
                            if (str_contains($leagueName, $keyword) || str_contains($matchType, $keyword)) {
                                $priorityScore += $bonus;
                                break;
                            }
                        }
                        
                        return $priorityScore;
                    });
                    
                    // Group by date for grouped view
                    $dateGroups = $sortedMatches->groupBy(function($match) {
                        $date = $match['event_date'] ?? $match['event_date_start'] ?? $match['endDate'] ?? '';
                        if ($date) {
                            try {
                                return \Carbon\Carbon::parse($date)->format('M d, Y');
                            } catch (\Exception $e) {
                                return 'Other Dates';
                            }
                        }
                        return 'Other Dates';
                    });
                @endphp
                
                <!-- Grouped View -->
                <div id="grouped-view">
                    @foreach($dateGroups as $dateName => $matches)
                        @php
                            // Sanitize date name for use as CSS selector
                            $sanitizedDateName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $dateName);
                            $sanitizedDateName = trim($sanitizedDateName, '_');
                        @endphp
                        <div class="mobile-match-card series-group" data-series="{{ $dateName }}">
                            <div class="series-header cursor-pointer" onclick="toggleSeries('{{ $sanitizedDateName }}')">
                                <div class="flex items-center justify-between">
                                    <span>{{ $dateName }}</span>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-xs text-gray-500">{{ $matches->count() }} matches</span>
                                        <svg class="w-4 h-4 transform transition-transform series-arrow" id="arrow-{{ $sanitizedDateName }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                        </div>
                                </div>
                            </div>
                            <div class="series-matches" id="matches-{{ $sanitizedDateName }}">
                                @foreach($matches->take(20) as $match)
                                    @include('cricket.partials.mobile-match-card', ['match' => $match, 'type' => 'mixed'])
                        @endforeach
                            </div>
                    </div>
                @endforeach
                </div>
                
                <!-- Ungrouped View -->
                <div id="ungrouped-view" style="display: none;">
                    @foreach($sortedMatches as $match)
                        @include('cricket.partials.mobile-match-card', ['match' => $match, 'type' => 'mixed'])
                    @endforeach
                </div>
            </div>
        </div>
    </div>
                    
        <!-- Live Tab (Mobile) -->
        <div id="tab-content-live-mobile" class="tab-content hidden">
            <div class="px-4 py-3 bg-white border-b border-gray-200">
                <h2 class="text-base font-semibold text-gray-900">Live Matches</h2>
            </div>
            @if(!empty($liveMatches))
                <div class="mobile-card-container space-y-1">
                    @foreach($liveMatches as $match)
                        <div class="mobile-match-card live-match-card">
                            <div class="series-header">
                                {{ $match['league_name'] ?? 'Live Match' }}
                            </div>
                            @include('cricket.partials.mobile-match-card', ['match' => $match, 'type' => 'live'])
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 px-4">
                    <div class="text-4xl mb-2">🏏</div>
                    <p class="text-gray-500">No live matches at the moment</p>
                </div>
            @endif
        </div>

        <!-- Upcoming Tab (Mobile) -->
        <div id="tab-content-upcoming-mobile" class="tab-content hidden">
            <div class="px-4 py-3 bg-white border-b border-gray-200">
                <h2 class="text-base font-semibold text-gray-900">Upcoming Matches</h2>
            </div>
            @if(!empty($upcomingMatches))
                @php
                    $nextWeekDate = now()->addDays(7)->format('Y-m-d');
                    $homePageUpcomingMatches = collect($upcomingMatches)->filter(function($match) use ($nextWeekDate) {
                        $matchDate = $match['event_date_start'] ?? '';
                        return $matchDate && $matchDate <= $nextWeekDate;
                    });
                    
                    // Sort upcoming matches to prioritize international matches
                    $sortedUpcomingMatches = $homePageUpcomingMatches->sortByDesc(function($match) {
                        $leagueName = strtolower($match['league_name'] ?? '');
                        $isInternational = strpos($leagueName, 'international') !== false || 
                                         strpos($leagueName, 't20 world cup') !== false ||
                                         strpos($leagueName, 'odi world cup') !== false ||
                                         strpos($leagueName, 'test championship') !== false ||
                                         strpos($leagueName, 'asia cup') !== false ||
                                         strpos($leagueName, 'champions trophy') !== false;
                        
                        // International matches get priority (higher score)
                        if ($isInternational) {
                            return 1000;
                        }
                        
                        // Other matches get lower priority
                        return 100;
                    });
                    
                    $upcomingDateGroups = $sortedUpcomingMatches->groupBy(function($match) {
                        $date = $match['event_date'] ?? $match['event_date_start'] ?? $match['endDate'] ?? '';
                        if ($date) {
                            try {
                                return \Carbon\Carbon::parse($date)->format('M d, Y');
                            } catch (\Exception $e) {
                                return 'Other Dates';
                            }
                        }
                        return 'Other Dates';
                    });
                @endphp
                <div class="mobile-card-container space-y-1">
                    @foreach($upcomingDateGroups as $dateName => $matches)
                        @php
                            // Sanitize date name for use as CSS selector
                            $sanitizedDateName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $dateName);
                            $sanitizedDateName = trim($sanitizedDateName, '_');
                        @endphp
                        <div class="mobile-match-card upcoming-match-card series-group" data-series="{{ $dateName }}">
                            <div class="series-header cursor-pointer" onclick="toggleSeries('{{ $sanitizedDateName }}')">
                                <div class="flex items-center justify-between">
                                    <span>{{ $dateName }}</span>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-xs text-gray-500">{{ $matches->count() }} matches</span>
                                        <svg class="w-4 h-4 transform transition-transform series-arrow" id="arrow-{{ $sanitizedDateName }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                            </div>
                                </div>
                            </div>
                            <div class="series-matches" id="matches-{{ $sanitizedDateName }}">
                                @foreach($matches->take(15) as $match)
                                    @include('cricket.partials.mobile-match-card', ['match' => $match, 'type' => 'upcoming'])
                            @endforeach
                            </div>
                        </div>
                    @endforeach
                    
                    <!-- All Upcoming Matches Button -->
                    <div class="text-center py-4">
                        <a href="{{ route('cricket.fixtures') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                            <span>All Upcoming Matches</span>
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            @else
                <div class="text-center py-8 px-4">
                    <div class="text-4xl mb-2">⏰</div>
                    <p class="text-gray-500">No upcoming matches</p>
                    <div class="mt-4">
                        <a href="{{ route('cricket.fixtures') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                            <span>All Upcoming Matches</span>
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            @endif
        </div>

        <!-- Finished Tab (Mobile) -->
        <div id="tab-content-finished-mobile" class="tab-content hidden">
            <div class="px-4 py-3 bg-white border-b border-gray-200">
                <h2 class="text-base font-semibold text-gray-900">Finished Matches</h2>
            </div>
            @if(!empty($recentCompletedMatches))
                @php
                    // Sort recent completed matches: International first, then by date (most recent first)
                    $sortedRecentMatches = collect($recentCompletedMatches)->sort(function($a, $b) {
                        // First sort by match type (International first)
                        $aType = strtolower($a['league_name'] ?? '');
                        $bType = strtolower($b['league_name'] ?? '');
                        
                        $aIsInternational = strpos($aType, 'international') !== false || 
                                           strpos($aType, 'test') !== false || 
                                           strpos($aType, 'odi') !== false || 
                                           strpos($aType, 't20i') !== false ||
                                           strpos($aType, 'world cup') !== false ||
                                           strpos($aType, 'champions trophy') !== false;
                        
                        $bIsInternational = strpos($bType, 'international') !== false || 
                                           strpos($bType, 'test') !== false || 
                                           strpos($bType, 'odi') !== false || 
                                           strpos($bType, 't20i') !== false ||
                                           strpos($bType, 'world cup') !== false ||
                                           strpos($bType, 'champions trophy') !== false;
                        
                        if ($aIsInternational && !$bIsInternational) {
                            return -1;
                        }
                        if (!$aIsInternational && $bIsInternational) {
                            return 1;
                        }
                        
                        // Then sort by date (most recent first)
                        $aDate = $a['event_date'] ?? $a['event_date_start'] ?? $a['endDate'] ?? '';
                        $bDate = $b['event_date'] ?? $b['event_date_start'] ?? $b['endDate'] ?? '';
                        
                        if ($aDate && $bDate) {
                            try {
                                $aTimestamp = \Carbon\Carbon::parse($aDate)->timestamp;
                                $bTimestamp = \Carbon\Carbon::parse($bDate)->timestamp;
                                return $bTimestamp - $aTimestamp; // Most recent first
                            } catch (\Exception $e) {
                                return 0;
                            }
                        }
                        return 0;
                    });
                    
                    // Pagination setup for mobile
                    $perPage = 15;
                    $currentPage = request()->get('mobile_finished_page', 1);
                    $totalRecentMatches = $sortedRecentMatches->count();
                    $totalPages = ceil($totalRecentMatches / $perPage);
                    $offset = ($currentPage - 1) * $perPage;
                    $currentPageRecentMatches = $sortedRecentMatches->slice($offset, $perPage);
                    
                    $finishedDateGroups = $currentPageRecentMatches->groupBy(function($match) {
                        $date = $match['event_date'] ?? $match['event_date_start'] ?? $match['endDate'] ?? '';
                        if ($date) {
                            try {
                                return \Carbon\Carbon::parse($date)->format('M d, Y');
                            } catch (\Exception $e) {
                                return 'Other Dates';
                            }
                        }
                        return 'Other Dates';
                    });
                @endphp
                
                <!-- Mobile Pagination Info -->
                <div class="px-4 py-2 bg-gray-50 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Page {{ $currentPage }} of {{ $totalPages }}</span>
                        <span class="text-sm text-gray-500">{{ $currentPageRecentMatches->count() }} matches</span>
                    </div>
                </div>
                
                <div class="mobile-card-container space-y-1">
                    @foreach($finishedDateGroups as $dateName => $matches)
                        @php
                            // Sanitize date name for use as CSS selector
                            $sanitizedDateName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $dateName);
                            $sanitizedDateName = trim($sanitizedDateName, '_');
                        @endphp
                        <div class="mobile-match-card finished-match-card series-group" data-series="{{ $dateName }}">
                            <div class="series-header cursor-pointer" onclick="toggleSeries('{{ $sanitizedDateName }}')">
                                <div class="flex items-center justify-between">
                                    <span>{{ $dateName }}</span>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-xs text-gray-500">{{ $matches->count() }} matches</span>
                                        <svg class="w-4 h-4 transform transition-transform series-arrow" id="arrow-{{ $sanitizedDateName }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                            </div>
                                </div>
                            </div>
                            <div class="series-matches" id="matches-{{ $sanitizedDateName }}">
                                @foreach($matches as $match)
                                    @include('cricket.partials.mobile-match-card', ['match' => $match, 'type' => 'finished'])
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                    
                    <!-- Mobile Pagination Controls -->
                    @if($totalPages > 1)
                    <div class="mobile-pagination px-4 py-4">
                        <div class="flex items-center justify-between">
                            @if($currentPage > 1)
                                <a href="?mobile_finished_page={{ $currentPage - 1 }}" 
                                   class="pagination-button inline-flex items-center px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 hover:text-gray-700 transition-colors duration-200">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                    </svg>
                                    Previous
                                </a>
                            @else
                                <div></div>
                            @endif
                            
                            <div class="page-numbers flex items-center space-x-1">
                                @php
                                    $startPage = max(1, $currentPage - 1);
                                    $endPage = min($totalPages, $currentPage + 1);
                                @endphp
                                
                                @if($startPage > 1)
                                    <a href="?mobile_finished_page=1" class="page-number px-2 py-1 text-xs font-medium text-gray-500 bg-white border border-gray-300 rounded hover:bg-gray-50 hover:text-gray-700 transition-colors duration-200">1</a>
                                    @if($startPage > 2)
                                        <span class="px-1 text-gray-500">...</span>
                                    @endif
                                @endif
                                
                                @for($i = $startPage; $i <= $endPage; $i++)
                                    <a href="?mobile_finished_page={{ $i }}" 
                                       class="page-number px-2 py-1 text-xs font-medium {{ $i == $currentPage ? 'text-green-600 bg-green-50 border-green-500' : 'text-gray-500 bg-white border-gray-300 hover:bg-gray-50 hover:text-gray-700' }} border rounded transition-colors duration-200">
                                        {{ $i }}
                                    </a>
                                @endfor
                                
                                @if($endPage < $totalPages)
                                    @if($endPage < $totalPages - 1)
                                        <span class="px-1 text-gray-500">...</span>
                                    @endif
                                    <a href="?mobile_finished_page={{ $totalPages }}" class="page-number px-2 py-1 text-xs font-medium text-gray-500 bg-white border border-gray-300 rounded hover:bg-gray-50 hover:text-gray-700 transition-colors duration-200">{{ $totalPages }}</a>
                                @endif
                            </div>
                            
                            @if($currentPage < $totalPages)
                                <a href="?mobile_finished_page={{ $currentPage + 1 }}" 
                                   class="pagination-button inline-flex items-center px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 hover:text-gray-700 transition-colors duration-200">
                                    Next
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            @else
                                <div></div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            @else
                <div class="text-center py-8 px-4">
                    <div class="text-4xl mb-2">🏆</div>
                    <p class="text-gray-500">No recent completed matches</p>
                    <div class="mt-4">
                        <a href="{{ route('cricket.fixtures') }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                            <span>All Finished Matches</span>
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Desktop Tab Content -->
    <div class="hidden lg:block">
    <div id="tab-content-all" class="tab-content active">
        <!-- All Matches Section -->
        <div class="mb-4 sm:mb-8">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-3 sm:mb-6">All Matches</h2>
            @php
                // Combine all matches for display - Live matches first, then today's matches (excluding live ones)
                $allMatches = collect();
                
                // Add live matches first (highest priority)
                if (!empty($liveMatches)) {
                    $allMatches = $allMatches->merge($liveMatches);
                }
                
                // Add today's matches (excluding those that are already live)
                if (!empty($todayMatches)) {
                    $todayNonLiveMatches = collect($todayMatches)->filter(function($match) use ($liveMatches) {
                        $matchKey = $match['event_key'] ?? '';
                        return !collect($liveMatches)->contains('event_key', $matchKey);
                    });
                    $allMatches = $allMatches->merge($todayNonLiveMatches);
                }
                
                // Sort matches with live matches first, then priority teams
                $allMatches = $allMatches->sortByDesc(function($match) use ($liveMatches) {
                    $leagueName = strtolower($match['league_name'] ?? '');
                    $matchType = strtolower($match['event_type'] ?? '');
                    $homeTeam = strtolower($match['event_home_team'] ?? '');
                    $awayTeam = strtolower($match['event_away_team'] ?? '');
                    
                    // Check if this is a live match (highest priority)
                    $isLiveMatch = collect($liveMatches)->contains('event_key', $match['event_key'] ?? '');
                    if ($isLiveMatch) {
                        return 1000; // Live matches get highest priority
                    }
                    
                    // Priority teams with specific scores
                    $priorityTeams = [
                        'england' => 100,
                        'australia' => 100,
                        'india' => 100,
                        'pakistan' => 100,
                        'south africa' => 100,
                        'west indies' => 100,
                        'new zealand' => 100,
                        'sri lanka' => 100,
                        'bangladesh' => 100,
                        'afghanistan' => 100,
                        'ireland' => 100,
                        'zimbabwe' => 100
                    ];
                    
                    // Calculate priority score
                    $priorityScore = 0;
                    
                    // Check home team
                    foreach ($priorityTeams as $team => $score) {
                        if (str_contains($homeTeam, $team)) {
                            $priorityScore += $score;
                            break;
                        }
                    }
                    
                    // Check away team
                    foreach ($priorityTeams as $team => $score) {
                        if (str_contains($awayTeam, $team)) {
                            $priorityScore += $score;
                            break;
                        }
                    }
                    
                    // Add bonus for international tournaments
                    $internationalKeywords = [
                        'international' => 50,
                        'test' => 40,
                        'odi' => 30,
                        't20i' => 25,
                        'world cup' => 60,
                        'champions trophy' => 55,
                        'asia cup' => 45,
                        'european cricket' => 35,
                        'icc' => 40
                    ];
                    
                    foreach ($internationalKeywords as $keyword => $bonus) {
                        if (str_contains($leagueName, $keyword) || str_contains($matchType, $keyword)) {
                            $priorityScore += $bonus;
                            break;
                        }
                    }
                    
                    return $priorityScore;
                });
                
                // Pagination for all matches
                $perPage = 15;
                $currentPage = request()->get('all_page', 1);
                $totalAllMatches = $allMatches->count();
                $totalAllPages = ceil($totalAllMatches / $perPage);
                $offset = ($currentPage - 1) * $perPage;
                
                // Get current page matches
                $currentPageAllMatches = $allMatches->slice($offset, $perPage);
            @endphp
            
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-6">
                @foreach($currentPageAllMatches as $match)
                    @php
                        $matchType = 'today';
                        if (collect($liveMatches)->contains('event_key', $match['event_key'] ?? '')) {
                            $matchType = 'live';
                        }
                    @endphp
                    @include('cricket.partials.match-card', ['match' => $match, 'type' => $matchType])
            @endforeach
        </div>
        
            <!-- Pagination for All Matches -->
            @include('cricket.partials.pagination', [
                'currentPage' => $currentPage,
                'totalPages' => $totalAllPages,
                'totalItems' => $totalAllMatches,
                'perPage' => $perPage,
                'offset' => $offset,
                'pageParam' => 'all_page'
            ])
        </div>
    </div>

    <div id="tab-content-live" class="tab-content hidden">
    <!-- Live Matches Section -->
    @if(!empty($liveMatches))
        <div class="mb-4 sm:mb-8">
            <div class="flex items-center justify-between mb-3 sm:mb-6">
                <h2 class="text-xl sm:text-2xl font-bold text-gray-900"><span class="blink-dot"></span> Live Matches</h2>
            <div class="flex items-center space-x-2">
                <div class="w-3 h-3 bg-red-500 rounded-full animate-pulse"></div>
                    <span class="text-sm text-gray-600">{{ count($liveMatches) }} live</span>
            </div>
        </div>
        
            @php
                // Sort live matches with explicit priority scores
                $sortedLiveMatches = collect($liveMatches)->sortByDesc(function($match) {
                    $leagueName = strtolower($match['league_name'] ?? '');
                    $matchType = strtolower($match['event_type'] ?? '');
                    $homeTeam = strtolower($match['event_home_team'] ?? '');
                    $awayTeam = strtolower($match['event_away_team'] ?? '');
                    
                    // Priority teams with specific scores
                    $priorityTeams = [
                        'england' => 100,
                        'australia' => 100,
                        'india' => 100,
                        'pakistan' => 100,
                        'south africa' => 100,
                        'west indies' => 100,
                        'new zealand' => 100,
                        'sri lanka' => 100,
                        'bangladesh' => 100,
                        'afghanistan' => 100,
                        'ireland' => 100,
                        'zimbabwe' => 100
                    ];
                    
                    // Calculate priority score
                    $priorityScore = 0;
                    
                    // Check home team
                    foreach ($priorityTeams as $team => $score) {
                        if (str_contains($homeTeam, $team)) {
                            $priorityScore += $score;
                            break;
                        }
                    }
                    
                    // Check away team
                    foreach ($priorityTeams as $team => $score) {
                        if (str_contains($awayTeam, $team)) {
                            $priorityScore += $score;
                            break;
                        }
                    }
                    
                    // Add bonus for international tournaments
                    $internationalKeywords = [
                        'international' => 50,
                        'test' => 40,
                        'odi' => 30,
                        't20i' => 25,
                        'world cup' => 60,
                        'champions trophy' => 55,
                        'asia cup' => 45,
                        'european cricket' => 35,
                        'icc' => 40
                    ];
                    
                    foreach ($internationalKeywords as $keyword => $bonus) {
                        if (str_contains($leagueName, $keyword) || str_contains($matchType, $keyword)) {
                            $priorityScore += $bonus;
                            break;
                        }
                    }
                    
                    return $priorityScore;
                });
                
                // Pagination for live matches
                $perPage = 15;
                $currentPage = request()->get('live_page', 1);
                $totalLiveMatches = $sortedLiveMatches->count();
                $totalLivePages = ceil($totalLiveMatches / $perPage);
                $offset = ($currentPage - 1) * $perPage;
                
                // Get current page matches
                $currentPageLiveMatches = $sortedLiveMatches->slice($offset, $perPage);
            @endphp
            
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-6">
                @foreach($currentPageLiveMatches as $match)
                    @include('cricket.partials.match-card', ['match' => $match, 'type' => 'live'])
                @endforeach
                        </div>
                        
            <!-- Pagination Controls for Live Matches -->
            @include('cricket.partials.pagination', [
                'currentPage' => $currentPage,
                'totalPages' => $totalLivePages,
                'totalItems' => $totalLiveMatches,
                'perPage' => $perPage,
                'offset' => $offset,
                'pageParam' => 'live_page'
            ])
        </div>
    </div>
    @else
    <div class="mb-8 text-center py-12">
        <div class="text-6xl mb-4">🏏</div>
        <h3 class="text-xl font-semibold text-gray-600 mb-2">No Live Matches</h3>
        <p class="text-gray-500">There are currently no live matches. Check back later!</p>
    </div>
    @endif
    </div>

    <div id="tab-content-today" class="tab-content hidden">
    <!-- Today's Matches Section -->
        @php
            // Filter out live matches from today's matches
            $todayCompletedMatches = collect($todayMatches)->filter(function($match) use ($liveMatches) {
                $matchKey = $match['event_key'] ?? '';
                return !collect($liveMatches)->contains('event_key', $matchKey);
            })->values();
        @endphp
        
        @if($todayCompletedMatches->count() > 0)
        <div class="mb-4 sm:mb-8">
            <div class="flex items-center justify-between mb-3 sm:mb-6">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900">📅 Today's Matches</h2>
            <div class="flex items-center space-x-2">
                <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                <span class="text-sm text-gray-600">{{ now()->format('M d, Y') }}</span>
            </div>
        </div>
        
        @php
            // Sort today's matches with explicit priority scores
            $sortedTodayMatches = $todayCompletedMatches->sortByDesc(function($match) {
                $leagueName = strtolower($match['league_name'] ?? '');
                $matchType = strtolower($match['event_type'] ?? '');
                $homeTeam = strtolower($match['event_home_team'] ?? '');
                $awayTeam = strtolower($match['event_away_team'] ?? '');
                
                // Priority teams with specific scores
                $priorityTeams = [
                    'england' => 100,
                    'australia' => 100,
                    'india' => 100,
                    'pakistan' => 100,
                    'south africa' => 100,
                    'west indies' => 100,
                    'new zealand' => 100,
                    'sri lanka' => 100,
                    'bangladesh' => 100,
                    'afghanistan' => 100,
                    'ireland' => 100,
                    'zimbabwe' => 100
                ];
                
                // Calculate priority score
                $priorityScore = 0;
                
                // Check home team
                foreach ($priorityTeams as $team => $score) {
                    if (str_contains($homeTeam, $team)) {
                        $priorityScore += $score;
                        break;
                    }
                }
                
                // Check away team
                foreach ($priorityTeams as $team => $score) {
                    if (str_contains($awayTeam, $team)) {
                        $priorityScore += $score;
                        break;
                    }
                }
                
                // Add bonus for international tournaments
                $internationalKeywords = [
                    'international' => 50,
                    'test' => 40,
                    'odi' => 30,
                    't20i' => 25,
                    'world cup' => 60,
                    'champions trophy' => 55,
                    'asia cup' => 45,
                    'european cricket' => 35,
                    'icc' => 40
                ];
                
                foreach ($internationalKeywords as $keyword => $bonus) {
                    if (str_contains($leagueName, $keyword) || str_contains($matchType, $keyword)) {
                        $priorityScore += $bonus;
                        break;
                    }
                }
                
                return $priorityScore;
            });
            
            // Pagination for today's matches
            $perPage = 15;
            $currentPage = request()->get('today_page', 1);
            $totalTodayMatches = $sortedTodayMatches->count();
            $totalTodayPages = ceil($totalTodayMatches / $perPage);
            $offset = ($currentPage - 1) * $perPage;
            
            // Get current page matches
            $currentPageTodayMatches = $sortedTodayMatches->slice($offset, $perPage);
        @endphp
        
        <div class="grid grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-6">
            @foreach($currentPageTodayMatches as $match)
                @include('cricket.partials.match-card', ['match' => $match, 'type' => 'today'])
            @endforeach
        </div>
        
        <!-- Pagination for Today's Matches -->
        @include('cricket.partials.pagination', [
            'currentPage' => $currentPage,
            'totalPages' => $totalTodayPages,
            'totalItems' => $totalTodayMatches,
            'perPage' => $perPage,
            'offset' => $offset,
            'pageParam' => 'today_page'
        ])
    </div>
    @else
    <div class="mb-8 text-center py-12">
        <div class="text-6xl mb-4">📅</div>
            <h3 class="text-xl font-semibold text-gray-600 mb-2">No Completed Matches Today</h3>
            <p class="text-gray-500">There are no completed matches for today.</p>
        </div>
        @endif
    </div>

    <div id="tab-content-upcoming" class="tab-content hidden">
    <!-- Upcoming Matches Section -->
    @if(!empty($upcomingMatches))
        @php
            // Filter upcoming matches to show only next 7 days on home page
            $nextWeekDate = now()->addDays(7)->format('Y-m-d');
            $homePageUpcomingMatches = collect($upcomingMatches)->filter(function($match) use ($nextWeekDate) {
                $matchDate = $match['event_date_start'] ?? '';
                return $matchDate && $matchDate <= $nextWeekDate;
            });
            
            // Sort upcoming matches with explicit priority scores
            $sortedUpcomingMatches = $homePageUpcomingMatches->sortByDesc(function($match) {
                $leagueName = strtolower($match['league_name'] ?? '');
                $matchType = strtolower($match['event_type'] ?? '');
                $homeTeam = strtolower($match['event_home_team'] ?? '');
                $awayTeam = strtolower($match['event_away_team'] ?? '');
                
                // Priority teams with specific scores
                $priorityTeams = [
                    'england' => 100,
                    'australia' => 100,
                    'india' => 100,
                    'pakistan' => 100,
                    'south africa' => 100,
                    'west indies' => 100,
                    'new zealand' => 100,
                    'sri lanka' => 100,
                    'bangladesh' => 100,
                    'afghanistan' => 100,
                    'ireland' => 100,
                    'zimbabwe' => 100
                ];
                
                // Calculate priority score
                $priorityScore = 0;
                
                // Check home team
                foreach ($priorityTeams as $team => $score) {
                    if (str_contains($homeTeam, $team)) {
                        $priorityScore += $score;
                        break;
                    }
                }
                
                // Check away team
                foreach ($priorityTeams as $team => $score) {
                    if (str_contains($awayTeam, $team)) {
                        $priorityScore += $score;
                        break;
                    }
                }
                
                // Add bonus for international tournaments
                $internationalKeywords = [
                    'international' => 50,
                    'test' => 40,
                    'odi' => 30,
                    't20i' => 25,
                    'world cup' => 60,
                    'champions trophy' => 55,
                    'asia cup' => 45,
                    'european cricket' => 35,
                    'icc' => 40
                ];
                
                foreach ($internationalKeywords as $keyword => $bonus) {
                    if (str_contains($leagueName, $keyword) || str_contains($matchType, $keyword)) {
                        $priorityScore += $bonus;
                        break;
                    }
                }
                
                return $priorityScore;
            });
            
           
            
            // Show only first 12 upcoming matches for home page
            $currentPageUpcomingMatches = $sortedUpcomingMatches->take(15);
        @endphp
        
        <div class="mb-4 sm:mb-8">
            <div class="flex items-center justify-between mb-3 sm:mb-6">
                <h2 class="text-xl sm:text-2xl font-bold text-gray-900">⏰ Upcoming Matches</h2>
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 bg-purple-500 rounded-full"></div>
                    <span class="text-sm text-gray-600">Next 7 days</span>
                    <span class="text-sm text-gray-500">(Showing 15 of {{ $sortedUpcomingMatches->count() }} matches)</span>
                </div>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-6">
                @foreach($currentPageUpcomingMatches as $match)
                    @include('cricket.partials.match-card', ['match' => $match, 'type' => 'upcoming'])
            @endforeach
        </div>
        
        <!-- View More Button -->
        <div class="mt-6 text-center">
            <a href="{{ route('cricket.fixtures') }}" 
               class="inline-flex items-center px-6 py-3 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition-colors duration-200">
                <span>📅 View All Upcoming Matches</span>
                <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>
    </div>
        @else
        <div class="mb-8 text-center py-12">
            <div class="text-6xl mb-4">⏰</div>
            <h3 class="text-xl font-semibold text-gray-600 mb-2">No Upcoming Matches</h3>
            <p class="text-gray-500">There are no upcoming matches in the next 7 days.</p>
            <div class="mt-4">
                <a href="{{ route('cricket.fixtures') }}" 
                   class="inline-flex items-center px-4 py-2 bg-green-600 text-white font-medium rounded-md hover:bg-green-700 transition-colors duration-200">
                    📅 Check Fixtures for More Matches
                </a>
            </div>
        </div>
        @endif
    </div>

    <!-- Recent Completed Matches Tab Content -->
    <div id="tab-content-recent" class="tab-content hidden">
        <!-- Recent Completed Matches Section -->
        @if(!empty($recentCompletedMatches))
            @php
                // Sort recent completed matches: International first, then by date (most recent first)
                $sortedRecentMatches = collect($recentCompletedMatches)->sort(function($a, $b) {
                    // First sort by match type (International first)
                    $aType = strtolower($a['league_name'] ?? '');
                    $bType = strtolower($b['league_name'] ?? '');
                    
                    $aIsInternational = strpos($aType, 'international') !== false || 
                                       strpos($aType, 'test') !== false || 
                                       strpos($aType, 'odi') !== false || 
                                       strpos($aType, 't20i') !== false ||
                                       strpos($aType, 'world cup') !== false ||
                                       strpos($aType, 'champions trophy') !== false;
                    
                    $bIsInternational = strpos($bType, 'international') !== false || 
                                       strpos($bType, 'test') !== false || 
                                       strpos($bType, 'odi') !== false || 
                                       strpos($bType, 't20i') !== false ||
                                       strpos($bType, 'world cup') !== false ||
                                       strpos($bType, 'champions trophy') !== false;
                    
                    if ($aIsInternational && !$bIsInternational) {
                        return -1;
                    }
                    if (!$aIsInternational && $bIsInternational) {
                        return 1;
                    }
                    
                    // Then sort by date (most recent first)
                    $aDate = $a['event_date'] ?? $a['event_date_start'] ?? '';
                    $bDate = $b['event_date'] ?? $b['event_date_start'] ?? '';
                    
                    if ($aDate && $bDate) {
                        try {
                            $aTimestamp = \Carbon\Carbon::parse($aDate)->timestamp;
                            $bTimestamp = \Carbon\Carbon::parse($bDate)->timestamp;
                            return $bTimestamp - $aTimestamp; // Most recent first
                        } catch (\Exception $e) {
                            return 0;
                        }
                    }
                    return 0;
                });
                
                // Pagination setup
                $perPage = 15;
                $currentPage = request()->get('recent_page', 1);
                $totalRecentMatches = $sortedRecentMatches->count();
                $totalPages = ceil($totalRecentMatches / $perPage);
                $offset = ($currentPage - 1) * $perPage;
                $currentPageRecentMatches = $sortedRecentMatches->slice($offset, $perPage);
            @endphp
            
            <div class="mb-4 sm:mb-8">
                <div class="flex items-center justify-between mb-3 sm:mb-6">
                    <h2 class="text-xl sm:text-2xl font-bold text-gray-900">🏆 Recent Completed Matches</h2>
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                        <span class="text-sm text-gray-600">Sorted by International teams first</span>
                        <span class="text-sm text-gray-500">(Page {{ $currentPage }} of {{ $totalPages }} - {{ $currentPageRecentMatches->count() }} matches)</span>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-6" id="recent-matches-container">
                    @foreach($currentPageRecentMatches as $match)
                        @include('cricket.partials.match-card', ['match' => $match, 'type' => 'finished'])
                    @endforeach
                </div>
                
                <!-- Pagination Controls -->
                @if($totalPages > 1)
                <div class="mt-6 flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        @if($currentPage > 1)
                            <a href="?recent_page={{ $currentPage - 1 }}" 
                               class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 hover:text-gray-700 transition-colors duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                                Previous
                            </a>
                        @endif
                        
                        <span class="text-sm text-gray-700">
                            Page {{ $currentPage }} of {{ $totalPages }}
                        </span>
                        
                        @if($currentPage < $totalPages)
                            <a href="?recent_page={{ $currentPage + 1 }}" 
                               class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 hover:text-gray-700 transition-colors duration-200">
                                Next
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        @endif
                    </div>
                    
                    <!-- Page Numbers -->
                    <div class="flex items-center space-x-1">
                        @php
                            $startPage = max(1, $currentPage - 2);
                            $endPage = min($totalPages, $currentPage + 2);
                        @endphp
                        
                        @if($startPage > 1)
                            <a href="?recent_page=1" class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 hover:text-gray-700 transition-colors duration-200">1</a>
                            @if($startPage > 2)
                                <span class="px-2 text-gray-500">...</span>
                            @endif
                        @endif
                        
                        @for($i = $startPage; $i <= $endPage; $i++)
                            <a href="?recent_page={{ $i }}" 
                               class="px-3 py-2 text-sm font-medium {{ $i == $currentPage ? 'text-green-600 bg-green-50 border-green-500' : 'text-gray-500 bg-white border-gray-300 hover:bg-gray-50 hover:text-gray-700' }} border rounded-md transition-colors duration-200">
                                {{ $i }}
                            </a>
                        @endfor
                        
                        @if($endPage < $totalPages)
                            @if($endPage < $totalPages - 1)
                                <span class="px-2 text-gray-500">...</span>
                            @endif
                            <a href="?recent_page={{ $totalPages }}" class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 hover:text-gray-700 transition-colors duration-200">{{ $totalPages }}</a>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        @else
            <div class="mb-8 text-center py-12">
                <div class="text-6xl mb-4">🏆</div>
                <h3 class="text-xl font-semibold text-gray-600 mb-2">No Recent Completed Matches</h3>
                <p class="text-gray-500">There are no completed matches in the past 7 days.</p>
                <div class="mt-4">
                    <a href="{{ route('cricket.results') }}" 
                       class="inline-flex items-center px-4 py-2 bg-green-600 text-white font-medium rounded-md hover:bg-green-700 transition-colors duration-200">
                        🏆 Check Results for More Matches
                    </a>
                </div>
            </div>
        @endif
    </div>
    </div> <!-- End Desktop Tab Content -->

    <!-- No Search Results Message -->
    <div id="noSearchResults" class="mb-4 sm:mb-8 text-center py-6 sm:py-12" style="display: none;">
        <div class="text-4xl sm:text-6xl mb-2 sm:mb-4">🔍</div>
        <h3 class="text-lg sm:text-xl font-semibold text-gray-600 mb-2">No Matches Found</h3>
        <p class="text-sm sm:text-base text-gray-500 mb-3 sm:mb-4">No matches match your search criteria. Try adjusting your search terms or date range.</p>
        <button onclick="clearSearch()" class="bg-green-600 text-white px-4 sm:px-6 py-2 rounded-md hover:bg-green-700 transition-colors text-sm sm:text-base">
            🗑️ Clear Search
            </button>
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
        const searchInput = document.getElementById('searchInput');

        // Tab switching
        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                const isMobile = window.innerWidth < 1024;
                let targetTab = this.id.replace('tab-', 'tab-content-');
                
                // Handle mobile-specific tab names
                if (isMobile) {
                    if (this.id === 'tab-for-you') {
                        targetTab = 'tab-content-for-you';
                    } else if (this.id === 'tab-live') {
                        targetTab = 'tab-content-live-mobile';
                    } else if (this.id === 'tab-upcoming') {
                        targetTab = 'tab-content-upcoming-mobile';
                    } else if (this.id === 'tab-finished') {
                        targetTab = 'tab-content-finished-mobile';
                    }
                }
                
                // Update active tab button
                tabButtons.forEach(btn => {
                    if (isMobile) {
                        // Mobile tab styling
                        btn.classList.remove('active', 'border-red-500', 'text-red-600');
                        btn.classList.add('border-transparent', 'text-gray-500');
                    } else {
                        // Desktop tab styling
                    btn.classList.remove('active', 'border-green-500', 'text-green-600');
                    btn.classList.add('border-transparent', 'text-gray-500');
                    }
                });
                
                if (isMobile) {
                    this.classList.add('active', 'border-red-500', 'text-red-600');
                    this.classList.remove('border-transparent', 'text-gray-500');
                } else {
                this.classList.add('active', 'border-green-500', 'text-green-600');
                this.classList.remove('border-transparent', 'text-gray-500');
                }
                
                // Show target tab content
                tabContents.forEach(content => {
                    content.classList.add('hidden');
                    content.classList.remove('active');
                });
                
                const targetElement = document.getElementById(targetTab);
                if (targetElement) {
                    targetElement.classList.remove('hidden');
                    targetElement.classList.add('active');
                }
                
                // Apply filters to current tab (desktop only)
                if (!isMobile) {
                filterMatches();
                }
            });
        });

        // Search functionality
        searchInput.addEventListener('input', filterMatches);

        function filterMatches() {
            const searchTerm = searchInput.value.toLowerCase();
            
            // Update search status
            updateSearchStatus(searchTerm);
            
            // Get current active tab
            const activeTab = document.querySelector('.tab-content.active');
            if (!activeTab) return;
            
            const matchCards = activeTab.querySelectorAll('.match-card');
            let visibleCount = 0;
            
            // Convert match cards to array for sorting
            const matchCardsArray = Array.from(matchCards);
            
            // Sort matches by priority score (highest first)
            matchCardsArray.sort((a, b) => {
                const aScore = getPriorityScore(a);
                const bScore = getPriorityScore(b);
                
                return bScore - aScore; // Higher score first
            });
            
            // Reorder DOM elements
            const container = activeTab.querySelector('.grid');
            if (container) {
                matchCardsArray.forEach(card => {
                    container.appendChild(card);
                });
            }
            
            // Apply filters
            matchCardsArray.forEach(card => {
                let showCard = true;
                
                // Search filter
                if (searchTerm) {
                    const cardText = card.textContent.toLowerCase();
                    if (!cardText.includes(searchTerm)) {
                        showCard = false;
                    }
                }
                

                
                card.style.display = showCard ? 'block' : 'none';
                if (showCard) visibleCount++;
            });
            
            // Show/hide no results message
            const noResults = document.getElementById('noSearchResults');
            if (noResults) {
                if (visibleCount === 0 && searchTerm) {
                    noResults.style.display = 'block';
                } else {
                    noResults.style.display = 'none';
                }
            }
        }

        function updateSearchStatus(searchTerm) {
            const searchStatus = document.getElementById('searchStatus');
            if (searchStatus) {
                if (searchTerm) {
                    searchStatus.style.display = 'block';
                    searchStatus.textContent = `Searching for: "${searchTerm}"`;
                } else {
                    searchStatus.style.display = 'none';
                }
            }
        }

        // Function to calculate priority score for a match
        function getPriorityScore(matchCard) {
            const leagueName = matchCard.getAttribute('data-league') || '';
            const matchType = matchCard.getAttribute('data-match-type') || '';
            const homeTeam = matchCard.getAttribute('data-home-team') || '';
            const awayTeam = matchCard.getAttribute('data-away-team') || '';
            
            // Priority teams with specific scores
            const priorityTeams = {
                'england': 100,
                'australia': 100,
                'india': 100,
                'pakistan': 100,
                'south africa': 100,
                'west indies': 100,
                'new zealand': 100,
                'sri lanka': 100,
                'bangladesh': 100,
                'afghanistan': 100,
                'ireland': 100,
                'zimbabwe': 100
            };
            
            // Calculate priority score
            let priorityScore = 0;
            
            // Check home team
            for (const [team, score] of Object.entries(priorityTeams)) {
                if (homeTeam.toLowerCase().includes(team)) {
                    priorityScore += score;
                    break;
                }
            }
            
            // Check away team
            for (const [team, score] of Object.entries(priorityTeams)) {
                if (awayTeam.toLowerCase().includes(team)) {
                    priorityScore += score;
                    break;
                }
            }
            
            // Add bonus for international tournaments
            const internationalKeywords = {
                'international': 50,
                'test': 40,
                'odi': 30,
                't20i': 25,
                'world cup': 60,
                'champions trophy': 55,
                'asia cup': 45,
                'european cricket': 35,
                'icc': 40
            };
            
            for (const [keyword, bonus] of Object.entries(internationalKeywords)) {
                if (leagueName.toLowerCase().includes(keyword) || matchType.toLowerCase().includes(keyword)) {
                    priorityScore += bonus;
                    break;
                }
            }
            
            return priorityScore;
        }

        // Initial filter
        filterMatches();
        
        // Series grouping toggle functionality
        const toggleButton = document.getElementById('toggle-series-grouping');
        const groupingText = document.getElementById('grouping-text');
        const groupedView = document.getElementById('grouped-view');
        const ungroupedView = document.getElementById('ungrouped-view');
        
        if (toggleButton && groupedView && ungroupedView) {
            let isGrouped = true;
            
            toggleButton.addEventListener('click', function() {
                isGrouped = !isGrouped;
                
                if (isGrouped) {
                    groupedView.style.display = 'block';
                    ungroupedView.style.display = 'none';
                    groupingText.textContent = 'Grouped';
                } else {
                    groupedView.style.display = 'none';
                    ungroupedView.style.display = 'block';
                    groupingText.textContent = 'Ungrouped';
                }
            });
        }
        
        // Individual series toggle functionality
        window.toggleSeries = function(seriesName) {
            try {
                // Handle multiple tabs by searching for the series in the active tab
                const activeTab = document.querySelector('.tab-content.active');
                if (!activeTab) {
                    console.warn('No active tab found');
                    return;
                }
                
                const matchesDiv = activeTab.querySelector('#matches-' + seriesName);
                const arrow = activeTab.querySelector('#arrow-' + seriesName);
                
                if (!matchesDiv) {
                    console.warn('Matches div not found for series:', seriesName);
                    return;
                }
                
                if (!arrow) {
                    console.warn('Arrow element not found for series:', seriesName);
                    return;
                }
                
                const isHidden = matchesDiv.style.display === 'none' || matchesDiv.style.display === '';
                
                if (isHidden) {
                    matchesDiv.style.display = 'block';
                    if (arrow.style) {
                        arrow.style.transform = 'rotate(0deg)';
                    }
                } else {
                    matchesDiv.style.display = 'none';
                    if (arrow.style) {
                        arrow.style.transform = 'rotate(-90deg)';
                    }
                }
            } catch (error) {
                console.error('Error in toggleSeries:', error);
            }
        };
    });

    // Global function to clear search
    function clearSearch() {
        const searchInput = document.getElementById('searchInput');
        
        if (searchInput) searchInput.value = '';
        
        // Trigger filter update
        filterMatches();
    }

</script>
@endsection

