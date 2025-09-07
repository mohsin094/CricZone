@extends('layouts.app')

@section('title', 'Cricket Fixtures - Upcoming Matches - CricZone.pk')
@section('description', 'View upcoming cricket matches, fixtures, and schedules from various leagues and tournaments around the world.')

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
            <div class="text-gray-600 text-lg font-medium">Loading fixtures...</div>
            
            <!-- Animated dots -->
            <div class="flex space-x-1 mt-3">
                <div class="w-2 h-2 bg-green-500 rounded-full animate-bounce" style="animation-delay: 0ms;"></div>
                <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce" style="animation-delay: 150ms;"></div>
                <div class="w-2 h-2 bg-green-500 rounded-full animate-bounce" style="animation-delay: 300ms;"></div>
            </div>
        </div>
    </div>
</div>
<style>
    /* Custom scrollbar for dropdowns */
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    
    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 3px;
    }
    
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 3px;
    }
    
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
    
    /* Dropdown animations */
    .dropdown-enter {
        opacity: 0;
        transform: translateY(-10px);
    }
    
    .dropdown-enter-active {
        opacity: 1;
        transform: translateY(0);
        transition: opacity 200ms, transform 200ms;
    }
    
    .dropdown-exit {
        opacity: 1;
        transform: translateY(0);
    }
    
    .dropdown-exit-active {
        opacity: 0;
        transform: translateY(-10px);
        transition: opacity 200ms, transform 200ms;
    }
    
    /* Loading animations */
    .fade-in {
        animation: fadeIn 0.3s ease-in;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    .fade-out {
        animation: fadeOut 0.2s ease-out;
    }
    
    @keyframes fadeOut {
        from { opacity: 1; }
        to { opacity: 0; }
    }
</style>

<div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 pt-4">


    <!-- Header -->
    <!-- <div class="text-center mb-3 sm:mb-6">
        <h1 class="text-xl sm:text-3xl font-bold text-gray-900 mb-1 sm:mb-3">📅 Fixtures</h1>
        <p class="text-xs sm:text-lg text-gray-600">Upcoming matches & schedules</p>
    </div> -->

    <!-- Search Filter -->
    <div class="mb-4 sm:mb-6">
        <div class="bg-white rounded-lg shadow-md border border-gray-200 p-3 sm:p-4">
            <form method="GET" action="{{ route('cricket.fixtures') }}" id="searchForm">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                    <!-- Search Bar -->
                    <div class="relative">
                        <div class="relative">
                            <input type="text" name="search" id="searchInput" placeholder="🔍 Search teams, leagues, venues..."
                                value="{{ request('search') }}"
                                class="w-full px-2 sm:px-3 py-1.5 text-xs sm:text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 pr-8">
                            
                            <!-- Search Loading Indicator -->
                            
                        </div>
                        
                        @if(request('search'))
                        <button type="button" id="clearSearch" class="absolute inset-y-0 right-0 flex items-center pr-2 text-gray-400 hover:text-gray-600" style="right: 2rem;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                        @endif
                </div>
                
                <!-- League Filter -->
                    <div class="relative">
                        <div class="relative">
                            <input type="text" id="leagueSearch" placeholder="🏆 Search Leagues..."
                                class="w-full px-2 sm:px-3 py-1.5 text-xs sm:text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 pr-8"
                                autocomplete="off">
                            

                    </div>
                    
                        <div id="leagueDropdown"
                            class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-md shadow-lg max-h-48 overflow-y-auto custom-scrollbar hidden">
                            <div class="league-option px-3 py-2 text-sm text-gray-700 cursor-pointer hover:bg-green-100" data-value="">
                                🏆 All Leagues
                    </div>
                            @if(isset($leagues) && is_array($leagues))
                        @foreach($leagues as $league)
                            <div class="league-option px-3 py-2 text-sm text-gray-700 cursor-pointer hover:bg-green-100"
                                data-value="{{ $league }}">
                                🏏 {{ $league }}
                            </div>
                        @endforeach
                            @endif
                        </div>

                        <!-- Hidden field to hold actual selected value -->
                        <input type="hidden" name="league" id="leagueFilter" value="{{ request('league') }}">
                </div>
                
                <!-- Team Filter -->
                    <div class="relative">
                        <div class="relative">
                            <input type="text" id="teamSearch" placeholder="👥 Search Teams..."
                                class="w-full px-2 sm:px-3 py-1.5 text-xs sm:text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 pr-8"
                                autocomplete="off">
                            

                        </div>

                        <div id="teamDropdown"
                            class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-md shadow-lg max-h-48 overflow-y-auto custom-scrollbar hidden">
                            <div class="team-option px-3 py-2 text-sm text-gray-700 cursor-pointer hover:bg-green-100" data-value="">
                                👥 All Teams
                            </div>
                            @if(isset($teams) && is_array($teams))
                        @foreach($teams as $team)
                            <div class="team-option px-3 py-2 text-sm text-gray-700 cursor-pointer hover:bg-green-100"
                                data-value="{{ $team }}">
                                🏏 {{ $team }}
                            </div>
                        @endforeach
                        @endif
                        </div>

                        <!-- Hidden field to hold actual selected value -->
                        <input type="hidden" name="team" id="teamFilter" value="{{ request('team') }}">
                </div>
                
                <!-- Format Filter -->
                <div class="relative">
                    <div class="relative">
                        <input type="text" id="formatSearch" placeholder="🏏 Search Formats..."
                            class="w-full px-2 sm:px-3 py-1.5 text-xs sm:text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 pr-8"
                            autocomplete="off">
                    </div>

                    <div id="formatDropdown"
                        class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-md shadow-lg max-h-48 overflow-y-auto custom-scrollbar hidden">
                        <div class="format-option px-3 py-2 text-sm text-gray-700 cursor-pointer hover:bg-green-100" data-value="">
                            🏏 All Formats
                        </div>
                        @if(isset($formats) && is_array($formats))
                            @foreach($formats as $format)
                                <div class="format-option px-3 py-2 text-sm text-gray-700 cursor-pointer hover:bg-green-100"
                                    data-value="{{ $format }}">
                                    🏏 {{ $format }}
                                </div>
                            @endforeach
                        @endif
                    </div>

                    <!-- Hidden field to hold actual selected value -->
                    <input type="hidden" name="match_type" id="formatFilter" value="{{ request('match_type') }}">
                </div>
                        
                        </div>
                        
                <!-- Search Status -->
                <div id="searchStatus" class="text-xs text-gray-600 mt-2">
                    @if(request('search') || request('league') || request('team') || request('match_type'))
                    <div class="bg-blue-50 border border-blue-200 rounded p-2">
                        <p class="text-blue-800">
                            🔍 Search Results: {{ isset($totalMatches) ? $totalMatches : 0 }} matches found
                            @if(request('search'))
                            for "{{ request('search') }}"
                            @endif
                            @if(request('league'))
                            in {{ request('league') }}
                            @endif
                            @if(request('team'))
                            featuring {{ request('team') }}
                            @endif
                            @if(request('match_type'))
                            in {{ request('match_type') }} format
                            @endif
                        </p>
                        <a href="{{ route('cricket.fixtures') }}" class="text-blue-600 hover:text-blue-800 text-xs underline">Clear all filters</a>
                        </div>
                    @endif
                    </div>
                    
                <!-- Filter Status -->
                <div class="mt-2 text-center">
                    @if(request('league') || request('team') || request('match_type'))
                    <div class="text-xs text-green-600">
                        🔍 Active Filters:
                        @if(request('league')) League: {{ request('league') }} @endif
                        @if(request('league') && (request('team') || request('match_type'))) | @endif
                        @if(request('team')) Team: {{ request('team') }} @endif
                        @if(request('team') && request('match_type')) | @endif
                        @if(request('match_type')) Format: {{ request('match_type') }} @endif
                    </div>
                    @endif
                </div>
            </form>
            </div>
    </div>

    <!-- Fixtures -->
    @if(isset($upcomingMatches) && is_array($upcomingMatches) && !empty($upcomingMatches))
    
    <!-- Fixtures Content -->
    <div id="fixturesContent">
    
    <!-- Loading Overlay -->
    <div id="fixturesLoader" class="hidden absolute inset-0 bg-white bg-opacity-90 z-50 flex items-center justify-center">
        <div class="text-center">
            <div class="inline-flex items-center px-6 py-4 bg-blue-50 border border-blue-200 rounded-lg shadow-lg">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mr-4"></div>
                <span class="text-blue-800 font-medium text-lg">Loading fixtures...</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-3 gap-2 sm:gap-3 lg:gap-4">
        @if(isset($paginatedMatches) && is_array($paginatedMatches))
        @foreach($paginatedMatches as $match)
        @include('cricket.partials.match-card', ['match' => $match, 'type' => 'upcoming'])
        @endforeach
        @else
        <div class="col-span-2 lg:col-span-3 text-center py-8">
            <p class="text-gray-500">No matches available for the current page.</p>
        </div>
                @endif
    </div>
    </div> <!-- End fixturesContent -->
 
    <!-- Pagination Navigation -->
    @if(isset($totalPages) && $totalPages > 1)
    <div class="mt-8 flex justify-center">
        <nav class="flex flex-wrap items-center justify-center gap-2">
            <!-- Previous Page -->
            @if(($currentPage ?? 1) > 1)
            <a href="{{ request()->fullUrlWithQuery(['page' => ($currentPage ?? 1) - 1]) }}"
                class="px-2 sm:px-3 py-2 text-xs sm:text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 hover:text-gray-700">
                    ← Previous
                </a>
            @endif
            
            <!-- Page Numbers -->
            @for($page = max(1, ($currentPage ?? 1) - 2); $page <= min($totalPages, ($currentPage ?? 1) + 2); $page++)
                @if($page==($currentPage ?? 1))
                <span class="px-2 sm:px-3 py-2 text-xs sm:text-sm font-medium text-white bg-green-600 border border-green-600 rounded-md">
                {{ $page }}
                    </span>
                @else
                <a href="{{ request()->fullUrlWithQuery(['page' => $page]) }}"
                    class="px-2 sm:px-3 py-2 text-xs sm:text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 hover:text-gray-700">
                    {{ $page }}
                    </a>
                @endif
            @endfor
            
                <!-- Next Page -->
                @if(($currentPage ?? 1) < $totalPages)
                    <a href="{{ request()->fullUrlWithQuery(['page' => ($currentPage ?? 1) + 1]) }}"
                    class="px-2 sm:px-3 py-2 text-xs sm:text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 hover:text-gray-700">
                    Next →
                </a>
            @endif
        </nav>
    </div>
    @endif
    
    @else
    <!-- No Fixtures or Error -->
    <div class="text-center py-8 sm:py-16">
        @if(!isset($upcomingMatches))
        <div class="text-6xl sm:text-8xl mb-4 sm:mb-6">⚠️</div>
        <h2 class="text-xl sm:text-2xl font-semibold text-gray-700 mb-3 sm:mb-4">Data Loading Error</h2>
        <p class="text-sm sm:text-base text-gray-500 mb-4 sm:mb-6">Unable to load fixtures data. Please try again later.</p>
        @elseif(empty($upcomingMatches))
            <div class="text-6xl sm:text-8xl mb-4 sm:mb-6">📅</div>
            <h2 class="text-xl sm:text-2xl font-semibold text-gray-700 mb-3 sm:mb-4">No Upcoming Fixtures</h2>
            <p class="text-sm sm:text-base text-gray-500 mb-4 sm:mb-6">There are currently no upcoming cricket matches scheduled. Check back later!</p>
        @endif
            
            <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 justify-center">
                <a href="{{ route('cricket.index') }}" class="bg-green-600 text-white px-4 sm:px-6 py-2 sm:py-3 rounded-md hover:bg-green-700 transition-colors text-sm sm:text-base">
                    🏠 Back to Home
                </a>
            </div>
    </div>
    @endif

    <!-- Ad Space -->
    <!-- <div class="mt-8 sm:mt-12 bg-gray-100 rounded-lg p-4 sm:p-8 text-center">
        <div class="text-gray-500">
            <p class="text-xs sm:text-sm mb-2">Advertisement</p>
            <div class="w-full h-24 sm:h-32 bg-gray-200 rounded flex items-center justify-center">
                <span class="text-gray-400 text-sm sm:text-base">Ad Space</span>
            </div>
        </div>
    </div>
</div> -->

    <script>
        // Loader functionality for fixtures - Define globally
        function showFixturesLoader() {
            const loader = document.getElementById('fixturesLoader');
            const content = document.getElementById('fixturesContent');
            if (loader && content) {
                loader.classList.remove('hidden');
                content.classList.add('hidden');
            }
        }
        
        function hideFixturesLoader() {
            const loader = document.getElementById('fixturesLoader');
            const content = document.getElementById('fixturesContent');
            if (loader && content) {
                loader.classList.add('hidden');
                content.classList.remove('hidden');
            }
        }

        // Update search status for search operations
        function updateSearchStatus(searchTerm, visibleCount, totalCount) {
            const searchStatus = document.getElementById('searchStatus');
            if (searchStatus) {
                if (searchTerm === 'typing') {
                    // Show typing indicator
                    searchStatus.innerHTML = `
                        <div class="bg-blue-50 border border-blue-200 rounded p-2">
                            <p class="text-blue-800">
                                🔍 Searching... Please wait
                            </p>
                        </div>
                    `;
                } else if (searchTerm && searchTerm.trim() !== '') {
                    // Show search results
                    searchStatus.innerHTML = `
                        <div class="bg-blue-50 border border-blue-200 rounded p-2">
                            <p class="text-blue-800">
                                🔍 Search Results: ${visibleCount} of ${totalCount} matches found
                                for "${searchTerm}"
                            </p>
                            <button onclick="clearClientSearch()" class="text-blue-600 hover:text-blue-800 text-xs underline mt-1">
                                Clear search
                            </button>
                        </div>
                    `;
                } else {
                    searchStatus.innerHTML = '';
                }
            }
        }

        // Clear client-side search - Global function
        window.clearClientSearch = function() {
            const searchInput = document.getElementById('searchInput');
            const searchStatus = document.getElementById('searchStatus');
            
            if (searchInput) searchInput.value = '';
            if (searchStatus) searchStatus.innerHTML = '';
            
            // Redirect to fixtures page without search parameters
            window.location.href = '{{ route("cricket.fixtures") }}';
        };

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
        
        // Auto-submit form when filters change
        document.addEventListener('DOMContentLoaded', function() {
        const searchForm = document.getElementById('searchForm');

        const leagueSearch = document.getElementById('leagueSearch');
        const teamSearch = document.getElementById('teamSearch');
        const formatSearch = document.getElementById('formatSearch');
        const leagueDropdown = document.getElementById('leagueDropdown');
        const teamDropdown = document.getElementById('teamDropdown');
        const formatDropdown = document.getElementById('formatDropdown');
        const leagueFilter = document.getElementById('leagueFilter');
        const teamFilter = document.getElementById('teamFilter');
        const formatFilter = document.getElementById('formatFilter');

        // Toggle dropdowns on focus
        leagueSearch.addEventListener('focus', () => leagueDropdown.classList.remove('hidden'));
        teamSearch.addEventListener('focus', () => teamDropdown.classList.remove('hidden'));
        formatSearch.addEventListener('focus', () => formatDropdown.classList.remove('hidden'));

        // Filter league options
        leagueSearch.addEventListener('input', function() {
            const term = this.value.toLowerCase();
            leagueDropdown.querySelectorAll('.league-option').forEach(option => {
                option.style.display = option.textContent.toLowerCase().includes(term) ? 'block' : 'none';
            });
        });

        // Filter team options
        teamSearch.addEventListener('input', function() {
            const term = this.value.toLowerCase();
            teamDropdown.querySelectorAll('.team-option').forEach(option => {
                option.style.display = option.textContent.toLowerCase().includes(term) ? 'block' : 'none';
            });
        });

        // Filter format options
        formatSearch.addEventListener('input', function() {
            const term = this.value.toLowerCase();
            formatDropdown.querySelectorAll('.format-option').forEach(option => {
                option.style.display = option.textContent.toLowerCase().includes(term) ? 'block' : 'none';
            });
        });

        // Select league
        leagueDropdown.addEventListener('click', function(e) {
            const option = e.target.closest('.league-option');
            if (!option) return;
            leagueSearch.value = option.textContent.trim();
            leagueFilter.value = option.dataset.value;
            leagueDropdown.classList.add('hidden');
            searchForm.submit();
        });

        // Select team
        teamDropdown.addEventListener('click', function(e) {
            const option = e.target.closest('.team-option');
            if (!option) return;
            teamSearch.value = option.textContent.trim();
            teamFilter.value = option.dataset.value;
            teamDropdown.classList.add('hidden');
            searchForm.submit();
        });

        // Select format
        formatDropdown.addEventListener('click', function(e) {
            const option = e.target.closest('.format-option');
            if (!option) return;
            formatSearch.value = option.textContent.trim();
            formatFilter.value = option.dataset.value;
            formatDropdown.classList.add('hidden');
            searchForm.submit();
        });

                 // Close dropdowns on outside click
         document.addEventListener('click', function(e) {
             if (!leagueSearch.contains(e.target) && !leagueDropdown.contains(e.target)) {
                 leagueDropdown.classList.add('hidden');
             }
             if (!teamSearch.contains(e.target) && !teamDropdown.contains(e.target)) {
                 teamDropdown.classList.add('hidden');
             }
             if (!formatSearch.contains(e.target) && !formatDropdown.contains(e.target)) {
                 formatDropdown.classList.add('hidden');
             }
         });
         
 
         
                            // Show loader for form submissions (but not for main search)
         let isMainSearchSubmitting = false;
         searchForm.addEventListener('submit', function(e) {
             // Only show loader if it's not from the main search
             if (!isMainSearchSubmitting) {
                 showFixturesLoader();
             }
         });
         
         // Show loader on dropdown selection
         leagueDropdown.addEventListener('click', function(e) {
             const option = e.target.closest('.league-option');
             if (option) {
                 showFixturesLoader();
             }
         });
         
         teamDropdown.addEventListener('click', function(e) {
             const option = e.target.closest('.team-option');
             if (option) {
                 showFixturesLoader();
             }
         });

         // Real-time main search (fixtures search bar at top) - Server-side search with delay
         const searchInput = document.getElementById('searchInput');
         let searchTimeout;
         if (searchInput) {
             searchInput.addEventListener('input', function() {
                 const searchTerm = this.value;
                 
                 // Clear previous timeout
                 clearTimeout(searchTimeout);
                 
                 // Show typing indicator (optional)
                 updateSearchStatus(searchTerm, 'typing', 'typing');
                 
                 // Submit to server after user stops typing (500ms delay)
                 searchTimeout = setTimeout(() => {
                     if (searchTerm.trim() !== '') {
                         // Set flag to prevent loader for main search
                         isMainSearchSubmitting = true;
                         // Submit form to get results from all pages
                         searchForm.submit();
                         // Reset flag after a short delay
                         setTimeout(() => {
                             isMainSearchSubmitting = false;
                         }, 100);
                     } else {
                         // If search is empty, clear and show all results
                         clearClientSearch();
                     }
                 }, 500);
             });
         }

         // Real-time league search (submit on typing, not just select)
         let leagueSearchTimeout;
         leagueSearch.addEventListener('input', function() {
             clearTimeout(leagueSearchTimeout);
             leagueSearchTimeout = setTimeout(() => {
                 leagueFilter.value = leagueSearch.value;
                 showFixturesLoader(); // Show fixtures loader
                 searchForm.submit();
             }, 500);
         });

         // Real-time team search (submit on typing, not just select)
         let teamSearchTimeout;
         teamSearch.addEventListener('input', function() {
             clearTimeout(teamSearchTimeout);
             teamSearchTimeout = setTimeout(() => {
                 teamFilter.value = teamSearch.value;
                 showFixturesLoader(); // Show fixtures loader
                 searchForm.submit();
             }, 500);
         });

         // Clear search functionality
         const clearSearchBtn = document.getElementById('clearSearch');
         if (clearSearchBtn) {
             clearSearchBtn.addEventListener('click', function() {
                 searchInput.value = '';
                 // Set flag to prevent loader for clear search
                 isMainSearchSubmitting = true;
                 // Submit form to show all results
                 searchForm.submit();
                 // Reset flag after a short delay
                 setTimeout(() => {
                     isMainSearchSubmitting = false;
                 }, 100);
             });
         }
     });


</script>
@endsection