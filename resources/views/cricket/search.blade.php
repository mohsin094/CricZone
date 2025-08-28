@extends('layouts.app')

@section('title', 'Search Cricket - CricZone.pk')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6">


        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Search Cricket</h1>
            <p class="text-gray-600">Find matches, teams, players, and leagues across all cricket data</p>
        </div>

        <!-- Search Form -->
        <div class="bg-white rounded-lg shadow-md border border-gray-200 p-6 mb-6">
            <form method="GET" action="{{ route('cricket.search') }}" class="space-y-4">
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <label for="q" class="block text-sm font-medium text-gray-700 mb-2">Search Query</label>
                        <input type="text" id="q" name="q" value="{{ request('q') }}" placeholder="Search for matches, teams, players, leagues..." class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-lg">
                    </div>
                    <div class="md:w-48">
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Search Type</label>
                        <select id="type" name="type" class="w-full px-3 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="all" {{ request('type') === 'all' ? 'selected' : '' }}>All</option>
                            <option value="matches" {{ request('type') === 'matches' ? 'selected' : '' }}>Matches</option>
                            <option value="teams" {{ request('type') === 'teams' ? 'selected' : '' }}>Teams</option>
                            <option value="players" {{ request('type') === 'players' ? 'selected' : '' }}>Players</option>
                            <option value="leagues" {{ request('type') === 'leagues' ? 'selected' : '' }}>Leagues</option>
                        </select>
                    </div>
                    <div class="md:w-48">
                        <label for="date" class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
                        <select id="date" name="date" class="w-full px-3 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="all" {{ request('date') === 'all' ? 'selected' : '' }}>All Time</option>
                            <option value="today" {{ request('date') === 'today' ? 'selected' : '' }}>Today</option>
                            <option value="week" {{ request('date') === 'week' ? 'selected' : '' }}>This Week</option>
                            <option value="month" {{ request('date') === 'month' ? 'selected' : '' }}>This Month</option>
                            <option value="year" {{ request('date') === 'year' ? 'selected' : '' }}>This Year</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-center">
                    <button type="submit" class="bg-blue-600 text-white px-8 py-3 rounded-md hover:bg-blue-700 transition-colors duration-200 font-medium text-lg">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Search
                    </button>
                </div>
            </form>
        </div>

        <!-- Search Results -->
        @if(request('q'))
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-gray-900">
                        Search Results for "{{ request('q') }}"
                    </h2>
                    <div class="text-sm text-gray-600">
                        @if(isset($totalResults))
                            {{ $totalResults }} results found
                        @endif
                    </div>
                </div>
            </div>

            <!-- Results Tabs -->
            @if(isset($results) && !empty($results))
                <div class="bg-white rounded-lg shadow-md border border-gray-200 mb-6">
                    <div class="border-b border-gray-200">
                        <nav class="flex space-x-8 px-6" aria-label="Tabs">
                            @if(isset($results['matches']) && !empty($results['matches']))
                                <button class="border-b-2 border-blue-500 py-4 px-1 text-sm font-medium text-blue-600" id="matches-tab">
                                    Matches ({{ count($results['matches']) }})
                                </button>
                            @endif
                            @if(isset($results['teams']) && !empty($results['teams']))
                                <button class="border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300" id="teams-tab">
                                    Teams ({{ count($results['teams']) }})
                                </button>
                            @endif
                            @if(isset($results['players']) && !empty($results['players']))
                                <button class="border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300" id="players-tab">
                                    Players ({{ count($results['players']) }})
                                </button>
                            @endif
                            @if(isset($results['leagues']) && !empty($results['leagues']))
                                <button class="border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300" id="leagues-tab">
                                    Leagues ({{ count($results['leagues']) }})
                                </button>
                            @endif
                        </nav>
                    </div>

                    <div class="p-6">
                        <!-- Matches Tab -->
                        @if(isset($results['matches']) && !empty($results['matches']))
                            <div id="matches-content" class="tab-content">
                                <div class="space-y-4">
                                    @foreach($results['matches'] as $match)
                                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow duration-200">
                                            <div class="flex items-center justify-between">
                                                <div class="flex-1">
                                                    <div class="flex items-center space-x-4 mb-2">
                                                        @if($match['event_live'])
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                                <div class="w-2 h-2 bg-red-400 rounded-full mr-1 animate-pulse"></div>
                                                                LIVE
                                                            </span>
                                                        @endif
                                                        <span class="text-sm text-gray-500">{{ $match['league_name'] ?? 'Cricket Match' }}</span>
                                                    </div>
                                                    <h3 class="text-lg font-semibold text-gray-900 mb-1">
                                                        {{ $match['event_home_team'] }} vs {{ $match['event_away_team'] }}
                                                    </h3>
                                                    <div class="text-sm text-gray-600">
                                                        {{ $match['event_date_start'] ?? 'TBD' }}
                                                        @if(isset($match['event_time']))
                                                            at {{ $match['event_time'] }}
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="text-right">
                                                    <a href="{{ route('cricket.match-detail', $match['event_key']) }}" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition-colors duration-200 text-sm font-medium">
                                                        View Details
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Teams Tab -->
                        @if(isset($results['teams']) && !empty($results['teams']))
                            <div id="teams-content" class="tab-content hidden">
                                <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
                                    @foreach($results['teams'] as $team)
                                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow duration-200">
                                            <div class="text-center">
                                                @if(isset($team['team_logo']) && $team['team_logo'])
                                                    <img src="{{ $team['team_logo'] }}" alt="{{ $team['team_name'] }}" class="w-16 h-16 mx-auto mb-3 rounded-full border-2 border-gray-200">
                                                @else
                                                    <div class="w-16 h-16 mx-auto mb-3 bg-gradient-to-br from-blue-500 to-green-500 rounded-full flex items-center justify-center">
                                                        <span class="text-lg font-bold text-white">{{ substr($team['team_name'], 0, 2) }}</span>
                                                    </div>
                                                @endif
                                                <h3 class="font-semibold text-gray-900 mb-2">{{ $team['team_name'] }}</h3>
                                                <a href="{{ route('cricket.team-detail', $team['team_key']) }}" class="bg-green-600 text-white px-3 py-1 rounded-md hover:bg-green-700 transition-colors duration-200 text-sm">
                                                    View Team
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Players Tab -->
                        @if(isset($results['players']) && !empty($results['players']))
                            <div id="players-content" class="tab-content hidden">
                                <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
                                    @foreach($results['players'] as $player)
                                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow duration-200">
                                            <div class="text-center">
                                                <div class="w-16 h-16 mx-auto mb-3 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full flex items-center justify-center">
                                                    <span class="text-lg font-bold text-white">{{ substr($player['name'] ?? 'Player', 0, 2) }}</span>
                                                </div>
                                                <h3 class="font-semibold text-gray-900 mb-1">{{ $player['name'] ?? 'Unknown Player' }}</h3>
                                                <p class="text-sm text-gray-600 mb-2">{{ $player['team'] ?? 'Unknown Team' }}</p>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    {{ $player['role'] ?? 'Player' }}
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Leagues Tab -->
                        @if(isset($results['leagues']) && !empty($results['leagues']))
                            <div id="leagues-content" class="tab-content hidden">
                                <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
                                    @foreach($results['leagues'] as $league)
                                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow duration-200">
                                            <div class="text-center">
                                                <div class="w-16 h-16 mx-auto mb-3 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-full flex items-center justify-center">
                                                    <span class="text-lg font-bold text-white">{{ substr($league['league_name'], 0, 2) }}</span>
                                                </div>
                                                <h3 class="font-semibold text-gray-900 mb-1">{{ $league['league_name'] }}</h3>
                                                <p class="text-sm text-gray-600 mb-2">{{ $league['league_year'] ?? '' }}</p>
                                                <a href="{{ route('cricket.league-detail', $league['league_key']) }}" class="bg-green-600 text-white px-3 py-1 rounded-md hover:bg-green-700 transition-colors duration-200 text-sm">
                                                    View League
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <!-- No Results -->
                <div class="text-center py-12">
                    <div class="text-gray-500 mb-4">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No Results Found</h3>
                    <p class="text-gray-500">We couldn't find any results for "{{ request('q') }}". Try different keywords or search terms.</p>
                </div>
            @endif
        @else
            <!-- Search Suggestions -->
            <div class="bg-white rounded-lg shadow-md border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Popular Searches</h3>
                <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
                    <a href="{{ route('cricket.search', ['q' => 'IPL 2024']) }}" class="block p-4 border border-gray-200 rounded-lg hover:shadow-md transition-shadow duration-200">
                        <div class="text-center">
                            <div class="w-12 h-12 mx-auto mb-2 bg-gradient-to-br from-orange-500 to-red-500 rounded-full flex items-center justify-center">
                                <span class="text-white font-bold">IPL</span>
                            </div>
                            <span class="text-sm font-medium text-gray-900">IPL 2024</span>
                        </div>
                    </a>
                    <a href="{{ route('cricket.search', ['q' => 'Pakistan vs India']) }}" class="block p-4 border border-gray-200 rounded-lg hover:shadow-md transition-shadow duration-200">
                        <div class="text-center">
                            <div class="w-12 h-12 mx-auto mb-2 bg-gradient-to-br from-green-500 to-blue-500 rounded-full flex items-center justify-center">
                                <span class="text-white font-bold">PAK</span>
                            </div>
                            <span class="text-sm font-medium text-gray-900">Pakistan vs India</span>
                        </div>
                    </a>
                    <a href="{{ route('cricket.search', ['q' => 'T20 World Cup']) }}" class="block p-4 border border-gray-200 rounded-lg hover:shadow-md transition-shadow duration-200">
                        <div class="text-center">
                            <div class="w-12 h-12 mx-auto mb-2 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full flex items-center justify-center">
                                <span class="text-white font-bold">T20</span>
                            </div>
                            <span class="text-sm font-medium text-gray-900">T20 World Cup</span>
                        </div>
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('[id$="-tab"]');
    const contents = document.querySelectorAll('.tab-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetId = this.id.replace('-tab', '-content');
            
            // Hide all contents
            contents.forEach(content => content.classList.add('hidden'));
            
            // Remove active state from all tabs
            tabs.forEach(t => {
                t.classList.remove('border-blue-500', 'text-blue-600');
                t.classList.add('border-transparent', 'text-gray-500');
            });
            
            // Show target content and activate tab
            document.getElementById(targetId).classList.remove('hidden');
            this.classList.remove('border-transparent', 'text-gray-500');
            this.classList.add('border-blue-500', 'text-blue-600');
        });
    });
});
</script>
@endsection







