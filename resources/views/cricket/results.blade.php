@extends('layouts.app')

@section('title', 'Cricket Results - CricZone')

@section('content')
@include('partials.page-loader')

<div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 pt-4">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Match Results</h1>
                <p class="text-gray-600 mt-2">View completed cricket matches from the last 30 days and their final scores</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('cricket.results') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Refresh
                </a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
        <form method="GET" action="{{ route('cricket.results') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search Matches</label>
                <input type="text" id="search" name="search" placeholder="Search by team or league..." 
                       value="{{ request('search') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
            </div>
            <div>
                <label for="league" class="block text-sm font-medium text-gray-700 mb-2">League</label>
                <select id="league" name="league" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    <option value="">All Leagues</option>
                    @foreach($leagues as $league)
                        <option value="{{ $league }}" {{ request('league') == $league ? 'selected' : '' }}>
                            {{ $league }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="team" class="block text-sm font-medium text-gray-700 mb-2">Team</label>
                <select id="team" name="team" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    <option value="">All Teams</option>
                    @foreach($teams as $team)
                        <option value="{{ $team }}" {{ request('team') == $team ? 'selected' : '' }}>
                            {{ $team }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Apply Filters
                </button>
            </div>
        </form>
    </div>

    <!-- Recent Completed Matches Section -->
    <div class="mb-6">
        <div class="bg-gradient-to-r from-green-50 to-blue-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-medium text-green-900">🏆 Recent Completed Matches</h3>
                    <p class="text-green-700">Latest results from the past 30 days</p>
                </div>
                <div class="text-sm text-green-600 bg-green-100 px-3 py-1 rounded-full">
                    Latest
                </div>
            </div>
            
            @php
                // Get recent completed matches from past 30 days for quick overview
                $recentMatches = collect($finishedMatches)->sortByDesc(function($match) {
                    $dateStr = $match['event_date_stop'] ?? $match['event_date'] ?? '';
                    if ($dateStr) {
                        try {
                            return \Carbon\Carbon::parse($dateStr)->timestamp;
                        } catch (\Exception $e) {
                            return 0;
                        }
                    }
                    return 0;
                })->take(6);
            @endphp
            
            @if($recentMatches->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($recentMatches as $match)
                        <div class="bg-white rounded-lg shadow-sm border border-green-200 p-3 hover:shadow-md transition-shadow duration-200">
                            <!-- Match Header -->
                            <div class="flex items-center justify-between mb-2">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    🏆 Finished
                                </span>
                                <span class="text-xs text-gray-500">
                                    @php
                                        $matchDate = '';
                                        if (!empty($match['event_date_stop'])) {
                                            try {
                                                $matchDate = \Carbon\Carbon::parse($match['event_date_stop'])->format('M d');
                                            } catch (\Exception $e) {
                                                $matchDate = $match['event_date_stop'];
                                            }
                                        }
                                    @endphp
                                    📅 {{ $matchDate ?: 'Date Unknown' }}
                                </span>
                            </div>
                            
                            <!-- Teams and Scores -->
                            <div class="space-y-2">
                                <!-- Home Team -->
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-900">{{ $match['event_home_team'] ?? 'Unknown Team' }}</span>
                                    <span class="text-sm font-bold text-gray-900">{{ $match['event_home_final_result'] ?? $match['event_home_team_score'] ?? 'N/A' }}</span>
                                </div>
                                
                                <!-- Away Team -->
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-900">{{ $match['event_away_team'] ?? 'Unknown Team' }}</span>
                                    <span class="text-sm font-bold text-gray-900">{{ $match['event_away_final_result'] ?? $match['event_away_team_score'] ?? 'N/A' }}</span>
                                </div>
                            </div>
                            
                            <!-- Match Result -->
                            <div class="mt-2 pt-2 border-t border-gray-100">
                                @php
                                    // Calculate result for completed matches
                                    $homeScoreStr = $match['event_home_final_result'] ?? $match['event_home_team_score'] ?? '0';
                                    $awayScoreStr = $match['event_away_final_result'] ?? $match['event_away_team_score'] ?? '0';
                                    
                                    $homeScore = 0;
                                    $awayScore = 0;
                                    
                                    // Extract runs from score strings
                                    if (!empty($homeScoreStr) && $homeScoreStr !== 'N/A') {
                                        if (is_numeric($homeScoreStr)) {
                                            $homeScore = (int)$homeScoreStr;
                                        } elseif (strpos($homeScoreStr, '/') !== false) {
                                            $parts = explode('/', $homeScoreStr);
                                            $homeScore = is_numeric($parts[0]) ? (int)$parts[0] : 0;
                                        }
                                    }
                                    
                                    if (!empty($awayScoreStr) && $awayScoreStr !== 'N/A') {
                                        if (is_numeric($awayScoreStr)) {
                                            $awayScore = (int)$awayScoreStr;
                                        } elseif (strpos($awayScoreStr, '/') !== false) {
                                            $parts = explode('/', $awayScoreStr);
                                            $awayScore = is_numeric($parts[0]) ? (int)$parts[0] : 0;
                                        }
                                    }
                                    
                                    if ($homeScore > $awayScore) {
                                        echo '<span class="text-green-600 text-xs font-medium">' . ($match['event_home_team'] ?? 'Home Team') . ' won by ' . ($homeScore - $awayScore) . ' runs</span>';
                                    } elseif ($awayScore > $homeScore) {
                                        echo '<span class="text-green-600 text-xs font-medium">' . ($match['event_away_team'] ?? 'Away Team') . ' won by ' . ($awayScore - $homeScore) . ' runs</span>';
                                    } else {
                                        echo '<span class="text-blue-600 text-xs font-medium">Match tied</span>';
                                    }
                                @endphp
                            </div>
                            
                            <!-- League Info -->
                            <div class="mt-2 text-xs text-gray-500">
                                {{ $match['league_name'] ?? 'Unknown League' }}
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <!-- View All Results Link -->
                <div class="mt-4 text-center">
                    <a href="#resultsList" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 transition-colors duration-200">
                        <span>View All Results</span>
                        <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </a>
                </div>
            @else
                <div class="text-center py-6">
                    <div class="text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="text-gray-600">No recent completed matches found</p>
                    </div>
                </div>
            @endif
        </div>
    </div>



    <!-- Results Summary -->
    <div class="mb-6">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium text-blue-900">Results Summary</h3>
                    <p class="text-blue-700">Showing {{ count($paginatedMatches) }} of {{ $totalMatches }} finished matches from the last 30 days</p>
                </div>
                @if($totalMatches > 0)
                    <div class="text-right">
                        <p class="text-sm text-blue-600">Page {{ $currentPage }} of {{ $totalPages }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Results List -->
    <div id="resultsContainer" class="space-y-4">
        @if($totalMatches === 0)
            <!-- No results state -->
            <div class="text-center py-12">
                <div class="text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No results found</h3>
                    <p class="text-gray-600">Try adjusting your search criteria or check back later for new results.</p>
                </div>
            </div>
        @else
            <!-- Results will be populated here -->
            <div id="resultsList" class="space-y-4">
                @foreach($paginatedMatches as $match)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow duration-200">
                        <div class="p-4">
                            <!-- Match Header -->
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center space-x-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Finished
                                    </span>
                                    <span class="text-sm text-gray-500">
                                        @php
                                            $matchDate = '';
                                            if (!empty($match['event_date_stop'])) {
                                                try {
                                                    $matchDate = \Carbon\Carbon::parse($match['event_date_stop'])->format('M d, Y');
                                                } catch (\Exception $e) {
                                                    $matchDate = $match['event_date_stop'];
                                                }
                                            } elseif (!empty($match['event_date'])) {
                                                try {
                                                    $matchDate = \Carbon\Carbon::parse($match['event_date'])->format('M d, Y');
                                                } catch (\Exception $e) {
                                                    $matchDate = $match['event_date'];
                                                }
                                            } else {
                                                $matchDate = 'Date Unknown';
                                            }
                                        @endphp
                                        {{ $matchDate }}
                                    </span>
                                </div>
                                <div class="text-sm text-gray-500">{{ $match['league_name'] ?? 'Unknown League' }}</div>
                            </div>

                            <!-- Teams -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center mb-4">
                                <!-- Team 1 -->
                                <div class="text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-2">
                                            <span class="text-2xl">🏏</span>
                                        </div>
                                        <h3 class="font-semibold text-gray-900">{{ $match['event_home_team'] ?? 'Unknown Team' }}</h3>
                                        <p class="text-sm text-gray-600">{{ $match['event_home_final_result'] ?? $match['event_home_team_score'] ?? 'N/A' }}</p>
                                    </div>
                                </div>

                                <!-- VS -->
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-gray-400">VS</div>
                                    <div class="text-xs text-gray-500 mt-1">Match Complete</div>
                                </div>

                                <!-- Team 2 -->
                                <div class="text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-2">
                                            <span class="text-2xl">🏏</span>
                                        </div>
                                        <h3 class="font-semibold text-gray-900">{{ $match['event_away_team'] ?? 'Unknown Team' }}</h3>
                                        <p class="text-sm text-gray-600">{{ $match['event_away_final_result'] ?? $match['event_away_team_score'] ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Match Details -->
                            <div class="border-t border-gray-100 pt-3">
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span class="text-gray-500">Venue:</span>
                                        <span class="ml-2 text-gray-900">{{ $match['event_stadium'] ?? 'TBD' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Result:</span>
                                        <span class="ml-2 text-gray-900">
                                            @php
                                                // Extract numeric scores from strings like "186/5" or handle "N/A"
                                                $homeScoreStr = $match['event_home_final_result'] ?? $match['event_home_team_score'] ?? '0';
                                                $awayScoreStr = $match['event_away_final_result'] ?? $match['event_away_team_score'] ?? '0';
                                                
                                                // Extract runs directly without function declaration
                                                $homeScore = 0;
                                                $awayScore = 0;
                                                
                                                // Process home score
                                                if (!empty($homeScoreStr) && $homeScoreStr !== 'N/A') {
                                                    if (is_numeric($homeScoreStr)) {
                                                        $homeScore = (int)$homeScoreStr;
                                                    } elseif (strpos($homeScoreStr, '/') !== false) {
                                                        $parts = explode('/', $homeScoreStr);
                                                        $homeScore = is_numeric($parts[0]) ? (int)$parts[0] : 0;
                                                    }
                                                }
                                                
                                                // Process away score
                                                if (!empty($awayScoreStr) && $awayScoreStr !== 'N/A') {
                                                    if (is_numeric($awayScoreStr)) {
                                                        $awayScore = (int)$awayScoreStr;
                                                    } elseif (strpos($awayScoreStr, '/') !== false) {
                                                        $parts = explode('/', $awayScoreStr);
                                                        $awayScore = is_numeric($parts[0]) ? (int)$parts[0] : 0;
                                                    }
                                                }
                                                
                                                if ($homeScore > $awayScore) {
                                                    echo ($match['event_home_team'] ?? 'Home Team') . ' won by ' . ($homeScore - $awayScore) . ' runs';
                                                } elseif ($awayScore > $homeScore) {
                                                    echo ($match['event_away_team'] ?? 'Away Team') . ' won by ' . ($awayScore - $homeScore) . ' runs';
                                                } else {
                                                    echo 'Match tied';
                                                }
                                            @endphp
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- View Details Button -->
                            <div class="mt-4 text-center">
                                @if(isset($match['event_key']))
                                    <a href="{{ route('cricket.match-detail', $match['event_key']) }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                        View Match Details
                                        <svg class="ml-2 -mr-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </a>
                                @else
                                    <span class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-400 bg-gray-100 cursor-not-allowed">
                                        Details Unavailable
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Pagination -->
    @if($totalPages > 1)
        <div class="mt-8">
            <nav class="flex items-center justify-between">
                <div class="flex-1 flex justify-between sm:hidden">
                    @if($currentPage > 1)
                        <a href="{{ route('cricket.results', array_merge(request()->query(), ['page' => $currentPage - 1])) }}" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Previous
                        </a>
                    @else
                        <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-400 bg-gray-100 cursor-not-allowed">
                            Previous
                        </span>
                    @endif
                    
                    @if($currentPage < $totalPages)
                        <a href="{{ route('cricket.results', array_merge(request()->query(), ['page' => $currentPage + 1])) }}" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Next
                        </a>
                    @else
                        <span class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-400 bg-gray-100 cursor-not-allowed">
                            Next
                        </span>
                    @endif
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Showing {{ (($currentPage - 1) * 20) + 1 }} to {{ min($currentPage * 20, $totalMatches) }} of {{ $totalMatches }} results
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                            @if($currentPage > 1)
                                <a href="{{ route('cricket.results', array_merge(request()->query(), ['page' => $currentPage - 1])) }}" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <span class="sr-only">Previous</span>
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </a>
                            @endif
                            
                            @for($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++)
                                <a href="{{ route('cricket.results', array_merge(request()->query(), ['page' => $i])) }}" class="relative inline-flex items-center px-4 py-2 border text-sm font-medium {{ $i === $currentPage ? 'z-10 bg-green-50 border-green-500 text-green-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50' }}">
                                    {{ $i }}
                                </a>
                            @endfor
                            
                            @if($currentPage < $totalPages)
                                <a href="{{ route('cricket.results', array_merge(request()->query(), ['page' => $currentPage + 1])) }}" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <span class="sr-only">Next</span>
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </a>
                            @endif
                        </nav>
                    </div>
                </div>
            </nav>
        </div>
    @endif
</div>

@endsection
