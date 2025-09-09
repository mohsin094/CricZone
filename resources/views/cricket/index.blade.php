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

        /* Mobile tab styles */
    .mobile-tab-container {
        position: fixed;
        top: 64px; /* Below the top navbar */
        left: 0;
        right: 0;
        z-index: 40;
        background: white;
        border-bottom: 1px solid #e5e7eb;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            height: 48px;
        }
        
        .mobile-tab-scroll {
            height: 100%;
            overflow-x: auto;
            display: flex;
            -webkit-overflow-scrolling: touch;
        }
        
        .mobile-tab-scroll nav {
            display: flex;
            width: 100%;
            min-width: max-content;
        }
        
        .mobile-tab-button {
            flex: 1;
            min-width: 90px;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 12px;
            font-size: 14px;
            font-weight: 500;
            color: #6b7280;
            border-bottom: 2px solid transparent;
            white-space: nowrap;
        }
        
        .mobile-tab-button.active {
            color: #ef4444;
            border-bottom-color: #ef4444;
        }
        
    .mobile-content {
            margin-top: 48px; /* Height of the tab bar */
        padding-top: 0;
    }
    
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        /* Match card styles */
    .mobile-match-card {
        background: white;
            border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin: 10px;
        overflow: hidden;
    }
    
    .series-header {
            background: #f8fafc;
            padding: 10px 12px;
        border-bottom: 1px solid #e5e7eb;
        font-weight: 600;
        color: #374151;
        font-size: 12px;
    }
    
    .match-item {
            padding: 12px;
        border-bottom: 1px solid #f3f4f6;
    }
    
    .match-item:last-child {
        border-bottom: none;
    }
    
    .team-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
            margin-bottom: 6px;
    }
    
    .team-info {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .team-flag {
            width: 20px;
            height: 20px;
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
    
        /* Live Match Update Animations */
        .score-updated {
            animation: scorePulse 0.6s ease-in-out;
            background-color: #fef3c7;
            border-radius: 4px;
            padding: 2px 4px;
        }

        @keyframes scorePulse {
            0% {
                transform: scale(1);
                background-color: #fef3c7;
            }
            50% {
                transform: scale(1.05);
                background-color: #fde68a;
            }
            100% {
                transform: scale(1);
                background-color: #fef3c7;
            }
        }

        .live-updated {
            animation: liveUpdate 1.5s ease-in-out;
            position: relative;
        }

        @keyframes liveUpdate {
            0% {
                background-color: transparent;
            }
            25% {
                background-color: #dcfce7;
            }
            50% {
                background-color: #bbf7d0;
            }
            75% {
                background-color: #dcfce7;
            }
            100% {
                background-color: transparent;
            }
        }

        .live-match-card {
            position: relative;
            transition: all 0.3s ease;
        }

        .live-match-card.updating {
            box-shadow: 0 0 0 2px #22c55e;
            transform: scale(1.02);
        }

        .connection-status {
            position: fixed;
            top: 80px;
            right: 20px;
            padding: 6px 10px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: bold;
            z-index: 999;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        /* Connection status when in news sidebar */
        .news-sidebar .connection-status {
            position: static;
            margin-bottom: 1rem;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .connection-status.connected {
            background-color: #22c55e;
            color: white;
        }

        .connection-status.disconnected {
            background-color: #ef4444;
            color: white;
        }

        .connection-status.connecting {
            background-color: #f59e0b;
            color: white;
        }

        /* Responsive connection status positioning */
        @media (max-width: 1024px) {
            .connection-status {
                top: 70px;
                right: 10px;
                padding: 4px 8px;
                font-size: 10px;
            }
        }

        @media (max-width: 768px) {
            .connection-status {
                top: 60px;
                right: 10px;
                padding: 3px 6px;
                font-size: 9px;
            }
        }

        /* News Sidebar Styles */
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .news-item:hover {
            transform: translateY(-1px);
            transition: transform 0.2s ease;
        }

        .news-number {
            background: linear-gradient(135deg, #10b981, #3b82f6);
        }

        .quick-stats-item {
            transition: all 0.2s ease;
        }

        .quick-stats-item:hover {
            background-color: rgba(16, 185, 129, 0.05);
            border-radius: 4px;
            padding: 2px 4px;
            margin: -2px -4px;
        }

        /* News Image Styles */
        .news-image {
            transition: transform 0.2s ease;
        }

        .news-item:hover .news-image {
            transform: scale(1.05);
        }

        .news-fallback {
            background: linear-gradient(135deg, #10b981, #3b82f6);
        }

        /* Ensure proper desktop layout */
        @media (min-width: 1024px) {
            .desktop-layout {
                display: flex !important;
                gap: 1.5rem;
            }
            
            .main-content {
                flex: 1;
                min-width: 0;
            }
            
            .news-sidebar {
                width: 20rem;
                flex-shrink: 0;
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
    </div> <!-- End Mobile Tab Content -->

    <!-- Desktop Layout with News Sidebar -->
    <div class="hidden lg:block">
        <div class="flex gap-6">
            <!-- Main Content Area -->
            <div class="flex-1">
                <!-- Desktop Tab Content -->
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
                </div> <!-- End tab-content-all -->

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
                </div> <!-- End tab-content-recent -->
            </div> <!-- End Main Content Area -->
            
            <!-- News Sidebar -->
            <div class="w-80 flex-shrink-0">
                <div class="sticky top-6">
                    <!-- Live Updates Status -->
                    <div class="mb-4">
                        <div id="connection-status" class="connection-status connecting" style="position: static; margin-bottom: 0;">
                            <div class="flex items-center justify-center space-x-2">
                                <div class="w-2 h-2 bg-yellow-500 rounded-full animate-pulse"></div>
                                <span>Live Updates</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-md border border-gray-200 p-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">📰 Featured News</h3>
                            <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
    </div>
                    
                        @if(isset($featuredNews['news']) && count($featuredNews['news']) > 0)
                            <div class="space-y-4">
                                @foreach($featuredNews['news'] as $index => $news)
                                    <div class="news-item border-b border-gray-100 pb-4 {{ $index === count($featuredNews['news']) - 1 ? 'border-b-0 pb-0' : '' }}">
                                        <div class="flex items-start space-x-3">
                                            <!-- News Image -->
                                            <div class="flex-shrink-0">
                                                @if(!empty($news['cover_image']))
                                                    <img src="{{ $news['cover_image'] }}" 
                                                         alt="{{ $news['title'] ?? 'News Image' }}"
                                                         class="w-16 h-16 object-cover rounded-lg news-image"
                                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                    <div class="w-16 h-16 news-fallback rounded-lg flex items-center justify-center text-white text-xs font-bold" style="display: none;">
                                                        {{ $index + 1 }}
                                                    </div>
                                                @else
                                                    <div class="w-16 h-16 news-fallback rounded-lg flex items-center justify-center text-white text-xs font-bold">
                                                        {{ $index + 1 }}
                                                    </div>
                                                @endif
                                            </div>
                                            
                                            <!-- News Content -->
                                            <div class="flex-1 min-w-0">
                                                <h4 class="text-sm font-medium text-gray-900 line-clamp-2 mb-1">
                                                    <a href="{{ $news['link'] ?? '#' }}" target="_blank" class="hover:text-green-600 transition-colors">
                                                        {{ $news['title'] ?? 'News Title' }}
                                                    </a>
                                                </h4>
                                                <p class="text-xs text-gray-500 line-clamp-2 mb-2">
                                                    {{ $news['excerpt'] ?? $news['description'] ?? 'News description...' }}
                                                </p>
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center space-x-2">
                                                        <span class="text-xs text-gray-400">
                                                            @if(isset($news['published_at_human']))
                                                                {{ $news['published_at_human'] }}
                                                            @elseif(isset($news['pubDate']))
                                                                {{ \Carbon\Carbon::parse($news['pubDate'])->diffForHumans() }}
                                                            @else
                                                                Recently
                                                            @endif
                                                        </span>
                                                        <span class="text-xs text-gray-300">•</span>
                                                        <span class="text-xs text-green-600 font-medium">ESPN</span>
                                                    </div>
                                                    <div class="flex items-center text-xs text-gray-400">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                                        </svg>
                                                        Read
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <a href="{{ route('cricket.news') }}" class="inline-flex items-center text-sm font-medium text-green-600 hover:text-green-700 transition-colors">
                                    View All News
                                    <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            </div>
                        @else
                            <div class="text-center py-8">
                                <div class="text-4xl mb-2">📰</div>
                                <p class="text-sm text-gray-500">No news available at the moment</p>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Quick Stats Card -->
                    <div class="mt-4 bg-gradient-to-br from-green-50 to-blue-50 rounded-lg border border-gray-200 p-4">
                        <h4 class="text-sm font-semibold text-gray-900 mb-3">🏏 Quick Stats</h4>
                        <div class="space-y-2">
                            <div class="quick-stats-item flex justify-between text-xs">
                                <span class="text-gray-600">Live Matches</span>
                                <span class="font-medium text-green-600">{{ count($liveMatches ?? []) }}</span>
                            </div>
                            <div class="quick-stats-item flex justify-between text-xs">
                                <span class="text-gray-600">Today's Matches</span>
                                <span class="font-medium text-blue-600">{{ count($todayMatches ?? []) }}</span>
                            </div>
                            <div class="quick-stats-item flex justify-between text-xs">
                                <span class="text-gray-600">Upcoming</span>
                                <span class="font-medium text-purple-600">{{ count($upcomingMatches ?? []) }}</span>
                            </div>
                            <div class="quick-stats-item flex justify-between text-xs">
                                <span class="text-gray-600">Recent Completed</span>
                                <span class="font-medium text-gray-600">{{ count($recentCompletedMatches ?? []) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div> <!-- End News Sidebar -->
        </div> <!-- End Desktop Layout -->
    </div> <!-- End Desktop Layout with News Sidebar -->

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

    // Live Match Updater
    class LiveMatchUpdater {
        constructor() {
            this.liveMatches = new Map();
            this.updateInterval = null;
            this.isConnected = false;
            this.retryCount = 0;
            this.maxRetries = 5;
            
            this.init();
        }

        init() {
            // Start periodic updates
            this.startPeriodicUpdates();
            
            // Listen for visibility changes
            this.handleVisibilityChange();
            
            // Add connection status indicator
            this.addConnectionStatus();
        }

        async fetchLiveMatches() {
            try {
                const response = await fetch('/api/live-matches', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                if (data.success) {
                    this.handleLiveMatchesUpdate(data.data);
                    this.updateConnectionStatus('connected');
                    this.retryCount = 0;
                } else {
                    throw new Error(data.message || 'Failed to fetch live matches');
                }
            } catch (error) {
                console.error('Error fetching live matches:', error);
                this.updateConnectionStatus('disconnected');
                this.retryCount++;
                
                if (this.retryCount >= this.maxRetries) {
                    console.error('Max retries reached, stopping updates');
                    this.stopUpdates();
                }
            }
        }

        handleLiveMatchesUpdate(data) {
            const { live_matches, timestamp } = data;
            
            console.log('Live matches updated:', live_matches.length, 'matches');
            
            // If no live matches, stop updates
            if (live_matches.length === 0) {
                console.log('No live matches found, stopping updates');
                this.stopUpdates();
                this.updateConnectionStatus('disconnected');
                return;
            }
            
            // Update all live matches
            live_matches.forEach(match => {
                const matchKey = match.event_key;
                if (matchKey) {
                    this.liveMatches.set(matchKey, {
                        ...match,
                        last_updated: timestamp
                    });
                    
                    // Update the UI
                    this.updateMatchInUI(matchKey, match);
                }
            });
        }

        updateMatchInUI(matchKey, matchData) {
            // Find the match card element
            const matchCard = document.querySelector(`[data-match-key="${matchKey}"]`);
            if (!matchCard) {
                return;
            }

            // Add updating class
            matchCard.classList.add('updating');
            setTimeout(() => {
                matchCard.classList.remove('updating');
            }, 2000);

            // Update scores
            this.updateMatchScores(matchCard, matchData);
            
            // Update status
            this.updateMatchStatus(matchCard, matchData);
            
            // Update overs
            this.updateMatchOvers(matchCard, matchData);
        }

        updateMatchScores(matchCard, matchData) {
            // Update home team score
            const homeScoreElement = matchCard.querySelector('.home-score');
            if (homeScoreElement && matchData.event_home_final_result) {
                const newScore = matchData.event_home_final_result;
                if (homeScoreElement.textContent !== newScore) {
                    this.animateScoreChange(homeScoreElement, newScore);
                }
            }

            // Update away team score
            const awayScoreElement = matchCard.querySelector('.away-score');
            if (awayScoreElement && matchData.event_away_final_result) {
                const newScore = matchData.event_away_final_result;
                if (awayScoreElement.textContent !== newScore) {
                    this.animateScoreChange(awayScoreElement, newScore);
                }
            }
        }

        updateMatchStatus(matchCard, matchData) {
            const statusElement = matchCard.querySelector('.match-status');
            if (statusElement) {
                const newStatus = matchData.status || matchData.event_status_info || matchData.event_state_title || 'Match in Progress';
                if (statusElement.textContent !== newStatus) {
                    statusElement.textContent = newStatus;
                    this.showUpdateIndicator(statusElement);
                }
            }
        }

        updateMatchOvers(matchCard, matchData) {
            // Update home team overs
            const homeOversElement = matchCard.querySelector('.home-overs');
            if (homeOversElement && matchData.event_home_overs) {
                const newOvers = this.formatOvers(matchData.event_home_overs);
                if (homeOversElement.textContent !== newOvers) {
                    homeOversElement.textContent = newOvers;
                    this.showUpdateIndicator(homeOversElement);
                }
            }

            // Update away team overs
            const awayOversElement = matchCard.querySelector('.away-overs');
            if (awayOversElement && matchData.event_away_overs) {
                const newOvers = this.formatOvers(matchData.event_away_overs);
                if (awayOversElement.textContent !== newOvers) {
                    awayOversElement.textContent = newOvers;
                    this.showUpdateIndicator(awayOversElement);
                }
            }
        }

        formatOvers(overs) {
            if (!overs || overs === '0.0') return '';
            
            const decimalOvers = parseFloat(overs);
            const fullOvers = Math.floor(decimalOvers);
            const balls = (decimalOvers - fullOvers) * 10;
            
            if (balls >= 6) {
                return (fullOvers + 1).toString();
            }
            
            return fullOvers.toString();
        }

        animateScoreChange(element, newScore) {
            // Add animation class
            element.classList.add('score-updated');
            
            // Update the score
            element.textContent = newScore;
            
            // Remove animation class after animation
            setTimeout(() => {
                element.classList.remove('score-updated');
            }, 1000);
        }

        showUpdateIndicator(element) {
            // Add update indicator
            element.classList.add('live-updated');
            
            // Remove after animation
            setTimeout(() => {
                element.classList.remove('live-updated');
            }, 2000);
        }

        startPeriodicUpdates() {
            // Update every 20 seconds
            this.updateInterval = setInterval(() => {
                this.fetchLiveMatches();
            }, 20000);
            
            // Initial fetch
            this.fetchLiveMatches();
        }

        stopUpdates() {
            if (this.updateInterval) {
                clearInterval(this.updateInterval);
                this.updateInterval = null;
            }
        }

        handleVisibilityChange() {
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    // Page is hidden, reduce update frequency
                    this.stopUpdates();
                    this.updateInterval = setInterval(() => {
                        this.fetchLiveMatches();
                    }, 120000); // 2 minutes when hidden
                } else {
                    // Page is visible, resume normal updates
                    this.stopUpdates();
                    this.startPeriodicUpdates();
                }
            });
        }

        addConnectionStatus() {
            // Connection status is already in the HTML, just update it
            const statusElement = document.getElementById('connection-status');
            if (statusElement) {
                statusElement.className = 'connection-status connecting';
                statusElement.innerHTML = '<div class="flex items-center justify-center space-x-2"><div class="w-2 h-2 bg-yellow-500 rounded-full animate-pulse"></div><span>Connecting...</span></div>';
            }
        }

        updateConnectionStatus(status) {
            const statusElement = document.getElementById('connection-status');
            if (statusElement) {
                statusElement.className = `connection-status ${status}`;
                switch (status) {
                    case 'connected':
                        statusElement.innerHTML = '<div class="flex items-center justify-center space-x-2"><div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div><span>Live Updates</span></div>';
                        break;
                    case 'disconnected':
                        statusElement.innerHTML = '<div class="flex items-center justify-center space-x-2"><div class="w-2 h-2 bg-red-500 rounded-full"></div><span>Offline</span></div>';
                        break;
                    case 'connecting':
                        statusElement.innerHTML = '<div class="flex items-center justify-center space-x-2"><div class="w-2 h-2 bg-yellow-500 rounded-full animate-pulse"></div><span>Connecting...</span></div>';
                        break;
                }
            }
        }
    }

    // Initialize live match updater when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        // Only initialize on pages with live matches
        const liveMatchCards = document.querySelectorAll('.live-match-card, [data-match-type="live"]');
        if (liveMatchCards.length > 0) {
            console.log('Found', liveMatchCards.length, 'live matches, starting updater');
            window.liveMatchUpdater = new LiveMatchUpdater();
        } else {
            console.log('No live matches found on page, skipping updater initialization');
        }
    });

    // Cleanup when page unloads
    window.addEventListener('beforeunload', function() {
        if (window.liveMatchUpdater) {
            window.liveMatchUpdater.stopUpdates();
        }
    });

</script>
@endsection

