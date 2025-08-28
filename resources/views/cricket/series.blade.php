@extends('layouts.app')

@section('title', 'All Cricket Series - CricZone.pk')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6">


        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">🏏 All Cricket Series</h1>
                    <p class="text-gray-600">Comprehensive view of all cricket series, matches, and standings</p>
                </div>
                <div class="flex space-x-3">
                    <button id="toggleView" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors text-sm font-medium">
                        Toggle View
                    </button>
                    <a href="{{ route('cricket.series', ['show_all' => 1]) }}" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors text-sm font-medium">
                        Show All API Data
                    </a>
                    <a href="{{ route('cricket.test-api') }}" class="bg-yellow-600 text-white px-4 py-2 rounded-lg hover:bg-yellow-700 transition-colors text-sm font-medium">
                        Test API
                    </a>
                    <a href="{{ route('cricket.series') }}" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors text-sm font-medium">
                        Refresh Data
                    </a>
                    <button onclick="scrollToMonthlySeries()" class="bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700 transition-colors text-sm font-medium">
                        📅 Monthly Series
                    </button>
                    <button onclick="scrollToProfessionalSeries()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors text-sm font-medium">
                        🏏 Professional Series
                    </button>
                    @if(request()->has('show_all'))
                        <a href="{{ route('cricket.series') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                            Show Active Only
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Debug Information -->
        @if(isset($debugInfo))
        <div class="mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
            <h3 class="text-lg font-semibold text-blue-800 mb-2">🔍 Debug Information</h3>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 text-sm">
                <div>
                    <span class="font-medium text-blue-700">Total Series from API:</span>
                    <span class="text-blue-600">{{ $debugInfo['total_series_from_api'] }}</span>
                </div>
                <div>
                    <span class="font-medium text-blue-700">Active Series Count:</span>
                    <span class="text-blue-600">{{ $debugInfo['active_series_count'] }}</span>
                </div>
                <div>
                    <span class="font-medium text-blue-700">Recent Series Count:</span>
                    <span class="text-blue-600">{{ $debugInfo['recent_series_count'] ?? 0 }}</span>
                </div>
                <div>
                    <span class="font-medium text-blue-700">Upcoming Series Count:</span>
                    <span class="text-blue-600">{{ $debugInfo['upcoming_series_count'] ?? 0 }}</span>
                </div>
                <div>
                    <span class="font-medium text-blue-700">Completed Series Count:</span>
                    <span class="text-blue-600">{{ $debugInfo['completed_series_count'] ?? 0 }}</span>
                </div>
                <div>
                    <span class="font-medium text-blue-700">Current Date:</span>
                    <span class="text-blue-600">{{ $debugInfo['current_date'] }}</span>
                </div>
                <div>
                    <span class="font-medium text-blue-700">Total Leagues:</span>
                    <span class="text-blue-600">{{ $debugInfo['total_leagues'] ?? 0 }}</span>
                </div>
                <div>
                    <span class="font-medium text-blue-700">Live Matches:</span>
                    <span class="text-blue-600">{{ $debugInfo['total_live_matches'] ?? 0 }}</span>
                </div>
                <div>
                    <span class="font-medium text-blue-700">API Status:</span>
                    <span class="text-green-600">✅ Active</span>
                </div>
            </div>
            
            <!-- Raw API Data Preview -->
            @if(!empty($allSeries))
            <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded">
                <h4 class="font-medium text-yellow-800 mb-2">📊 Raw API Data Preview (First 3 Series)</h4>
                <div class="text-xs text-yellow-700 space-y-2">
                    @foreach(array_slice($allSeries, 0, 3) as $index => $series)
                    <div class="border-l-2 border-yellow-300 pl-2">
                        <strong>Series {{ $index + 1 }}:</strong><br>
                        Name: {{ $series['series_name'] ?? $series['league_name'] ?? 'Unknown' }}<br>
                        Key: {{ $series['series_key'] ?? 'N/A' }}<br>
                        Events Count: {{ count($series['events'] ?? []) }}<br>
                        @if(!empty($series['events']))
                        First Event: {{ $series['events'][0]['event_home_team'] ?? 'N/A' }} vs {{ $series['events'][0]['event_away_team'] ?? 'N/A' }} ({{ $series['series_name'] ?? 'Unknown' }})
                        @endif
                    </div>
                    @endforeach
                </div>
                
                <!-- Data Structure Debug -->
                <div class="mt-3 p-2 bg-yellow-100 rounded">
                    <h5 class="font-medium text-yellow-800 mb-1">🔍 Data Structure Debug</h5>
                    <div class="text-xs text-yellow-700">
                        <strong>Total Series:</strong> {{ count($allSeries) }}<br>
                        <strong>First Series Keys:</strong> {{ implode(', ', array_keys($allSeries[0] ?? [])) }}<br>
                        <strong>Sample Event Keys:</strong> 
                        @if(!empty($allSeries[0]['events']))
                            {{ implode(', ', array_keys($allSeries[0]['events'][0] ?? [])) }}
                        @else
                            No events found
                        @endif
                    </div>
                </div>
            </div>
            @else
            <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded">
                <h4 class="font-medium text-red-800 mb-2">❌ No API Data Available</h4>
                <p class="text-sm text-red-700">The API call returned no data. This could indicate:</p>
                <ul class="text-sm text-red-700 mt-2 list-disc list-inside">
                    <li>API key is invalid or expired</li>
                    <li>API endpoint is not responding</li>
                    <li>No cricket data available for the current period</li>
                    <li>Filtering is too restrictive</li>
                </ul>
                <div class="mt-3">
                    <a href="{{ route('cricket.test-api') }}" class="text-red-600 hover:text-red-800 text-sm font-medium">
                        Test API Connection →
                    </a>
                </div>
            </div>
            @endif
            
            <div class="mt-3 text-center">
                <a href="{{ route('cricket.debug') }}" class="text-blue-600 hover:text-blue-800 text-sm">View Detailed Debug</a> |
                <a href="{{ route('cricket.test-api') }}" class="text-green-600 hover:text-green-800 text-sm">Test API Directly</a>
            </div>
            
            <!-- Data Structure Debug -->
            @if(isset($debugInfo['error']))
            <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded">
                <h4 class="font-medium text-red-800 mb-2">🚨 API Error</h4>
                <p class="text-sm text-red-700">{{ $debugInfo['error'] }}</p>
            </div>
            @endif
                
                <!-- Sample Data Structure -->
                @if(!empty($allSeries))
                <div class="mt-3 p-2 bg-gray-100 rounded">
                    <h5 class="font-medium text-gray-800 mb-1">📋 Sample Data Structure</h5>
                    <div class="text-xs text-gray-700">
                        <strong>First Series Keys:</strong> {{ implode(', ', array_keys($allSeries[0] ?? [])) }}<br>
                        <strong>Sample Series Name:</strong> {{ $allSeries[0]['series_name'] ?? $allSeries[0]['league_name'] ?? 'Not found' }}<br>
                        <strong>Events Count:</strong> {{ count($allSeries[0]['events'] ?? []) }}<br>
                        @if(!empty($allSeries[0]['events']))
                        <strong>First Event:</strong> {{ $allSeries[0]['events'][0]['event_home_team'] ?? 'N/A' }} vs {{ $allSeries[0]['events'][0]['event_away_team'] ?? 'N/A' }}
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Professional Series Overview Section -->
        @if(isset($allSeries) && !empty($allSeries))
        <div class="mb-6 bg-white rounded-lg shadow p-4">
            <h3 class="text-xl font-semibold text-gray-900 mb-4">🏏 Professional Series Overview</h3>
            
            <!-- Series Statistics Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-6">
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white p-4 rounded-lg text-center">
                    <div class="text-2xl font-bold">{{ count($activeSeries ?? []) }}</div>
                    <div class="text-sm opacity-90">Live Series</div>
                </div>
                <div class="bg-gradient-to-br from-green-500 to-green-600 text-white p-4 rounded-lg text-center">
                    <div class="text-2xl font-bold">{{ count($upcomingSeries ?? []) }}</div>
                    <div class="text-sm opacity-90">Upcoming</div>
                </div>
                <div class="bg-gradient-to-br from-gray-500 to-gray-600 text-white p-4 rounded-lg text-center">
                    <div class="text-2xl font-bold">{{ count($completedSeries ?? []) }}</div>
                    <div class="text-sm opacity-90">Completed</div>
                </div>
                <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white p-4 rounded-lg text-center">
                    <div class="text-2xl font-bold">{{ count($recentSeries ?? []) }}</div>
                    <div class="text-sm opacity-90">Recent</div>
                </div>
                <div class="bg-gradient-to-br from-orange-500 to-orange-600 text-white p-4 rounded-lg text-center">
                    <div class="text-2xl font-bold">{{ count($allLeagues ?? []) }}</div>
                    <div class="text-sm opacity-90">Total Leagues</div>
                </div>
                <div class="bg-gradient-to-br from-red-500 to-red-600 text-white p-4 rounded-lg text-center">
                    <div class="text-2xl font-bold">{{ count($liveScores ?? []) }}</div>
                    <div class="text-sm opacity-90">Live Matches</div>
                </div>
            </div>

            <!-- Live Series Section -->
            @if(!empty($activeSeries))
            <div class="mb-8">
                <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <span class="w-3 h-3 bg-red-500 rounded-full mr-3 animate-pulse"></span>
                    🔴 Live Series ({{ count($activeSeries) }})
                </h4>
                <div class="grid grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($activeSeries as $series)
                    <div class="border border-red-200 rounded-lg p-6 hover:shadow-lg transition-all duration-300 bg-gradient-to-br from-red-50 to-white">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h5 class="text-lg font-semibold text-gray-900">{{ $series['series_name'] }}</h5>
                                <div class="text-sm text-gray-600">{{ $series['series_country'] }} • {{ $series['series_type'] ?? 'Series' }}</div>
                            </div>
                            <div class="text-right">
                                <div class="text-2xl font-bold text-red-600">{{ $series['live_matches'] }}</div>
                                <div class="text-xs text-red-600 font-medium">LIVE</div>
                            </div>
                        </div>
                        
                        <!-- Series Progress -->
                        <div class="mb-4">
                            <div class="flex justify-between text-sm text-gray-600 mb-1">
                                <span>Series Progress</span>
                                <span>{{ $series['completed_matches'] }}/{{ $series['total_matches'] }}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                @php
                                    $progress = $series['total_matches'] > 0 ? ($series['completed_matches'] / $series['total_matches']) * 100 : 0;
                                @endphp
                                <div class="bg-red-600 h-2 rounded-full transition-all duration-300" style="width: {{ $progress }}%"></div>
                            </div>
                        </div>
                        
                        <!-- Live Matches Preview -->
                        <div class="space-y-2">
                            @foreach(array_slice($series['events'], 0, 3) as $event)
                                @if(in_array(strtolower($event['event_status'] ?? ''), ['live', 'started', 'in progress']))
                                <div class="flex items-center justify-between p-2 bg-red-100 rounded border border-red-200">
                                    <div class="flex items-center space-x-2">
                                        <span class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
                                        <span class="text-sm font-medium">{{ $event['event_home_team'] ?? 'TBD' }} vs {{ $event['event_away_team'] ?? 'TBD' }}</span>
                                    </div>
                                    <div class="text-xs text-red-700 font-medium">LIVE</div>
                                </div>
                                @endif
                            @endforeach
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="flex space-x-2 mt-4">
                            <a href="{{ route('cricket.series-detail', $series['series_key']) }}" class="flex-1 bg-green-600 text-white text-center py-2 px-4 rounded-lg hover:bg-green-700 transition-colors text-sm font-medium">
                                View Details
                            </a>
                            @if(!empty($series['standings']))
                            <button class="px-3 py-2 border border-red-600 text-red-600 rounded-lg hover:bg-red-50 transition-colors text-xs font-medium">
                                Standings
                            </button>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Upcoming Series Section -->
            @if(!empty($upcomingSeries))
            <div class="mb-8">
                <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <span class="w-3 h-3 bg-blue-500 rounded-full mr-3"></span>
                    🔵 Upcoming Series ({{ count($upcomingSeries) }})
                </h4>
                <div class="grid grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($upcomingSeries as $series)
                    <div class="border border-blue-200 rounded-lg p-6 hover:shadow-lg transition-all duration-300 bg-gradient-to-br from-blue-50 to-white">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h5 class="text-lg font-semibold text-gray-900">{{ $series['series_name'] }}</h5>
                                <div class="text-sm text-gray-600">{{ $series['series_country'] }} • {{ $series['series_type'] ?? 'Series' }}</div>
                            </div>
                            <div class="text-right">
                                <div class="text-2xl font-bold text-blue-600">{{ $series['upcoming_matches'] }}</div>
                                <div class="text-xs text-blue-600 font-medium">UPCOMING</div>
                            </div>
                        </div>
                        
                        <!-- Next Match Info -->
                        @if(!empty($series['events']))
                            @php
                                $nextMatch = null;
                                foreach($series['events'] as $event) {
                                    if(in_array(strtolower($event['event_status'] ?? ''), ['scheduled', 'not started'])) {
                                        $nextMatch = $event;
                                        break;
                                    }
                                }
                            @endphp
                            @if($nextMatch)
                            <div class="mb-4 p-3 bg-blue-100 rounded-lg border border-blue-200">
                                <div class="text-sm font-medium text-blue-800 mb-1">Next Match</div>
                                <div class="text-sm text-blue-700">{{ $nextMatch['event_home_team'] ?? 'TBD' }} vs {{ $nextMatch['event_away_team'] ?? 'TBD' }}</div>
                                @if(isset($nextMatch['event_date_start']))
                                <div class="text-xs text-blue-600 mt-1">{{ \Carbon\Carbon::parse($nextMatch['event_date_start'])->format('M d, Y H:i') }}</div>
                                @endif
                            </div>
                            @endif
                        @endif
                        
                        <!-- Action Buttons -->
                        <div class="flex space-x-2 mt-4">
                            <a href="{{ route('cricket.series-detail', $series['series_key']) }}" class="flex-1 bg-green-600 text-white text-center py-2 px-4 rounded-lg hover:bg-green-700 transition-colors text-sm font-medium">
                                View Details
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        @endif

        <!-- Test API Data Section -->
        @if(isset($testApiData))
        <div class="mb-6 bg-white rounded-lg shadow p-4">
            <h3 class="text-xl font-semibold text-gray-900 mb-4">🧪 Test API Data Overview</h3>
            
            @if(isset($testApiData['error']))
            <div class="mb-4 p-4 bg-red-50 rounded-lg border border-red-200">
                <h4 class="text-lg font-semibold text-red-800 mb-2">❌ API Error</h4>
                <p class="text-red-700">{{ $testApiData['error'] }}</p>
            </div>
            @else
            
            <!-- Summary Statistics -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                    <div class="text-2xl font-bold text-blue-600">{{ $testApiData['total_leagues_available'] }}</div>
                    <div class="text-sm text-blue-700">Total Leagues</div>
                </div>
                <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                    <div class="text-2xl font-bold text-green-600">{{ $testApiData['total_teams_available'] }}</div>
                    <div class="text-sm text-green-700">Total Teams</div>
                </div>
                <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
                    <div class="text-2xl font-bold text-yellow-600">{{ $testApiData['current_month_events'] }}</div>
                    <div class="text-sm text-yellow-700">Current Month Events</div>
                </div>
                <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                    <div class="text-2xl font-bold text-purple-600">{{ $testApiData['next_month_events'] }}</div>
                    <div class="text-sm text-purple-700">Next Month Events</div>
                </div>
            </div>

            <!-- Pakistan Matches -->
            @if(!empty($testApiData['pakistan_matches']))
            <div class="mb-6">
                <h4 class="text-lg font-semibold text-gray-800 mb-3">🇵🇰 Pakistan Matches</h4>
                <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($testApiData['pakistan_matches'] as $match)
                    <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                        <div class="font-medium text-green-800">{{ $match['event_home_team'] ?? 'N/A' }} vs {{ $match['event_away_team'] ?? 'N/A' }}</div>
                        <div class="text-sm text-green-600 mt-1">{{ $match['league_name'] ?? 'Unknown League' }}</div>
                        <div class="text-xs text-green-500 mt-1">{{ $match['event_date'] ?? 'Date not available' }}</div>
                        @if(isset($match['event_status']))
                        <span class="inline-block px-2 py-1 text-xs rounded-full {{ $match['event_status'] === 'live' ? 'bg-red-100 text-red-800' : ($match['event_status'] === 'upcoming' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                            {{ ucfirst($match['event_status']) }}
                        </span>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- West Indies Matches -->
            @if(!empty($testApiData['west_indies_matches']))
            <div class="mb-6">
                <h4 class="text-lg font-semibold text-gray-800 mb-3">🏝️ West Indies Matches</h4>
                <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($testApiData['west_indies_matches'] as $match)
                    <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                        <div class="font-medium text-blue-800">{{ $match['event_home_team'] ?? 'N/A' }} vs {{ $match['event_away_team'] ?? 'N/A' }}</div>
                        <div class="text-sm text-blue-600 mt-1">{{ $match['league_name'] ?? 'Unknown League' }}</div>
                        <div class="text-xs text-blue-500 mt-1">{{ $match['event_date'] ?? 'Date not available' }}</div>
                        @if(isset($match['event_status']))
                        <span class="inline-block px-2 py-1 text-xs rounded-full {{ $match['event_status'] === 'live' ? 'bg-red-100 text-red-800' : ($match['event_status'] === 'upcoming' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                            {{ ucfirst($match['event_status']) }}
                        </span>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Sample Leagues -->
            @if(!empty($testApiData['sample_leagues']))
            <div class="mb-6">
                <h4 class="text-lg font-semibold text-gray-800 mb-3">🏆 Sample Leagues (First 10)</h4>
                <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($testApiData['sample_leagues'] as $league)
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <div class="font-medium text-gray-800">{{ $league['league_name'] ?? 'Unknown League' }}</div>
                        <div class="text-sm text-gray-600 mt-1">{{ $league['league_country'] ?? 'Country not specified' }}</div>
                        @if(isset($league['league_season']))
                        <div class="text-xs text-gray-500 mt-1">Season: {{ $league['league_season'] }}</div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Sample Teams -->
            @if(!empty($testApiData['sample_teams']))
            <div class="mb-6">
                <h4 class="text-lg font-semibold text-gray-800 mb-3">👥 Sample Teams (First 10)</h4>
                <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($testApiData['sample_teams'] as $team)
                    <div class="bg-indigo-50 p-4 rounded-lg border border-indigo-200">
                        <div class="font-medium text-indigo-800">{{ $team['team_name'] ?? 'Unknown Team' }}</div>
                        <div class="text-sm text-indigo-600 mt-1">{{ $team['team_country'] ?? 'Country not specified' }}</div>
                        @if(isset($team['team_key']))
                        <div class="text-xs text-indigo-500 mt-1">Key: {{ $team['team_key'] }}</div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Sample Current Month Events -->
            @if(!empty($testApiData['sample_current_month']))
            <div class="mb-6">
                <h4 class="text-lg font-semibold text-gray-800 mb-3">📅 Sample Current Month Events (First 5)</h4>
                <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($testApiData['sample_current_month'] as $event)
                    <div class="bg-orange-50 p-4 rounded-lg border border-orange-200">
                        <div class="font-medium text-orange-800">{{ $event['event_home_team'] ?? 'N/A' }} vs {{ $event['event_away_team'] ?? 'N/A' }}</div>
                        <div class="text-sm text-orange-600 mt-1">{{ $event['league_name'] ?? 'Unknown League' }}</div>
                        <div class="text-xs text-orange-500 mt-1">{{ $event['event_date'] ?? 'Date not available' }}</div>
                        @if(isset($event['event_status']))
                        <span class="inline-block px-2 py-1 text-xs rounded-full {{ $event['event_status'] === 'live' ? 'bg-red-100 text-red-800' : ($event['event_status'] === 'upcoming' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                            {{ ucfirst($event['event_status']) }}
                        </span>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Sample Next Month Events -->
            @if(!empty($testApiData['sample_next_month']))
            <div class="mb-6">
                <h4 class="text-lg font-semibold text-gray-800 mb-3">📅 Sample Next Month Events (First 5)</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($testApiData['sample_next_month'] as $event)
                    <div class="bg-pink-50 p-4 rounded-lg border border-pink-200">
                        <div class="font-medium text-pink-800">{{ $event['event_home_team'] ?? 'N/A' }} vs {{ $event['event_away_team'] ?? 'N/A' }}</div>
                        <div class="text-sm text-pink-600 mt-1">{{ $event['league_name'] ?? 'Unknown League' }}</div>
                        <div class="text-xs text-pink-500 mt-1">{{ $event['event_date'] ?? 'Date not available' }}</div>
                        @if(isset($event['event_status']))
                        <span class="inline-block px-2 py-1 text-xs rounded-full {{ $event['event_status'] === 'live' ? 'bg-red-100 text-red-800' : ($event['event_status'] === 'upcoming' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                            {{ ucfirst($event['event_status']) }}
                        </span>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
            @endif
        </div>
        @endif

        <!-- Current and Previous Month Series Section -->
        @if(isset($monthlySeriesData))
        <div class="mb-6 bg-white rounded-lg shadow p-4" id="monthly-series-section">
            <h3 class="text-xl font-semibold text-gray-900 mb-4">📅 Monthly Series Overview</h3>
            
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-gradient-to-r from-green-500 to-green-600 text-white p-4 rounded-lg">
                    <div class="text-2xl font-bold">{{ $monthlySeriesData['current_month']['total_events'] }}</div>
                    <div class="text-sm opacity-90">Current Month Events</div>
                    <div class="text-xs opacity-75">{{ $monthlySeriesData['current_month']['period'] }}</div>
                </div>
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white p-4 rounded-lg">
                    <div class="text-2xl font-bold">{{ $monthlySeriesData['previous_month']['total_events'] }}</div>
                    <div class="text-sm opacity-90">Previous Month Events</div>
                    <div class="text-xs opacity-75">{{ $monthlySeriesData['previous_month']['period'] }}</div>
                </div>
                <div class="bg-gradient-to-r from-purple-500 to-purple-600 text-white p-4 rounded-lg">
                    <div class="text-2xl font-bold">{{ count($monthlySeriesData['current_month']['series']) + count($monthlySeriesData['previous_month']['series']) }}</div>
                    <div class="text-sm opacity-90">Total Series</div>
                    <div class="text-xs opacity-75">Both Months</div>
                </div>
            </div>
            
            <!-- Current Month Series -->
            @if(!empty($monthlySeriesData['current_month']['series']))
            <div class="mb-8">
                <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <span class="w-3 h-3 bg-green-500 rounded-full mr-3"></span>
                    {{ $monthlySeriesData['current_month']['period'] }} - {{ $monthlySeriesData['current_month']['total_events'] }} Events
                </h4>
                
                <div class="space-y-6">
                    @foreach($monthlySeriesData['current_month']['series'] as $series)
                    <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                        <!-- Series Header -->
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h5 class="text-lg font-semibold text-gray-900">{{ $series['series_name'] }}</h5>
                                <div class="text-sm text-gray-600">{{ $series['series_country'] }} • Season {{ $series['series_season'] }}</div>
                            </div>
                            <div class="text-right">
                                <div class="text-2xl font-bold text-blue-600">{{ $series['total_matches'] }}</div>
                                <div class="text-xs text-gray-500">Total Matches</div>
                            </div>
                        </div>
                        
                        <!-- Series Statistics -->
                        <div class="grid grid-cols-4 gap-4 mb-4">
                            <div class="text-center p-3 bg-green-50 rounded-lg">
                                <div class="text-lg font-bold text-green-600">{{ $series['live_matches'] }}</div>
                                <div class="text-xs text-green-700">Live</div>
                            </div>
                            <div class="text-center p-3 bg-blue-50 rounded-lg">
                                <div class="text-lg font-bold text-blue-600">{{ $series['upcoming_matches'] }}</div>
                                <div class="text-xs text-blue-700">Upcoming</div>
                            </div>
                            <div class="text-center p-3 bg-gray-50 rounded-lg">
                                <div class="text-lg font-bold text-gray-600">{{ $series['completed_matches'] }}</div>
                                <div class="text-xs text-gray-700">Completed</div>
                            </div>
                            <div class="text-center p-3 bg-purple-50 rounded-lg">
                                <div class="text-lg font-bold text-purple-600">{{ $series['match_summary']['total_runs'] ?? 0 }}</div>
                                <div class="text-xs text-purple-700">Total Runs</div>
                            </div>
                        </div>
                        
                        <!-- Match Summary -->
                        @if(isset($series['match_summary']))
                        <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                            <h6 class="font-medium text-gray-800 mb-2">📊 Series Summary</h6>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                                <div>
                                    <span class="font-medium text-gray-700">Highest Score:</span>
                                    <span class="text-gray-600">{{ $series['match_summary']['highest_score'] ?? 0 }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Lowest Score:</span>
                                    <span class="text-gray-600">{{ $series['match_summary']['lowest_score'] ?? 0 }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Total Wickets:</span>
                                    <span class="text-gray-600">{{ $series['match_summary']['total_wickets'] ?? 0 }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Total Runs:</span>
                                    <span class="text-gray-600">{{ $series['match_summary']['total_runs'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        <!-- All Matches -->
                        <div class="border-t pt-4">
                            <h6 class="font-medium text-gray-800 mb-3">🏏 All Matches ({{ count($series['events']) }})</h6>
                            <div class="space-y-3">
                                @foreach($series['events'] as $event)
                                <div class="border border-gray-100 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center space-x-3">
                                            <span class="w-2 h-2 rounded-full 
                                                @if(in_array(strtolower($event['event_status'] ?? ''), ['live', 'started', 'in progress'])) bg-green-500
                                                @elseif(in_array(strtolower($event['event_status'] ?? ''), ['finished', 'completed'])) bg-gray-500
                                                @else bg-blue-500 @endif">
                                            </span>
                                            <span class="font-medium text-gray-900">{{ $event['event_home_team'] ?? 'TBD' }} vs {{ $event['event_away_team'] ?? 'TBD' }}</span>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-sm font-medium text-gray-700">{{ ucfirst($event['event_status'] ?? 'Unknown') }}</div>
                                            @if(isset($event['event_date_start']))
                                                <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($event['event_date_start'])->format('M d, H:i') }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <!-- Match Results -->
                                    @if(isset($event['match_results']))
                                    <div class="mt-3 p-3 bg-blue-50 rounded-lg border border-blue-200">
                                        <h7 class="font-medium text-blue-800 mb-2 block">📈 Match Results</h7>
                                        <div class="grid grid-cols-2 gap-4 text-sm">
                                            <div>
                                                <div class="font-medium text-blue-700">{{ $event['match_results']['home_team']['name'] }}</div>
                                                <div class="text-blue-600">{{ $event['match_results']['home_team']['score'] }} ({{ $event['match_results']['home_team']['overs'] }})</div>
                                                <div class="text-xs text-blue-500">{{ $event['match_results']['home_team']['wickets'] }} wickets</div>
                                            </div>
                                            <div>
                                                <div class="font-medium text-blue-700">{{ $event['match_results']['away_team']['name'] }}</div>
                                                <div class="text-blue-600">{{ $event['match_results']['away_team']['score'] }} ({{ $event['match_results']['away_team']['overs'] }})</div>
                                                <div class="text-xs text-blue-500">{{ $event['match_results']['away_team']['wickets'] }} wickets</div>
                                            </div>
                                        </div>
                                        @if(!empty($event['match_results']['result']))
                                        <div class="mt-2 p-2 bg-green-100 rounded border border-green-200">
                                            <div class="text-sm font-medium text-green-800">🏆 {{ $event['match_results']['result'] }}</div>
                                        </div>
                                        @endif
                                    </div>
                                    @endif
                                    
                                    <!-- Match Details Link -->
                                    @if(isset($event['event_key']))
                                    <div class="mt-3 text-right">
                                        <a href="{{ route('cricket.match-detail', $event['event_key']) }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                                            View Full Details →
                                        </a>
                                    </div>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @else
            <div class="mb-6 p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                <h4 class="font-medium text-yellow-800 mb-2">⚠️ No Current Month Series</h4>
                <p class="text-sm text-yellow-700">No series data available for {{ $monthlySeriesData['current_month']['period'] }}</p>
            </div>
            @endif
            
            <!-- Previous Month Series -->
            @if(!empty($monthlySeriesData['previous_month']['series']))
            <div class="mb-8">
                <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <span class="w-3 h-3 bg-blue-500 rounded-full mr-3"></span>
                    {{ $monthlySeriesData['previous_month']['period'] }} - {{ $monthlySeriesData['previous_month']['total_events'] }} Events
                </h4>
                
                <div class="space-y-6">
                    @foreach($monthlySeriesData['previous_month']['series'] as $series)
                    <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                        <!-- Series Header -->
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h5 class="text-lg font-semibold text-gray-900">{{ $series['series_name'] }}</h5>
                                <div class="text-sm text-gray-600">{{ $series['series_country'] }} • Season {{ $series['series_season'] }}</div>
                            </div>
                            <div class="text-right">
                                <div class="text-2xl font-bold text-blue-600">{{ $series['total_matches'] }}</div>
                                <div class="text-xs text-gray-500">Total Matches</div>
                            </div>
                        </div>
                        
                        <!-- Series Statistics -->
                        <div class="grid grid-cols-4 gap-4 mb-4">
                            <div class="text-center p-3 bg-green-50 rounded-lg">
                                <div class="text-lg font-bold text-green-600">{{ $series['live_matches'] }}</div>
                                <div class="text-xs text-green-700">Live</div>
                            </div>
                            <div class="text-center p-3 bg-blue-50 rounded-lg">
                                <div class="text-lg font-bold text-blue-600">{{ $series['upcoming_matches'] }}</div>
                                <div class="text-xs text-blue-700">Upcoming</div>
                                </div>
                            <div class="text-center p-3 bg-gray-50 rounded-lg">
                                <div class="text-lg font-bold text-gray-600">{{ $series['completed_matches'] }}</div>
                                <div class="text-xs text-gray-700">Completed</div>
                            </div>
                            <div class="text-center p-3 bg-purple-50 rounded-lg">
                                <div class="text-lg font-bold text-purple-600">{{ $series['match_summary']['total_runs'] ?? 0 }}</div>
                                <div class="text-xs text-purple-700">Total Runs</div>
                            </div>
                        </div>
                        
                        <!-- Match Summary -->
                        @if(isset($series['match_summary']))
                        <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                            <h6 class="font-medium text-gray-800 mb-2">📊 Series Summary</h6>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                                <div>
                                    <span class="font-medium text-gray-700">Highest Score:</span>
                                    <span class="text-gray-600">{{ $series['match_summary']['highest_score'] ?? 0 }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Lowest Score:</span>
                                    <span class="text-gray-600">{{ $series['match_summary']['lowest_score'] ?? 0 }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Total Wickets:</span>
                                    <span class="text-gray-600">{{ $series['match_summary']['total_wickets'] ?? 0 }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Total Runs:</span>
                                    <span class="text-gray-600">{{ $series['match_summary']['total_runs'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        <!-- All Matches -->
                        <div class="border-t pt-4">
                            <h6 class="font-medium text-gray-800 mb-3">🏏 All Matches ({{ count($series['events']) }})</h6>
                            <div class="space-y-3">
                                @foreach($series['events'] as $event)
                                <div class="border border-gray-100 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center space-x-3">
                                            <span class="w-2 h-2 rounded-full 
                                                @if(in_array(strtolower($event['event_status'] ?? ''), ['live', 'started', 'in progress'])) bg-green-500
                                                @elseif(in_array(strtolower($event['event_status'] ?? ''), ['finished', 'completed'])) bg-gray-500
                                                @else bg-blue-500 @endif">
                                            </span>
                                            <span class="font-medium text-gray-900">{{ $event['event_home_team'] ?? 'TBD' }} vs {{ $event['event_away_team'] ?? 'TBD' }}</span>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-sm font-medium text-gray-700">{{ ucfirst($event['event_status'] ?? 'Unknown') }}</div>
                                            @if(isset($event['event_date_start']))
                                                <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($event['event_date_start'])->format('M d, H:i') }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <!-- Match Results -->
                                    @if(isset($event['match_results']))
                                    <div class="mt-3 p-3 bg-blue-50 rounded-lg border border-blue-200">
                                        <h7 class="font-medium text-blue-800 mb-2 block">📈 Match Results</h7>
                                        <div class="grid grid-cols-2 gap-4 text-sm">
                                            <div>
                                                <div class="font-medium text-blue-700">{{ $event['match_results']['home_team']['name'] }}</div>
                                                <div class="text-blue-600">{{ $event['match_results']['home_team']['score'] }} ({{ $event['match_results']['home_team']['overs'] }})</div>
                                                <div class="text-xs text-blue-500">{{ $event['match_results']['home_team']['wickets'] }} wickets</div>
                                            </div>
                                            <div>
                                                <div class="font-medium text-blue-700">{{ $event['match_results']['away_team']['name'] }}</div>
                                                <div class="text-blue-600">{{ $event['match_results']['away_team']['score'] }} ({{ $event['match_results']['away_team']['overs'] }})</div>
                                                <div class="text-xs text-blue-500">{{ $event['match_results']['away_team']['wickets'] }} wickets</div>
                                            </div>
                                        </div>
                                        @if(!empty($event['match_results']['result']))
                                        <div class="mt-2 p-2 bg-green-100 rounded border border-green-200">
                                            <div class="text-sm font-medium text-green-800">🏆 {{ $event['match_results']['result'] }}</div>
                                        </div>
                                        @endif
                                    </div>
                                    @endif
                                    
                                    <!-- Match Details Link -->
                                    @if(isset($event['event_key']))
                                    <div class="mt-3 text-right">
                                        <a href="{{ route('cricket.match-detail', $event['event_key']) }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                                            View Full Details →
                                        </a>
                                    </div>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @else
            <div class="mb-6 p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                <h4 class="font-medium text-yellow-800 mb-2">⚠️ No Previous Month Series</h4>
                <p class="text-sm text-yellow-700">No series data available for {{ $monthlySeriesData['previous_month']['period'] }}</p>
                </div>
            @endif
        </div>
        @endif

        <!-- Filter Controls -->
        <div class="mb-6 bg-white rounded-lg shadow p-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Series Type</label>
                    <select id="seriesTypeFilter" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Types</option>
                        <option value="international">International</option>
                        <option value="league">League</option>
                        <option value="tournament">Tournament</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Match Status</label>
                    <select id="matchStatusFilter" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Status</option>
                        <option value="live">Live</option>
                        <option value="upcoming">Upcoming</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Country</label>
                    <select id="countryFilter" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Countries</option>
                        @php
                            $countries = collect($allSeries)->pluck('series_country')->unique()->filter()->sort()->values();
                        @endphp
                        @foreach($countries as $country)
                            <option value="{{ $country }}">{{ $country }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input type="text" id="searchFilter" placeholder="Search series or teams..." class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6 text-center">
                <div class="text-2xl font-bold text-green-600">{{ count($activeSeries) }}</div>
                <div class="text-sm text-gray-600">Active Series</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6 text-center">
                <div class="text-2xl font-bold text-blue-600">{{ count($recentSeries) }}</div>
                <div class="text-sm text-gray-600">Recent Series</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6 text-center">
                <div class="text-2xl font-bold text-orange-600">{{ count($upcomingSeries) }}</div>
                <div class="text-sm text-gray-600">Upcoming Series</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6 text-center">
                <div class="text-2xl font-bold text-purple-600">{{ count($allSeries) }}</div>
                <div class="text-sm text-gray-600">Total Series</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6 text-center">
                <div class="text-2xl font-bold text-red-600" id="totalMatches">0</div>
                <div class="text-sm text-gray-600">Total Matches</div>
            </div>
        </div>

        <!-- View Toggle Buttons -->
        <div class="mb-6 flex space-x-2">
            <button id="seriesViewBtn" class="px-4 py-2 bg-indigo-600 text-white rounded-lg font-medium">Series View</button>
            <button id="matchesViewBtn" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg font-medium">All Matches</button>
            <button id="standingsViewBtn" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg font-medium">Standings</button>
        </div>

        <!-- Series View (Default) -->
        <div id="seriesView">
            <!-- Active Series Section -->
            @if(!empty($activeSeries))
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                    <span class="w-3 h-3 bg-green-500 rounded-full mr-3"></span>
                    Active Series (Live & Notable)
                </h2>
                <div class="space-y-4">
                    @foreach($activeSeries as $series)
                    <div class="bg-white rounded-lg shadow overflow-hidden series-card" 
                         data-type="{{ strtolower($series['series_name'] ?? '') }}" 
                         data-country="{{ $series['series_country'] ?? '' }}">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                        <span class="text-green-600 text-xl">🏏</span>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900">
                                            {{ $series['series_name'] ?? $series['league_name'] ?? 'Unknown Series' }}
                                        </h3>
                                        <div class="flex items-center space-x-4 text-sm text-gray-500">
                                            <span>{{ $series['series_year'] ?? $series['league_year'] ?? 'N/A' }}</span>
                                            <span>{{ $series['series_country'] ?? 'Unknown Country' }}</span>
                                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Active</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-bold text-green-600">{{ $series['stats']['total_matches'] ?? count($series['events'] ?? []) }}</div>
                                    <div class="text-sm text-gray-500">Total Matches</div>
                                </div>
                            </div>

                            <!-- Series Progress -->
                            <div class="mb-4">
                                <div class="flex justify-between text-sm text-gray-600 mb-1">
                                    <span>Series Progress</span>
                                    <span>{{ $series['stats']['series_progress'] ?? 0 }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-green-600 h-2 rounded-full" style="width: {{ $series['stats']['series_progress'] ?? 0 }}%"></div>
                                </div>
                            </div>

                            <!-- Match Summary -->
                            <div class="grid grid-cols-3 gap-4 mb-4">
                                <div class="text-center p-3 bg-green-50 rounded-lg">
                                    <div class="text-lg font-bold text-green-600">{{ $series['stats']['live_matches'] ?? 0 }}</div>
                                    <div class="text-sm text-gray-600">Live</div>
                                </div>
                                <div class="text-center p-3 bg-blue-50 rounded-lg">
                                    <div class="text-lg font-bold text-blue-600">{{ $series['stats']['upcoming_matches'] ?? 0 }}</div>
                                    <div class="text-sm text-gray-600">Upcoming</div>
                                </div>
                                <div class="text-center p-3 bg-gray-50 rounded-lg">
                                    <div class="text-lg font-bold text-gray-600">{{ $series['stats']['completed_matches'] ?? 0 }}</div>
                                    <div class="text-sm text-gray-600">Completed</div>
                                </div>
                            </div>

                            <!-- Recent Matches Preview -->
                            @if(!empty($series['events']))
                            <div class="border-t pt-4">
                                <h4 class="font-medium text-gray-900 mb-3">Recent Matches</h4>
                                <div class="space-y-2">
                                    @foreach(array_slice($series['events'], 0, 3) as $event)
                                    <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                        <div class="flex items-center space-x-3">
                                            <span class="w-2 h-2 rounded-full 
                                                @if(in_array($event['event_status'] ?? '', ['Live', 'Started', 'In Progress'])) bg-green-500
                                                @elseif(in_array($event['event_status'] ?? '', ['Finished', 'Completed'])) bg-gray-500
                                                @else bg-blue-500 @endif">
                                            </span>
                                            <span class="text-sm font-medium">{{ $event['event_home_team'] ?? 'TBD' }} vs {{ $event['event_away_team'] ?? 'TBD' }}</span>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-xs text-gray-500">{{ $event['event_status'] ?? 'Unknown' }}</div>
                                            @if(isset($event['event_date_start']))
                                                <div class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($event['event_date_start'])->format('M d, H:i') }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                @if(count($series['events']) > 3)
                                    <div class="text-center mt-3">
                                        <button class="text-indigo-600 hover:text-indigo-800 text-sm font-medium" onclick="viewSeriesDetails('{{ $series['series_key'] }}')">
                                            View All {{ count($series['events']) }} Matches →
                                        </button>
                                    </div>
                                @endif
                            </div>
                            @endif

                            <!-- Action Buttons -->
                            <div class="flex space-x-3 mt-4">
                                <a href="{{ route('cricket.series-detail', $series['series_key']) }}" class="flex-1 bg-green-600 text-white text-center py-2 px-4 rounded-lg hover:bg-green-700 transition-colors">
                                    View Details
                                </a>
                                @if(!empty($series['standings']))
                                <button class="px-4 py-2 border border-indigo-600 text-indigo-600 rounded-lg hover:bg-indigo-50 transition-colors" onclick="viewStandings('{{ $series['series_key'] }}')">
                                    Standings
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Recent Series Section -->
            @if(!empty($recentSeries))
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                    <span class="w-3 h-3 bg-blue-500 rounded-full mr-3"></span>
                    Recent Series (Last 90 Days)
                </h2>
                <div class="space-y-4">
                    @foreach($recentSeries as $series)
                    <div class="bg-white rounded-lg shadow overflow-hidden series-card" 
                         data-type="{{ strtolower($series['series_name'] ?? '') }}" 
                         data-country="{{ $series['series_country'] ?? '' }}">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                        <span class="text-blue-600 text-xl">📅</span>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900">
                                            {{ $series['series_name'] ?? $series['league_name'] ?? 'Unknown Series' }}
                                        </h3>
                                        <div class="flex items-center space-x-4 text-sm text-gray-500">
                                            <span>{{ $series['series_year'] ?? $series['league_year'] ?? 'N/A' }}</span>
                                            <span>{{ $series['series_country'] ?? 'Unknown Country' }}</span>
                                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">Recent</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-bold text-blue-600">{{ $series['stats']['completed_matches'] ?? 0 }}</div>
                                    <div class="text-sm text-gray-500">Completed</div>
                                </div>
                            </div>

                            <!-- Match Summary -->
                            <div class="grid grid-cols-3 gap-4 mb-4">
                                <div class="text-center p-3 bg-green-50 rounded-lg">
                                    <div class="text-lg font-bold text-green-600">{{ $series['stats']['live_matches'] ?? 0 }}</div>
                                    <div class="text-sm text-gray-600">Live</div>
                                </div>
                                <div class="text-center p-3 bg-blue-50 rounded-lg">
                                    <div class="text-lg font-bold text-blue-600">{{ $series['stats']['upcoming_matches'] ?? 0 }}</div>
                                    <div class="text-sm text-gray-600">Upcoming</div>
                                </div>
                                <div class="text-center p-3 bg-gray-50 rounded-lg">
                                    <div class="text-lg font-bold text-gray-600">{{ $series['stats']['completed_matches'] ?? 0 }}</div>
                                    <div class="text-sm text-gray-600">Completed</div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex space-x-3 mt-4">
                                <a href="{{ route('cricket.series-detail', $series['series_key']) }}" class="flex-1 bg-green-600 text-white text-center py-2 px-4 rounded-lg hover:bg-green-700 transition-colors">
                                    View Details
                                </a>
                                @if(!empty($series['standings']))
                                <button class="px-4 py-2 border border-blue-600 text-blue-600 rounded-lg hover:bg-blue-50 transition-colors" onclick="viewStandings('{{ $series['series_key'] }}')">
                                    Standings
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Upcoming Series Section -->
            @if(!empty($upcomingSeries))
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                    <span class="w-3 h-3 bg-orange-500 rounded-full mr-3"></span>
                    Upcoming Series (Next 90 Days)
                </h2>
                <div class="space-y-4">
                    @foreach($upcomingSeries as $series)
                    <div class="bg-white rounded-lg shadow overflow-hidden series-card" 
                         data-type="{{ strtolower($series['series_name'] ?? '') }}" 
                         data-country="{{ $series['series_country'] ?? '' }}">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                                        <span class="text-orange-600 text-xl">⏰</span>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900">
                                            {{ $series['series_name'] ?? $series['league_name'] ?? 'Unknown Series' }}
                                        </h3>
                                        <div class="flex items-center space-x-4 text-sm text-gray-500">
                                            <span>{{ $series['series_year'] ?? $series['league_year'] ?? 'N/A' }}</span>
                                            <span>{{ $series['series_country'] ?? 'Unknown Country' }}</span>
                                            <span class="px-2 py-1 bg-orange-100 text-orange-800 rounded-full text-xs">Upcoming</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-bold text-orange-600">{{ $series['stats']['upcoming_matches'] ?? 0 }}</div>
                                    <div class="text-sm text-gray-500">Scheduled</div>
                                </div>
                            </div>

                            <!-- Match Summary -->
                            <div class="grid grid-cols-3 gap-4 mb-4">
                                <div class="text-center p-3 bg-green-50 rounded-lg">
                                    <div class="text-lg font-bold text-green-600">{{ $series['stats']['live_matches'] ?? 0 }}</div>
                                    <div class="text-sm text-gray-600">Live</div>
                                </div>
                                <div class="text-center p-3 bg-blue-50 rounded-lg">
                                    <div class="text-lg font-bold text-blue-600">{{ $series['stats']['upcoming_matches'] ?? 0 }}</div>
                                    <div class="text-sm text-gray-600">Upcoming</div>
                                </div>
                                <div class="text-center p-3 bg-gray-50 rounded-lg">
                                    <div class="text-lg font-bold text-gray-600">{{ $series['stats']['completed_matches'] ?? 0 }}</div>
                                    <div class="text-sm text-gray-600">Completed</div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex space-x-3 mt-4">
                                <a href="{{ route('cricket.series-detail', $series['series_key']) }}" class="flex-1 bg-green-600 text-white text-center py-2 px-4 rounded-lg hover:bg-green-700 transition-colors">
                                    View Details
                                </a>
                                @if(!empty($series['standings']))
                                <button class="px-4 py-2 border border-orange-600 text-orange-600 rounded-lg hover:bg-orange-50 transition-colors" onclick="viewStandings('{{ $series['series_key'] }}')">
                                    Standings
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- All Matches View -->
        <div id="matchesView" class="hidden">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">All Matches</h2>
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Series</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teams</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Format</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="matchesTableBody">
                                <!-- Matches will be populated here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Standings View -->
        <div id="standingsView" class="hidden">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Series Standings</h2>
                <div id="standingsContainer">
                    <!-- Standings will be populated here -->
                </div>
            </div>
        </div>

        <!-- No Series Found Message -->
        @if(empty($activeSeries) && empty($recentSeries) && empty($upcomingSeries))
        <div class="text-center py-12">
            <div class="text-gray-400 text-6xl mb-4">🏏</div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No Series Found</h3>
            <p class="text-gray-500 mb-4">There are currently no active, recent, or upcoming cricket series. Check back later for updates.</p>
            
            <!-- Show all available series for debugging -->
            @if(isset($debugInfo) && $debugInfo['total_series_from_api'] > 0)
                <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <h4 class="font-medium text-yellow-800 mb-2">📊 Available Series Data (Debug)</h4>
                    <p class="text-sm text-yellow-700 mb-3">
                        Found {{ $debugInfo['total_series_from_api'] }} total series from API, but none met the active criteria.
                    </p>
                    <a href="{{ route('cricket.test-api') }}" class="text-yellow-600 hover:text-yellow-800 text-sm font-medium">
                        View Raw API Data →
                    </a>
                </div>
            @endif
        </div>
        @endif

        <!-- All Matches View -->
        <div id="matchesView" class="hidden">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">All Matches</h2>
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Series</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teams</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Format</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="matchesTableBody">
                                <!-- Matches will be populated here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Standings View -->
        <div id="standingsView" class="hidden">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Series Standings</h2>
                <div id="standingsContainer">
                    <!-- Standings will be populated here -->
                </div>
            </div>
        </div>

        <!-- Navigation Links -->
        <div class="mt-8 text-center">
            <a href="{{ route('cricket.index') }}" class="text-indigo-600 hover:text-indigo-800 font-medium">
                ← Back to Home
            </a>
        </div>
    </div>
</div>

<!-- Standings Modal -->
<div id="standingsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900" id="standingsModalTitle">Series Standings</h3>
                <button onclick="closeStandingsModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div id="standingsModalContent">
                <!-- Standings content will be populated here -->
            </div>
        </div>
    </div>
</div>

<script>
// Store all series data for filtering
const allSeriesData = @json($allSeries);
let currentView = 'series';

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    updateTotalMatches();
    setupFilters();
    setupViewToggles();
    
    // Setup toggle view button
    document.getElementById('toggleView').addEventListener('click', function() {
        if (currentView === 'series') {
            switchView('matches');
        } else if (currentView === 'matches') {
            switchView('standings');
        } else {
            switchView('series');
        }
    });
});

// Update total matches count
function updateTotalMatches() {
    let total = 0;
    allSeriesData.forEach(series => {
        total += series.events ? series.events.length : 0;
    });
    document.getElementById('totalMatches').textContent = total;
}

// Setup filters
function setupFilters() {
    const filters = ['seriesTypeFilter', 'matchStatusFilter', 'countryFilter', 'searchFilter'];
    
    filters.forEach(filterId => {
        const element = document.getElementById(filterId);
        if (element) {
            element.addEventListener('change', applyFilters);
            element.addEventListener('input', applyFilters);
        }
    });
}

// Apply filters
function applyFilters() {
    const seriesType = document.getElementById('seriesTypeFilter').value.toLowerCase();
    const matchStatus = document.getElementById('matchStatusFilter').value.toLowerCase();
    const country = document.getElementById('countryFilter').value.toLowerCase();
    const search = document.getElementById('searchFilter').value.toLowerCase();

    const seriesCards = document.querySelectorAll('.series-card');
    
    seriesCards.forEach(card => {
        let show = true;
        
        // Series type filter
        if (seriesType && !card.dataset.type.includes(seriesType)) {
            show = false;
        }
        
        // Country filter
        if (country && card.dataset.country.toLowerCase() !== country) {
            show = false;
        }
        
        // Search filter
        if (search) {
            const seriesName = card.querySelector('h3').textContent.toLowerCase();
            if (!seriesName.includes(search)) {
                show = false;
            }
        }
        
        card.style.display = show ? 'block' : 'none';
    });
}

// Setup view toggles
function setupViewToggles() {
    document.getElementById('seriesViewBtn').addEventListener('click', () => switchView('series'));
    document.getElementById('matchesViewBtn').addEventListener('click', () => switchView('matches'));
    document.getElementById('standingsViewBtn').addEventListener('click', () => switchView('standings'));
}

// Switch between views
function switchView(view) {
    currentView = view;
    
    // Hide all views
    document.getElementById('seriesView').classList.add('hidden');
    document.getElementById('matchesView').classList.add('hidden');
    document.getElementById('standingsView').classList.add('hidden');
    
    // Remove active state from all buttons
    document.getElementById('seriesViewBtn').classList.remove('bg-indigo-600', 'text-white');
    document.getElementById('seriesViewBtn').classList.add('bg-gray-300', 'text-gray-700');
    document.getElementById('matchesViewBtn').classList.remove('bg-indigo-600', 'text-white');
    document.getElementById('matchesViewBtn').classList.add('bg-gray-300', 'text-gray-700');
    document.getElementById('standingsViewBtn').classList.remove('bg-indigo-600', 'text-white');
    document.getElementById('standingsViewBtn').classList.add('bg-gray-300', 'text-gray-700');
    
    // Show selected view and activate button
    if (view === 'series') {
        document.getElementById('seriesView').classList.remove('hidden');
        document.getElementById('seriesViewBtn').classList.remove('bg-gray-300', 'text-gray-700');
        document.getElementById('seriesViewBtn').classList.add('bg-indigo-600', 'text-white');
    } else if (view === 'matches') {
        document.getElementById('matchesView').classList.remove('hidden');
        document.getElementById('matchesViewBtn').classList.remove('bg-gray-300', 'text-gray-700');
        document.getElementById('matchesViewBtn').classList.add('bg-indigo-600', 'text-white');
        populateMatchesView();
    } else if (view === 'standings') {
        document.getElementById('standingsView').classList.remove('hidden');
        document.getElementById('standingsViewBtn').classList.remove('bg-gray-300', 'text-gray-700');
        document.getElementById('standingsViewBtn').classList.add('bg-indigo-600', 'text-white');
        populateStandingsView();
    }
}

// Populate matches view
function populateMatchesView() {
    const tbody = document.getElementById('matchesTableBody');
    tbody.innerHTML = '';
    
    allSeriesData.forEach(series => {
        if (series.events) {
            series.events.forEach(event => {
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50';
                
                const format = determineMatchFormat(event);
                const status = getStatusBadge(event.event_status);
                
                row.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">${series.series_name || 'Unknown'}</div>
                        <div class="text-sm text-gray-500">${series.series_country || 'Unknown'}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">${event.event_home_team || 'TBD'}</div>
                        <div class="text-sm text-gray-500">vs</div>
                        <div class="text-sm font-medium text-gray-900">${event.event_away_team || 'TBD'}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">${status}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        ${event.event_date_start ? new Date(event.event_date_start).toLocaleDateString() : 'TBD'}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            ${format}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="/cricket/match/${event.event_key}" class="text-indigo-600 hover:text-indigo-900">View →</a>
                    </td>
                `;
                
                tbody.appendChild(row);
            });
        }
    });
}

// Populate standings view
function populateStandingsView() {
    const container = document.getElementById('standingsContainer');
    container.innerHTML = '';
    
    allSeriesData.forEach(series => {
        if (series.standings && series.standings.length > 0) {
            const standingsCard = document.createElement('div');
            standingsCard.className = 'bg-white rounded-lg shadow mb-4';
            
            let standingsHtml = `
                <div class="p-4 border-b">
                    <h3 class="text-lg font-semibold text-gray-900">${series.series_name || 'Unknown Series'}</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Team</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">P</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">W</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">L</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">D</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pts</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">`;
            
            series.standings.forEach(team => {
                standingsHtml += '<tr class="hover:bg-gray-50">';
                standingsHtml += `<td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${team.position || 'N/A'}</td>`;
                standingsHtml += `<td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${team.team_name || 'Unknown'}</td>`;
                standingsHtml += `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${team.played || 0}</td>`;
                standingsHtml += `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${team.won || 0}</td>`;
                standingsHtml += `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${team.lost || 0}</td>`;
                standingsHtml += `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${team.drawn || 0}</td>`;
                standingsHtml += `<td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${team.points || 0}</td>`;
                standingsHtml += '</tr>';
            });
            
            standingsHtml += '</tbody></table></div>';
            
            standingsCard.innerHTML = standingsHtml;
            container.appendChild(standingsCard);
        }
    });
}

// Determine match format
function determineMatchFormat(match) {
    const leagueName = (match.league_name || '').toLowerCase();
    
    if (leagueName.includes('test')) return 'Test Match';
    if (leagueName.includes('odi') || leagueName.includes('one day')) return 'ODI';
    if (leagueName.includes('t20') || leagueName.includes('twenty20')) return 'T20';
    if (leagueName.includes('ipl') || leagueName.includes('psl')) return 'T20 League';
    
    return 'Limited Overs';
}

// Get status badge
function getStatusBadge(status) {
    const statusLower = (status || '').toLowerCase();
    
    if (statusLower.includes('live') || statusLower.includes('started') || statusLower.includes('progress')) {
        return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Live</span>';
    } else if (statusLower.includes('finished') || statusLower.includes('completed')) {
        return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Completed</span>';
    } else if (statusLower.includes('scheduled') || statusLower.includes('not started')) {
        return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Scheduled</span>';
    }
    
    return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">' + (status || 'Unknown') + '</span>';
}

// View series details
function viewSeriesDetails(seriesKey) {
    window.location.href = `/cricket/series/${seriesKey}`;
}

// View match details with scoreboard
function viewMatchDetails(matchKey) {
    // This would typically redirect to a match detail page
    // For now, we'll show a modal or redirect
    window.location.href = `/cricket/match/${matchKey}`;
}

// Get live score for a match
function getLiveScore(matchKey) {
    // This would typically make an API call to get live scores
    // For now, we'll return a placeholder
    return {
        home_score: '0/0',
        away_score: '0/0',
        overs: '0.0',
        status: 'Live'
    };
}

// View standings
function viewStandings(seriesKey) {
    const series = allSeriesData.find(s => s.series_key === seriesKey);
    if (series && series.standings) {
        document.getElementById('standingsModalTitle').textContent = `${series.series_name} - Standings`;
        
        let standingsHtml = '<div class="overflow-x-auto">';
        standingsHtml += '<table class="min-w-full divide-y divide-gray-200">';
        standingsHtml += '<thead class="bg-gray-50"><tr>';
        standingsHtml += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>';
        standingsHtml += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Team</th>';
        standingsHtml += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">P</th>';
        standingsHtml += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">W</th>';
        standingsHtml += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">L</th>';
        standingsHtml += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">D</th>';
        standingsHtml += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pts</th>';
        standingsHtml += '</tr></thead>';
        standingsHtml += '<tbody class="bg-white divide-y divide-gray-200">';
        
        series.standings.forEach(team => {
            standingsHtml += '<tr class="hover:bg-gray-50">';
            standingsHtml += `<td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${team.position || 'N/A'}</td>`;
            standingsHtml += `<td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${team.team_name || 'Unknown'}</td>`;
            standingsHtml += `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${team.played || 0}</td>`;
            standingsHtml += `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${team.won || 0}</td>`;
            standingsHtml += `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${team.lost || 0}</td>`;
            standingsHtml += `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${team.drawn || 0}</td>`;
            standingsHtml += `<td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${team.points || 0}</td>`;
            standingsHtml += '</tr>';
        });
        
        standingsHtml += '</tbody></table></div>';
        
        document.getElementById('standingsModalContent').innerHTML = standingsHtml;
        document.getElementById('standingsModal').classList.remove('hidden');
    }
}

// Close standings modal
function closeStandingsModal() {
    document.getElementById('standingsModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('standingsModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeStandingsModal();
    }
});

// Scroll to monthly series section
function scrollToMonthlySeries() {
    const element = document.getElementById('monthly-series-section');
    if (element) {
        element.scrollIntoView({ 
            behavior: 'smooth', 
            block: 'start' 
        });
    }
}

// Scroll to professional series section
function scrollToProfessionalSeries() {
    const element = document.querySelector('.bg-white.rounded-lg.shadow.p-4');
    if (element) {
        element.scrollIntoView({ 
            behavior: 'smooth', 
            block: 'start' 
        });
    }
}
</script>
@endsection
