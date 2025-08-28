    @extends('layouts.app')

    @section('title', 'Live Cricket Scores - CricZone.pk')
    @section('description', 'Get real-time live cricket scores and updates from matches happening around the world.')

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

<div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 pt-4">
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
                <div class="text-gray-600 text-base font-medium">Loading live scores...</div>
            </div>
        </div>
    </div>

    <!-- Header -->
    <div class="mb-4 sm:mb-6">
        <!-- <h1 class="text-xl sm:text-3xl font-bold text-gray-900 mb-1 sm:mb-3"><span class="blink-dot"></span> Live Scores</h1> -->
        <p class="text-sm sm:text-lg text-gray-600">Real-time cricket updates from around the world</p>
    </div>

    <!-- Live Matches Section -->
    @if(!empty($filteredLiveMatches))
    <div class="mb-6 sm:mb-8">
        <!-- Section Header -->
        <div class="flex items-center justify-between mb-4 sm:mb-6">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900"><span class="blink-dot"></span> Live Matches</h2>
            <div class="flex items-center space-x-2">
                <div class="w-3 h-3 bg-red-500 rounded-full animate-pulse"></div>
                <span class="text-sm text-gray-600">{{ count($filteredLiveMatches) }} live</span>
            </div>
        </div>

        <!-- Matches Grid - 2 per row -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 lg:gap-6">
            @foreach($filteredLiveMatches as $match)
                @include('cricket.partials.match-card', ['match' => $match, 'type' => 'live'])
            @endforeach
        </div>
    </div>
    @else
    <!-- No Live Matches -->
    <div class="text-center py-8 sm:py-16">
        <div class="text-6xl sm:text-8xl mb-4 sm:mb-6">🏏</div>
        <h3 class="text-xl sm:text-2xl font-semibold text-gray-600 mb-2 sm:mb-4">No Live Matches</h3>
        <p class="text-sm sm:text-base text-gray-500 mb-4 sm:mb-6">There are currently no live matches. Check back later!</p>
        <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 justify-center">
            <a href="{{ route('cricket.index') }}" class="bg-green-600 text-white px-4 sm:px-6 py-2 sm:py-3 rounded-md hover:bg-green-700 transition-colors text-sm sm:text-base">
                🏠 Back to Home
            </a>
            <a href="{{ route('cricket.fixtures') }}" class="bg-blue-600 text-white px-4 sm:px-6 py-2 sm:py-3 rounded-md hover:bg-blue-700 transition-colors text-sm sm:text-base">
                📅 Check Fixtures
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