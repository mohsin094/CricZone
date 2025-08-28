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
    background-color: #10b981;
        border-radius: 50%;
    margin-right: 4px;
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

<div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 pt-2">


    <!-- Header -->
    <div class="text-center mb-3 sm:mb-6">
        <!-- <h1 class="text-xl sm:text-3xl font-bold text-gray-900 mb-1 sm:mb-3">🏏 Welcome to CricZone.pk</h1> -->
        <p class="text-xs sm:text-lg text-gray-600">All cricket in one zone - Live scores, updates, and more!</p>
    </div>

        <!-- Search Filter -->
    <div class="mb-4 sm:mb-6">
        <div class="bg-white rounded-lg shadow-md border border-gray-200 p-3 sm:p-4">
            <div class="grid grid-cols-1 gap-3 sm:gap-4">
                <!-- Search Bar -->
                <div class="relative">
                    <input type="text" id="searchInput" placeholder="🔍 Search teams, leagues, venues..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-sm">
                </div>
            </div>
            
            <!-- Search Status -->
            <div id="searchStatus" class="text-sm text-gray-600 mt-2" style="display: none;"></div>
        </div>
    </div>
        
        <!-- Tabs -->
    <div class="mb-4 sm:mb-8">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-2 sm:space-x-8" aria-label="Tabs">
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
            </nav>
        </div>
    </div>
                    
    <!-- Tab Content -->
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
                $perPage = 12;
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
                $perPage = 12;
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
            $perPage = 12;
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
            $currentPageUpcomingMatches = $sortedUpcomingMatches->take(12);
        @endphp
        
        <div class="mb-4 sm:mb-8">
            <div class="flex items-center justify-between mb-3 sm:mb-6">
                <h2 class="text-xl sm:text-2xl font-bold text-gray-900">⏰ Upcoming Matches</h2>
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 bg-purple-500 rounded-full"></div>
                    <span class="text-sm text-gray-600">Next 7 days</span>
                    <span class="text-sm text-gray-500">(Showing 12 of {{ $sortedUpcomingMatches->count() }} matches)</span>
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
            <p class="mt-2 text-sm text-gray-600">See all {{ $sortedUpcomingMatches->count() }} upcoming matches for the next 30 days</p>
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
                const targetTab = this.id.replace('tab-', 'tab-content-');
                
                // Update active tab button
                tabButtons.forEach(btn => {
                    btn.classList.remove('active', 'border-green-500', 'text-green-600');
                    btn.classList.add('border-transparent', 'text-gray-500');
                });
                this.classList.add('active', 'border-green-500', 'text-green-600');
                this.classList.remove('border-transparent', 'text-gray-500');
                
                // Show target tab content
                tabContents.forEach(content => {
                    content.classList.add('hidden');
                    content.classList.remove('active');
                });
                document.getElementById(targetTab).classList.remove('hidden');
                document.getElementById(targetTab).classList.add('active');
                
                // Apply filters to current tab
                filterMatches();
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

