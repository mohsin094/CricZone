@extends('layouts.app')

@section('title', 'Cricket Leagues & Tournaments - CricZone.pk')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6">


        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Cricket Leagues & Tournaments</h1>
            <p class="text-gray-600">Discover all cricket leagues, tournaments, and their current standings</p>
        </div>

        <!-- Search and Filter -->
        <div class="bg-white rounded-lg shadow-md border border-gray-200 p-6 mb-6">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search Leagues</label>
                    <input type="text" id="search" placeholder="Search by league name..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div class="md:w-48">
                    <label for="filter" class="block text-sm font-medium text-gray-700 mb-2">Filter by</label>
                    <select id="filter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">All Leagues</option>
                        <option value="international">International</option>
                        <option value="domestic">Domestic</option>
                        <option value="franchise">Franchise</option>
                        <option value="t20">T20</option>
                        <option value="odi">ODI</option>
                        <option value="test">Test</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Leagues Grid -->
        @if(!empty($leagues))
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-6" id="leagues-grid">
                @foreach($leagues as $league)
                    <div class="bg-white rounded-lg shadow-md border border-gray-200 hover:shadow-lg transition-shadow duration-200 league-card" data-name="{{ strtolower($league['league_name']) }}" data-type="{{ strtolower($league['league_name']) }}">
                        <div class="p-6">
                            <!-- League Header -->
                            <div class="text-center mb-4">
                                <div class="w-16 h-16 mx-auto mb-3 bg-gradient-to-br from-purple-500 to-blue-500 rounded-full flex items-center justify-center">
                                    <span class="text-xl font-bold text-white">{{ substr($league['league_name'], 0, 2) }}</span>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900">{{ $league['league_name'] }}</h3>
                                @if(isset($league['league_year']))
                                    <p class="text-sm text-gray-600">{{ $league['league_year'] }}</p>
                                @endif
                            </div>

                            <!-- League Stats -->
                            <div class="space-y-2 mb-4">
                                @if(isset($league['total_teams']))
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Teams:</span>
                                        <span class="font-medium text-gray-900">{{ $league['total_teams'] }}</span>
                                    </div>
                                @endif
                                
                                @if(isset($league['total_matches']))
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Matches:</span>
                                        <span class="font-medium text-gray-900">{{ $league['total_matches'] }}</span>
                                    </div>
                                @endif
                                
                                @if(isset($league['completed_matches']))
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Completed:</span>
                                        <span class="font-medium text-green-600">{{ $league['completed_matches'] }}</span>
                                    </div>
                                @endif
                                
                                @if(isset($league['upcoming_matches']))
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Upcoming:</span>
                                        <span class="font-medium text-blue-600">{{ $league['upcoming_matches'] }}</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Current Status -->
                            @if(isset($league['status']))
                                <div class="mb-4">
                                    <div class="text-sm text-gray-600 mb-2">Status:</div>
                                    @if($league['status'] === 'active')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <div class="w-2 h-2 bg-green-400 rounded-full mr-1"></div>
                                            Active
                                        </span>
                                    @elseif($league['status'] === 'upcoming')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <div class="w-2 h-2 bg-blue-400 rounded-full mr-1"></div>
                                            Upcoming
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <div class="w-2 h-2 bg-gray-400 rounded-full mr-1"></div>
                                            {{ ucfirst($league['status']) }}
                                        </span>
                                    @endif
                                </div>
                            @endif

                            <!-- Action Buttons -->
                            <div class="flex space-x-2">
                                <a href="{{ route('cricket.league-detail', $league['league_key']) }}" class="flex-1 bg-green-600 text-white text-center py-2 px-4 rounded-md hover:bg-green-700 transition-colors duration-200 text-sm font-medium">
                                    View Details
                                </a>
                                <button class="bg-gray-100 text-gray-700 py-2 px-3 rounded-md hover:bg-gray-200 transition-colors duration-200 text-sm" onclick="toggleLeagueInfo('{{ $league['league_key'] }}')">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Expandable League Info -->
                        <div id="league-info-{{ $league['league_key'] }}" class="hidden border-t border-gray-200 p-4 bg-gray-50">
                            <div class="space-y-3">
                                @if(isset($league['format']))
                                    <div class="text-sm">
                                        <span class="text-gray-600">Format:</span>
                                        <span class="font-medium text-gray-900 ml-2">{{ $league['format'] }}</span>
                                    </div>
                                @endif
                                
                                @if(isset($league['country']))
                                    <div class="text-sm">
                                        <span class="text-gray-600">Country:</span>
                                        <span class="font-medium text-gray-900 ml-2">{{ $league['country'] }}</span>
                                    </div>
                                @endif
                                
                                @if(isset($league['start_date']))
                                    <div class="text-sm">
                                        <span class="text-gray-600">Start Date:</span>
                                        <span class="font-medium text-gray-900 ml-2">{{ $league['start_date'] }}</span>
                                    </div>
                                @endif
                                
                                @if(isset($league['end_date']))
                                    <div class="text-sm">
                                        <span class="text-gray-600">End Date:</span>
                                        <span class="font-medium text-gray-900 ml-2">{{ $league['end_date'] }}</span>
                                    </div>
                                @endif
                                
                                @if(isset($league['prize_money']))
                                    <div class="text-sm">
                                        <span class="text-gray-600">Prize Money:</span>
                                        <span class="font-medium text-gray-900 ml-2">{{ $league['prize_money'] }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- No Leagues Found -->
            <div class="text-center py-12">
                <div class="text-gray-500 mb-4">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Leagues Found</h3>
                <p class="text-gray-500">We couldn't find any cricket leagues at the moment. Please try again later.</p>
            </div>
        @endif

        <!-- Load More Button -->
        @if(isset($hasMorePages) && $hasMorePages)
            <div class="text-center mt-8">
                <button class="bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700 transition-colors duration-200 font-medium">
                    Load More Leagues
                </button>
            </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search');
    const filterSelect = document.getElementById('filter');
    const leagueCards = document.querySelectorAll('.league-card');

    // Search functionality
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        filterLeagues(searchTerm, filterSelect.value);
    });

    // Filter functionality
    filterSelect.addEventListener('change', function() {
        const filterValue = this.value.toLowerCase();
        filterLeagues(searchInput.value.toLowerCase(), filterValue);
    });

    function filterLeagues(searchTerm, filterValue) {
        leagueCards.forEach(card => {
            const leagueName = card.dataset.name;
            const leagueType = card.dataset.type;
            
            const matchesSearch = leagueName.includes(searchTerm);
            const matchesFilter = !filterValue || leagueType.includes(filterValue);
            
            if (matchesSearch && matchesFilter) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }
});

function toggleLeagueInfo(leagueKey) {
    const leagueInfo = document.getElementById(`league-info-${leagueKey}`);
    if (leagueInfo.classList.contains('hidden')) {
        leagueInfo.classList.remove('hidden');
    } else {
        leagueInfo.classList.add('hidden');
    }
}
</script>
@endsection







