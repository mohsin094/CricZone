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
    <div class="mb-6">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8">
                <a href="{{ route('rankings.index', ['category' => 'men', 'type' => $type]) }}" 
                   class="py-2 px-1 border-b-2 font-medium text-sm {{ $category === 'men' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Men
                </a>
                <a href="{{ route('rankings.index', ['category' => 'women', 'type' => $type]) }}" 
                   class="py-2 px-1 border-b-2 font-medium text-sm {{ $category === 'women' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Women
                </a>
            </nav>
        </div>
    </div>

    <!-- Type Tabs -->
    <div class="mb-8">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8">
                <a href="{{ route('rankings.index', ['category' => $category, 'type' => 'team']) }}" 
                   class="py-2 px-1 border-b-2 font-medium text-sm {{ $type === 'team' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Team
                </a>
                <a href="{{ route('rankings.index', ['category' => $category, 'type' => 'batter']) }}" 
                   class="py-2 px-1 border-b-2 font-medium text-sm {{ $type === 'batter' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Batter
                </a>
                <a href="{{ route('rankings.index', ['category' => $category, 'type' => 'bowler']) }}" 
                   class="py-2 px-1 border-b-2 font-medium text-sm {{ $type === 'bowler' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Bowler
                </a>
                <a href="{{ route('rankings.index', ['category' => $category, 'type' => 'all_rounder']) }}" 
                   class="py-2 px-1 border-b-2 font-medium text-sm {{ $type === 'all_rounder' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    All Rounder
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
