@extends('layouts.app')

@section('title', $team->team_name . ' - Team Details - CricZone.pk')
@section('description', 'View ' . $team->team_name . ' team details, live matches, completed matches, and upcoming fixtures.')

@section('content')
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
            <div class="text-gray-600 text-lg font-medium">Loading team details...</div>
            
            <!-- Animated dots -->
            <div class="flex space-x-1 mt-3">
                <div class="w-2 h-2 bg-green-500 rounded-full animate-bounce" style="animation-delay: 0ms;"></div>
                <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce" style="animation-delay: 150ms;"></div>
                <div class="w-2 h-2 bg-green-500 rounded-full animate-bounce" style="animation-delay: 300ms;"></div>
            </div>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 pt-4">
    <!-- Breadcrumb -->
    @include('cricket.partials.breadcrumb', [
        'items' => [
            ['url' => route('cricket.index'), 'label' => 'Home'],
            ['url' => route('cricket.teams'), 'label' => 'Teams'],
            ['url' => '#', 'label' => $team->team_name]
        ]
    ])

    <!-- Team Header -->
    <div class="text-center mb-6 sm:mb-8">
        <div class="flex flex-col sm:flex-row items-center justify-center space-y-4 sm:space-y-0 sm:space-x-6">
            @if($team->team_logo)
                <img src="{{ $team->team_logo }}" alt="{{ $team->team_name }}" class="w-20 h-20 sm:w-24 sm:h-24 rounded-full border-4 border-green-200 shadow-lg">
            @else
                <div class="w-20 h-20 sm:w-24 sm:h-24 bg-gradient-to-br from-green-400 to-blue-500 rounded-full border-4 border-green-200 shadow-lg flex items-center justify-center">
                    <span class="text-3xl sm:text-4xl text-white font-bold">🏏</span>
                </div>
            @endif
            
            <div>
                <h1 class="text-2xl sm:text-4xl font-bold text-gray-900 mb-2">{{ $team->team_name }}</h1>
                <p class="text-sm sm:text-base text-gray-600">Cricket Team</p>
            </div>
        </div>
    </div>

    <!-- Page Title -->
    <div class="mb-6 sm:mb-8">
        <h2 class="text-xl sm:text-2xl font-bold text-gray-900 text-center">⏰ Upcoming Matches</h2>
        <p class="text-sm text-gray-600 text-center">Showing upcoming matches for {{ $team->team_name }}</p>
    </div>

    <!-- Upcoming Matches Content -->
    @if(count($upcomingMatches) > 0)
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
            @foreach($upcomingMatches as $match)
                @include('cricket.partials.match-card', ['match' => $match, 'type' => 'upcoming'])
            @endforeach
        </div>
    @else
        <div class="text-center py-12">
            <div class="text-6xl mb-4">📅</div>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">No Upcoming Matches</h3>
            <p class="text-gray-500">{{ $team->team_name }} doesn't have any upcoming matches scheduled.</p>
        </div>
    @endif

    <!-- Back Button -->
    <div class="text-center mt-8">
        <a href="{{ route('cricket.teams') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
            ← Back to Teams
        </a>
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


</script>
@endsection
