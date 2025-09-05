@extends('layouts.app')

@section('title', 'ICC Rankings - CricZone.pk')
@section('description', 'Latest ICC cricket rankings for teams and players across all formats - ODI, T20, and Test cricket.')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">ICC Rankings</h1>
        <p class="text-gray-600">Latest cricket rankings for teams and players across all formats</p>
    </div>

    <!-- Category Tabs -->
    <div class="mb-8">
        <div class="tab-container rounded-xl p-1">
            <nav class="flex space-x-1">
                <a href="{{ route('rankings.index', ['category' => 'men', 'type' => $type]) }}" 
                   class="tab-item flex-1 py-3 px-4 text-center font-semibold text-sm rounded-lg transition-all duration-200 {{ $category === 'men' ? 'bg-blue-600 text-white shadow-md transform scale-105 active' : 'text-gray-600 hover:text-blue-600 hover:bg-blue-50' }}">
                    <div class="flex items-center justify-center space-x-2">
                        <svg class="tab-icon w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                        </svg>
                        <span>Men</span>
                    </div>
                </a>
                <a href="{{ route('rankings.index', ['category' => 'women', 'type' => $type]) }}" 
                   class="tab-item flex-1 py-3 px-4 text-center font-semibold text-sm rounded-lg transition-all duration-200 {{ $category === 'women' ? 'bg-pink-600 text-white shadow-md transform scale-105 active' : 'text-gray-600 hover:text-pink-600 hover:bg-pink-50' }}">
                    <div class="flex items-center justify-center space-x-2">
                        <svg class="tab-icon w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                        </svg>
                        <span>Women</span>
                    </div>
                </a>
            </nav>
        </div>
    </div>

    <!-- Type Tabs -->
    <div class="mb-8">
        <div class="tab-container rounded-xl p-1">
            <nav class="grid grid-cols-2 md:grid-cols-4 gap-1">
                <a href="{{ route('rankings.index', ['category' => $category, 'type' => 'team']) }}" 
                   class="tab-item py-3 px-4 text-center font-semibold text-sm rounded-lg transition-all duration-200 {{ $type === 'team' ? 'bg-green-600 text-white shadow-md transform scale-105 active' : 'text-gray-600 hover:text-green-600 hover:bg-green-50' }}">
                    <div class="flex flex-col items-center space-y-1">
                        <svg class="tab-icon w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" clip-rule="evenodd"></path>
                        </svg>
                        <span>Team</span>
                    </div>
                </a>
                <a href="{{ route('rankings.index', ['category' => $category, 'type' => 'batter']) }}" 
                   class="tab-item py-3 px-4 text-center font-semibold text-sm rounded-lg transition-all duration-200 {{ $type === 'batter' ? 'bg-orange-600 text-white shadow-md transform scale-105 active' : 'text-gray-600 hover:text-orange-600 hover:bg-orange-50' }}">
                    <div class="flex flex-col items-center space-y-1">
                        <svg class="tab-icon w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                        </svg>
                        <span>Batter</span>
                    </div>
                </a>
                <a href="{{ route('rankings.index', ['category' => $category, 'type' => 'bowler']) }}" 
                   class="tab-item py-3 px-4 text-center font-semibold text-sm rounded-lg transition-all duration-200 {{ $type === 'bowler' ? 'bg-red-600 text-white shadow-md transform scale-105 active' : 'text-gray-600 hover:text-red-600 hover:bg-red-50' }}">
                    <div class="flex flex-col items-center space-y-1">
                        <svg class="tab-icon w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l2-2a1 1 0 00-1.414-1.414L11 7.586V3a1 1 0 10-2 0v4.586l-.293-.293z" clip-rule="evenodd"></path>
                        </svg>
                        <span>Bowler</span>
                    </div>
                </a>
                <a href="{{ route('rankings.index', ['category' => $category, 'type' => 'all_rounder']) }}" 
                   class="tab-item py-3 px-4 text-center font-semibold text-sm rounded-lg transition-all duration-200 {{ $type === 'all_rounder' ? 'bg-purple-600 text-white shadow-md transform scale-105 active' : 'text-gray-600 hover:text-purple-600 hover:bg-purple-50' }}">
                    <div class="flex flex-col items-center space-y-1">
                        <svg class="tab-icon w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"></path>
                        </svg>
                        <span>All Rounder</span>
                    </div>
                </a>
            </nav>
        </div>
    </div>

    <!-- Rankings Content -->
    <div class="space-y-8">
        @if(isset($error))
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ $error }}
                </div>
            </div>
        @else
            <!-- Show all formats in 3 columns -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                @foreach(['odi', 't20', 'test'] as $format)
                    <div class="bg-white rounded-lg shadow-lg format-card">
                        @if($type === 'team')
                            @include('cricket.rankings.partials.team-rankings', ['format' => $format, 'rankingsData' => $rankingsData])
                        @else
                            @include('cricket.rankings.partials.player-rankings', ['format' => $format, 'rankingsData' => $rankingsData])
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Last Updated Info -->
    <div class="mt-6 text-center text-sm text-gray-500">
        <p>Rankings are updated every 3 days from ICC official data</p>
    </div>
</div>

<script>
function toggleFullList(tableId, button) {
    const tableBody = document.getElementById(tableId);
    const hiddenRows = tableBody.querySelectorAll('.full-list-row');
    const isExpanded = !hiddenRows[0].classList.contains('hidden');
    
    if (isExpanded) {
        // Collapse - hide all full list rows
        hiddenRows.forEach(row => {
            row.classList.add('hidden');
        });
        button.textContent = 'View Full List >';
        button.classList.remove('bg-gray-600');
        button.classList.add('bg-blue-600');
    } else {
        // Expand - show all full list rows
        hiddenRows.forEach(row => {
            row.classList.remove('hidden');
        });
        button.textContent = 'Show Less <';
        button.classList.remove('bg-blue-600');
        button.classList.add('bg-gray-600');
    }
}
</script>
@endsection
