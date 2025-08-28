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
    }
    
    .ball-0 { background: #6b7280; }
    .ball-1 { background: #6b7280; }
    .ball-4 { background: #3b82f6; }
    .ball-6 { background: #10b981; }
    .ball-w { background: #ef4444; }
    
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
    }
</style>

<div class="min-h-screen bg-gray-50 pt-4 pb-6">
    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6">
        <!-- Breadcrumb -->
        @include('cricket.partials.breadcrumb', [
            'items' => [
                ['url' => route('cricket.index'), 'label' => 'Home'],
                ['url' => route('cricket.live-scores'), 'label' => 'Live Scores'],
                ['url' => '#', 'label' => 'Match Details']
            ]
        ])

        <!-- Match Header -->
        <div class="bg-white rounded-lg shadow-md border border-gray-200 mb-6">
            <div class="p-4 sm:p-6">
                <!-- Match Result Banner -->
                @if(isset($match['event_live']) && $match['event_live'] == '1')
                <div class="bg-gradient-to-r from-red-500 to-red-600 text-white p-3 rounded-lg mb-4 animate-pulse">
                    <div class="flex items-center justify-center space-x-2">
                        <div class="w-2 h-2 bg-white rounded-full animate-pulse"></div>
                        <span class="text-base font-bold">🔴 LIVE MATCH</span>
                        <div class="w-2 h-2 bg-white rounded-full animate-pulse"></div>
                    </div>
                    @if(isset($match['event_status_info']))
                    <div class="text-center mt-1">
                        <span class="text-xs">{{ $match['event_status_info'] }}</span>
                    </div>
                    @endif
                </div>
                @else
                <div class="bg-gradient-to-r from-green-500 to-green-600 text-white p-3 rounded-lg mb-4">
                    <div class="flex items-center justify-center space-x-2">
                        <span class="text-base font-bold">🏆 MATCH COMPLETED</span>
                    </div>
                    @if(isset($match['event_status']))
                    <div class="text-center mt-1">
                        <span class="text-sm font-medium">{{ $match['event_status'] }}</span>
                    </div>
                    @endif
                </div>
                @endif

                <!-- Match Title -->
                <div class="text-center mb-4">
                    <h1 class="text-lg sm:text-xl font-bold text-gray-900">
                        {{ $match['event_home_team'] }} vs {{ $match['event_away_team'] }}, 
                        {{ $match['event_type'] ?? 'Match' }}, 
                        {{ $match['league_name'] ?? 'Tournament' }}
                    </h1>
                </div>

                <!-- Teams Score Summary -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                    <!-- Home Team -->
                    <div class="text-center">
                        <div class="mb-2">
                            @if(isset($match['event_home_team_logo']))
                            <img src="{{ $match['event_home_team_logo'] }}" alt="{{ $match['event_home_team'] }}" class="w-16 h-16 mx-auto mb-2 rounded-full border-2 border-white shadow-md">
                            @else
                            <div class="w-16 h-16 mx-auto mb-2 rounded-full border-2 border-white shadow-md bg-blue-100 flex items-center justify-center">
                                <span class="text-lg font-bold text-blue-600">{{ substr($match['event_home_team'], 0, 2) }}</span>
                            </div>
                            @endif
                            <h2 class="text-sm font-bold text-gray-900 truncate">{{ $match['event_home_team'] }}</h2>
                        </div>
                        <div class="text-2xl font-bold text-gray-900">{{ $match['event_home_final_result'] ?? $match['event_service_home'] ?? '0/0' }}</div>
                        @if(isset($match['event_home_overs']))
                        <div class="text-sm text-gray-600">({{ $match['event_home_overs'] }} overs)</div>
                        @endif
                    </div>

                    <!-- VS / Status -->
                    <div class="text-center">
                        <div class="text-2xl font-bold text-gray-400 mb-1">VS</div>
                        @if(isset($match['event_live']) && $match['event_live'] == '1')
                        <div class="text-sm text-red-600 font-semibold animate-pulse">LIVE</div>
                        @else
                        <div class="text-sm text-gray-600 font-semibold">{{ $match['event_status'] ?? 'Upcoming' }}</div>
                        @endif
                    </div>

                    <!-- Away Team -->
                    <div class="text-center">
                        <div class="mb-2">
                            @if(isset($match['event_away_team_logo']))
                            <img src="{{ $match['event_away_team_logo'] }}" alt="{{ $match['event_away_team'] }}" class="w-16 h-16 mx-auto mb-2 rounded-full border-2 border-white shadow-md">
                            @else
                            <div class="w-16 h-16 mx-auto mb-2 rounded-full border-2 border-white shadow-md bg-green-100 flex items-center justify-center">
                                <span class="text-lg font-bold text-green-600">{{ substr($match['event_away_team'], 0, 2) }}</span>
                            </div>
                            @endif
                            <h2 class="text-sm font-bold text-gray-900 truncate">{{ $match['event_away_team'] }}</h2>
                        </div>
                        <div class="text-2xl font-bold text-gray-900">{{ $match['event_away_final_result'] ?? $match['event_service_away'] ?? '0/0' }}</div>
                        @if(isset($match['event_away_overs']))
                        <div class="text-sm text-gray-600">({{ $match['event_away_overs'] }} overs)</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div class="bg-white rounded-lg shadow-md border border-gray-200 mb-6">
            <div class="border-b border-gray-200">
                <nav class="flex space-x-8 px-6" aria-label="Tabs">
                    <button class="tab-button active" data-tab="match-info">
                        📊 Match Info
                    </button>
                    <button class="tab-button" data-tab="live">
                        🔴 Live
                    </button>
                    <button class="tab-button" data-tab="scorecard">
                        📈 Scorecard
                    </button>
                </nav>
            </div>
        </div>

        <!-- Tab Content -->
        <div class="bg-white rounded-lg shadow-lg border border-gray-200 mb-6">
            <!-- Match Info Tab -->
            <div id="match-info-content" class="tab-content p-4 active">
                <div class="space-y-4">
                    <!-- Match Overview -->
                    <div class="match-info-card">
                        <div class="match-info-header">Match Overview</div>
                        <div class="match-info-content">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-500">Series:</span>
                                    <span class="font-medium ml-2">{{ $match['event_type'] ?? 'N/A' }}</span>
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

                    <!-- Match Details -->
                    <div class="match-info-card">
                        <div class="match-info-header">Match Details</div>
                        <div class="match-info-content">
                            <div class="grid grid-cols-3 gap-4 text-center">
                                <div class="bg-gray-50 rounded p-2 stat-card">
                                    <div class="text-xs text-gray-500 mb-1">Match Type</div>
                                    <div class="text-xs font-semibold text-gray-900 truncate">{{ $match['event_type'] ?? 'TBD' }}</div>
                                </div>
                                <div class="bg-gray-50 rounded p-2 stat-card">
                                    <div class="text-xs text-gray-500 mb-1">Venue</div>
                                    <div class="text-xs font-semibold text-gray-900 truncate">{{ $match['event_stadium'] ?? 'TBD' }}</div>
                                </div>
                                <div class="bg-gray-50 rounded p-2 stat-card">
                                    <div class="text-xs text-gray-500 mb-1">Status</div>
                                    <div class="text-xs font-semibold text-gray-900 truncate">{{ $match['event_status'] ?? 'TBD' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(isset($match['event_man_of_match']))
                    <!-- Man of the Match -->
                    <div class="match-info-card">
                        <div class="match-info-header">Player of the Match</div>
                        <div class="match-info-content">
                            <div class="text-center">
                                <div class="text-lg font-bold text-blue-600">{{ $match['event_man_of_match'] }}</div>
                                <div class="text-sm text-gray-600">Outstanding performance</div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Live Tab -->
            <div id="live-content" class="tab-content p-4">
                <div class="space-y-4">
                    <!-- Current Match Status -->
                    @if(isset($match['event_live']) && $match['event_live'] == '1')
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex items-center justify-center space-x-2 mb-3">
                            <div class="w-3 h-3 bg-red-500 rounded-full animate-pulse"></div>
                            <span class="text-red-700 font-semibold">LIVE MATCH</span>
                            <div class="w-3 h-3 bg-red-500 rounded-full animate-pulse"></div>
                        </div>
                        <div class="text-center">
                            <div class="text-sm text-red-600">{{ $match['event_status_info'] ?? 'Match in progress' }}</div>
                        </div>
                    </div>
                    @endif

                    <!-- Commentary Section -->
                    <div class="match-info-card">
                        <div class="match-info-header">Commentary</div>
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
                                @if(isset($match['comments']) && !empty($match['comments']))
                                    @foreach($match['comments'] as $type => $commentList)
                                        @foreach($commentList as $comment)
                                        <div class="commentary-entry">
                                            <div class="ball-indicator ball-{{ $comment['runs'] ?? 0 }}">
                                                {{ $comment['runs'] ?? '0' }}
                                            </div>
                                            <div class="flex-1">
                                                <div class="text-sm text-gray-900">
                                                    {{ $comment['overs'] ?? 'N/A' }} {{ $comment['comment'] ?? 'No commentary available' }}
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    {{ $comment['timestamp'] ?? '' }}
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    @endforeach
                                @else
                                    <div class="text-center py-8 text-gray-500">
                                        <div class="text-4xl mb-2">🏏</div>
                                        <p>Commentary not available for this match</p>
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
                            <h3 class="font-semibold text-blue-900 mb-2">{{ $match['event_home_team'] }}</h3>
                            <div class="text-2xl font-bold text-blue-900">{{ $match['event_home_final_result'] ?? '0/0' }}</div>
                            @if(isset($match['event_home_overs']))
                            <div class="text-sm text-blue-600">({{ $match['event_home_overs'] }} overs)</div>
                            @endif
                        </div>
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <h3 class="font-semibold text-green-900 mb-2">{{ $match['event_away_team'] }}</h3>
                            <div class="text-2xl font-bold text-green-900">{{ $match['event_away_final_result'] ?? '0/0' }}</div>
                            @if(isset($match['event_away_overs']))
                            <div class="text-sm text-green-600">({{ $match['event_away_overs'] }} overs)</div>
                            @endif
                        </div>
                    </div>

                    <!-- Batting Scorecard -->
                    <div class="match-info-card">
                        <div class="match-info-header">Batting</div>
                        <div class="match-info-content">
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
                                    <tr>
                                        <td class="text-gray-900">Sample Player</td>
                                        <td>50</td>
                                        <td>30</td>
                                        <td>4</td>
                                        <td>2</td>
                                        <td>166.67</td>
                                    </tr>
                                    <tr>
                                        <td class="text-gray-900">Another Player</td>
                                        <td>25</td>
                                        <td>20</td>
                                        <td>2</td>
                                        <td>1</td>
                                        <td>125.00</td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="text-center py-4 text-gray-500">
                                <p>Detailed scorecard data will be available here</p>
                            </div>
                        </div>
                    </div>

                    <!-- Bowling Scorecard -->
                    <div class="match-info-card">
                        <div class="match-info-header">Bowling</div>
                        <div class="match-info-content">
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
                                    <tr>
                                        <td class="text-gray-900">Sample Bowler</td>
                                        <td>4.0</td>
                                        <td>0</td>
                                        <td>25</td>
                                        <td>2</td>
                                        <td>6.25</td>
                                    </tr>
                                    <tr>
                                        <td class="text-gray-900">Another Bowler</td>
                                        <td>4.0</td>
                                        <td>0</td>
                                        <td>30</td>
                                        <td>1</td>
                                        <td>7.50</td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="text-center py-4 text-gray-500">
                                <p>Detailed bowling data will be available here</p>
                            </div>
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
    });
</script>
@endsection
