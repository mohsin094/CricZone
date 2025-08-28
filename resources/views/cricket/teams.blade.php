@extends('layouts.app')

@section('title', 'Cricket Teams - CricZone.pk')

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
            <div class="text-gray-600 text-lg font-medium">Loading teams...</div>
            
            <!-- Animated dots -->
            <div class="flex space-x-1 mt-3">
                <div class="w-2 h-2 bg-green-500 rounded-full animate-bounce" style="animation-delay: 0ms;"></div>
                <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce" style="animation-delay: 150ms;"></div>
                <div class="w-2 h-2 bg-green-500 rounded-full animate-bounce" style="animation-delay: 300ms;"></div>
            </div>
        </div>
    </div>
</div>
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6">


        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Cricket Teams</h1>
                    <p class="text-gray-600">Explore all cricket teams and their upcoming matches</p>
                </div>
            </div>
        </div>

        <!-- Search and Filter -->
        <div class="bg-white rounded-lg shadow-md border border-gray-200 p-6 mb-6">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search Teams</label>
                    <input type="text" id="search" placeholder="Search by team name..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div class="md:w-48">
                    <label for="filter" class="block text-sm font-medium text-gray-700 mb-2">Filter by</label>
                    <select id="filter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">All Teams</option>
                        <option value="international">🌍 International Teams</option>
                        <option value="domestic">🏏 Domestic Teams</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Teams Grid -->
        @if(!empty($teams))
            <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4" id="teams-grid">
                @foreach($teams as $team)
                    @php
                        $isInternational = in_array(strtolower($team->team_name), [
                            'england', 'australia', 'india', 'pakistan', 'south africa', 
                            'west indies', 'new zealand', 'sri lanka', 'bangladesh', 
                            'afghanistan', 'ireland', 'zimbabwe', 'netherlands', 'scotland', 
                            'oman', 'uae', 'namibia', 'papua new guinea', 'kenya', 'canada', 
                            'bermuda', 'hong kong', 'singapore', 'malaysia'
                        ]);
                    @endphp
                    
                    <a href="{{ route('cricket.team-detail', $team->team_key) }}" 
                       class="block bg-white rounded-lg shadow-md border border-gray-200 hover:shadow-lg transition-all duration-200 team-card {{ $isInternational ? 'ring-2 ring-blue-500 bg-blue-50' : '' }} cursor-pointer" 
                       data-name="{{ strtolower($team->team_name) }}" 
                       data-type="{{ $isInternational ? 'international' : 'domestic' }}">
                        
                        <!-- Team Header -->
                        <div class="p-3 border-b border-gray-100">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-medium px-2 py-1 rounded-full 
                                    @if($isInternational) text-blue-700 bg-blue-100 @else text-gray-600 bg-gray-100 @endif">
                                    @if($isInternational) 🌍 @else 🏏 @endif
                                    {{ $isInternational ? 'International' : 'Domestic' }}
                                </span>
                                @if($isInternational)
                                    <span class="text-xs text-blue-600 font-bold">⭐</span>
                                @endif
                            </div>
                        </div>

                        <!-- Team Content -->
                        <div class="p-4">
                            <!-- Team Logo and Name -->
                            <div class="text-center">
                                @if($team->team_logo)
                                    <img src="{{ $team->team_logo }}" alt="{{ $team->team_name }}" 
                                         class="w-16 h-16 mx-auto mb-2 rounded-full border-2 border-gray-200">
                                @else
                                    <div class="w-16 h-16 mx-auto mb-2 bg-gradient-to-br from-blue-500 to-green-500 rounded-full flex items-center justify-center">
                                        <span class="text-xl font-bold text-white">{{ substr($team->team_name, 0, 2) }}</span>
                                    </div>
                                @endif
                                <h3 class="text-sm font-semibold text-gray-900 leading-tight">{{ $team->team_name }}</h3>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <!-- No Teams Found -->
            <div class="text-center py-12">
                <div class="text-gray-500 mb-4">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Teams Found</h3>
                <p class="text-gray-500">We couldn't find any cricket teams at the moment. Please try again later.</p>
            </div>
        @endif

        <!-- Load More Button -->
        @if(isset($hasMorePages) && $hasMorePages)
            <div class="text-center mt-8">
                <button class="bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700 transition-colors duration-200 font-medium">
                    Load More Teams
                </button>
            </div>
        @endif
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

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search');
    const filterSelect = document.getElementById('filter');
    const teamCards = document.querySelectorAll('.team-card');

    // Search functionality
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        filterTeams(searchTerm, filterSelect.value);
    });

    // Filter functionality
    filterSelect.addEventListener('change', function() {
        const filterValue = this.value.toLowerCase();
        filterTeams(searchInput.value.toLowerCase(), filterValue);
    });

    function filterTeams(searchTerm, filterValue) {
        teamCards.forEach(card => {
            const teamName = card.dataset.name;
            const teamType = card.dataset.type;
            
            const matchesSearch = teamName.includes(searchTerm);
            const matchesFilter = !filterValue || teamType.includes(filterValue);
            
            if (matchesSearch && matchesFilter) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }
});

// Function removed - no longer needed

function syncTeams() {
    const button = event.target;
    const originalText = button.innerHTML;
    
    // Show loading state
    button.innerHTML = '🔄 Syncing...';
    button.disabled = true;
    button.classList.add('opacity-50');
    
    // Make AJAX request to sync teams
    fetch('/cricket/sync-teams', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            alert('Teams synced successfully! ' + data.message);
            // Reload page to show new teams
            location.reload();
        } else {
            alert('Error syncing teams: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error syncing teams. Please try again.');
    })
    .finally(() => {
        // Restore button state
        button.innerHTML = originalText;
        button.disabled = false;
        button.classList.remove('opacity-50');
    });
}

function testApi() {
    const button = event.target;
    const originalText = button.innerHTML;
    
    // Show loading state
    button.innerHTML = '🧪 Testing...';
    button.disabled = true;
    button.classList.add('opacity-50');
    
    // Test the teams API endpoint
    fetch('/cricket/teams', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.text())
    .then(html => {
        // Check if we get a proper HTML response
        if (html.includes('Cricket Teams') && html.includes('team-card')) {
            alert('✅ API Test Successful! Teams page loaded correctly.');
        } else {
            alert('⚠️ API Test Partial Success. Response received but may have issues.');
        }
        console.log('API Test Response:', html.substring(0, 500) + '...');
    })
    .catch(error => {
        console.error('API Test Error:', error);
        alert('❌ API Test Failed: ' + error.message);
    })
    .finally(() => {
        // Restore button state
        button.innerHTML = originalText;
        button.disabled = false;
        button.classList.remove('opacity-50');
    });
}
</script>
@endsection







