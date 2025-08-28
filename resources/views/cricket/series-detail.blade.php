@extends('layouts.app')

@section('title', ($seriesData['series']['series_name'] ?? $seriesData['series']['league_name'] ?? 'Series') . ' - CricZone.pk')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">


        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">
                        {{ $seriesData['series']['series_name'] ?? $seriesData['series']['league_name'] ?? 'Unknown Series' }}
                    </h1>
                    <p class="text-gray-600">
                        {{ $seriesData['series']['series_year'] ?? $seriesData['series']['league_year'] ?? 'N/A' }} • 
                        {{ $seriesData['summary']['series_type'] }} • 
                        <span class="font-medium">{{ $seriesData['summary']['series_status'] }}</span>
                    </p>
                </div>
                <div class="text-right">
                    <a href="{{ route('cricket.series') }}" class="text-indigo-600 hover:text-indigo-800 font-medium">
                        ← Back to Series
                    </a>
                </div>
            </div>
        </div>

        <!-- Series Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                            <span class="text-blue-600 text-sm">🏏</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Matches</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $seriesData['summary']['total_matches'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                            <span class="text-green-600 text-sm">✅</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Completed</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $seriesData['summary']['completed_matches'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                            <span class="text-red-600 text-sm">🟢</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Live</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $seriesData['summary']['live_matches'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                            <span class="text-yellow-600 text-sm">📅</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Upcoming</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $seriesData['summary']['upcoming_matches'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Teams Participating -->
        @if(!empty($seriesData['teams']))
        <div class="bg-white rounded-lg shadow mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800">Teams Participating</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach($seriesData['teams'] as $team)
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <div class="w-16 h-16 mx-auto mb-3 bg-blue-100 rounded-full flex items-center justify-center">
                            <span class="text-blue-600 font-semibold text-lg">{{ substr($team, 0, 2) }}</span>
                        </div>
                        <h3 class="font-medium text-gray-900">{{ $team }}</h3>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Tabs Navigation -->
        <div class="bg-white rounded-lg shadow mb-8">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                    <button onclick="showTab('matches')" class="tab-button active border-indigo-500 text-indigo-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        All Matches
                    </button>
                    <button onclick="showTab('results')" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Results
                    </button>
                    <button onclick="showTab('standings')" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Standings
                    </button>
                    <button onclick="showTab('stats')" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Statistics
                    </button>
                </nav>
            </div>

            <!-- Tab Content -->
            <div class="p-6">
                <!-- All Matches Tab -->
                <div id="matches" class="tab-content">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">All Matches</h3>
                    @if(empty($seriesData['matches']))
                        <p class="text-gray-500 text-center py-8">No matches found for this series.</p>
                    @else
                        <div class="space-y-4">
                            @foreach($seriesData['matches'] as $match)
                            <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-4">
                                            <div class="flex items-center space-x-2">
                                                <img src="{{ $match['event_home_team_logo'] ?? '' }}" alt="{{ $match['event_home_team'] }}" class="w-8 h-8 rounded-full">
                                                <span class="font-medium">{{ $match['event_home_team'] }}</span>
                                            </div>
                                            <span class="text-gray-400">vs</span>
                                            <div class="flex items-center space-x-2">
                                                <span class="font-medium">{{ $match['event_away_team'] }}</span>
                                                <img src="{{ $match['event_away_team_logo'] ?? '' }}" alt="{{ $match['event_away_team'] }}" class="w-8 h-8 rounded-full">
                                            </div>
                                        </div>
                                        <div class="mt-2 text-sm text-gray-600">
                                            {{ $match['event_date_start'] ?? 'N/A' }} • {{ $match['event_time'] ?? 'N/A' }}
                                            @if($match['event_stadium'])
                                                • {{ $match['event_stadium'] }}
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="mb-2">
                                            @php
                                                $status = $match['event_status'] ?? 'Unknown';
                                                $statusColors = [
                                                    'Live' => 'bg-red-100 text-red-800',
                                                    'Finished' => 'bg-green-100 text-green-800',
                                                    'Upcoming' => 'bg-blue-100 text-blue-800',
                                                    'Cancelled' => 'bg-gray-100 text-gray-800'
                                                ];
                                                $statusColor = $statusColors[$status] ?? 'bg-gray-100 text-gray-800';
                                            @endphp
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                                {{ $status }}
                                            </span>
                                        </div>
                                        @if($match['event_status'] === 'Finished' && isset($match['event_status_info']))
                                            <div class="text-sm text-gray-600">{{ $match['event_status_info'] }}</div>
                                        @endif
                                        @if($match['event_status'] === 'Live')
                                            <div class="text-sm font-medium text-red-600">
                                                {{ $match['event_service_home'] ?? '0/0' }} - {{ $match['event_service_away'] ?? '0/0' }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                @if($match['event_key'])
                                <div class="mt-3 pt-3 border-t border-gray-200">
                                    <a href="{{ route('cricket.match-detail', $match['event_key']) }}" 
                                       class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                                        View Match Details →
                                    </a>
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Results Tab -->
                <div id="results" class="tab-content hidden">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Match Results</h3>
                    @if(empty($seriesData['results']))
                        <p class="text-gray-500 text-center py-8">No completed matches found for this series.</p>
                    @else
                        <div class="space-y-4">
                            @foreach($seriesData['results'] as $result)
                            <div class="border border-gray-200 rounded-lg p-4 bg-green-50">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-4">
                                            <div class="flex items-center space-x-2">
                                                <span class="font-medium">{{ $result['event_home_team'] ?? 'N/A' }}</span>
                                                <span class="text-lg">{{ $result['event_home_final_result'] ?? '0' }}</span>
                                            </div>
                                            <span class="text-gray-400">vs</span>
                                            <div class="flex items-center space-x-2">
                                                <span class="text-lg">{{ $result['event_away_final_result'] ?? '0' }}</span>
                                                <span class="font-medium">{{ $result['event_away_team'] ?? 'N/A' }}</span>
                                            </div>
                                        </div>
                                        <div class="mt-2 text-sm text-gray-600">
                                            {{ $result['event_date_start'] ?? 'N/A' }}
                                            @if($result['event_stadium'])
                                                • {{ $result['event_stadium'] }}
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm text-gray-600 mb-2">{{ $result['event_status_info'] ?? 'Match completed' }}</div>
                                        @if($result['event_man_of_match'])
                                        <div class="text-sm font-medium text-green-600">
                                            🏆 {{ $result['event_man_of_match'] }}
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Standings Tab -->
                <div id="standings" class="tab-content hidden">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Series Standings</h3>
                    @if(empty($seriesData['standings']))
                        <p class="text-gray-500 text-center py-8">No standings available for this series.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Team</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">P</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">W</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">L</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NR</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pts</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NRR</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($seriesData['standings'] as $standing)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $standing['standing_place'] ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $standing['standing_team'] ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $standing['standing_MP'] ?? '0' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $standing['standing_W'] ?? '0' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $standing['standing_L'] ?? '0' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $standing['standing_NR'] ?? '0' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $standing['standing_Pts'] ?? '0' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $standing['standing_NRR'] ?? 'N/A' }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <!-- Statistics Tab -->
                <div id="stats" class="tab-content hidden">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Series Statistics</h3>
                    @if(empty($seriesData['stats']))
                        <p class="text-gray-500 text-center py-8">No statistics available for this series.</p>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach($seriesData['stats'] as $statType => $statData)
                            <div class="bg-gray-50 rounded-lg p-4">
                                <h4 class="font-medium text-gray-900 mb-3">{{ ucfirst(str_replace('_', ' ', $statType)) }}</h4>
                                @if(is_array($statData))
                                    <div class="space-y-2">
                                        @foreach($statData as $key => $value)
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                                            <span class="font-medium">{{ $value }}</span>
                                        </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-gray-600">{{ $statData }}</p>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Navigation Links -->
        <div class="mt-8 text-center">
            <a href="{{ route('cricket.series') }}" class="text-indigo-600 hover:text-indigo-800 font-medium">
                ← Back to All Series
            </a>
        </div>
    </div>
</div>

<script>
function showTab(tabName) {
    // Hide all tab contents
    const tabContents = document.querySelectorAll('.tab-content');
    tabContents.forEach(content => content.classList.add('hidden'));
    
    // Remove active class from all tab buttons
    const tabButtons = document.querySelectorAll('.tab-button');
    tabButtons.forEach(button => {
        button.classList.remove('active', 'border-indigo-500', 'text-indigo-600');
        button.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Show selected tab content
    document.getElementById(tabName).classList.remove('hidden');
    
    // Add active class to selected tab button
    event.target.classList.add('active', 'border-indigo-500', 'text-indigo-600');
    event.target.classList.remove('border-transparent', 'text-gray-500');
}
</script>
@endsection






