@php
    // Get rankings data from controller
    $rankings = $rankingsData[$format]['rankings'] ?? collect();
    $topPlayer = $rankingsData[$format]['topRanking'] ?? null;
@endphp

<div class="p-4">
    <!-- Format Title -->
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-900">{{ strtoupper($format) }}</h3>
    </div>

    <!-- Top Player Display -->
    @if($topPlayer)
        <div class="mb-4 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="text-3xl font-bold text-blue-600">1</div>
                    <div>
                        <div class="flex items-center space-x-2">
                            <img src="{{ is_array($topPlayer) ? ($topPlayer['player_image_url'] ?? '/images/default-player.png') : ($topPlayer->player_image_url ?? '/images/default-player.png') }}" alt="{{ is_array($topPlayer) ? ($topPlayer['player_name'] ?? 'Unknown Player') : ($topPlayer->player_name ?? 'Unknown Player') }}" class="w-8 h-8 player-avatar rounded-full object-cover">
                            <div>
                                <div class="text-sm font-bold text-gray-900">{{ is_array($topPlayer) ? ucwords(strtolower($topPlayer['player_name'] ?? 'Unknown Player')) : ($topPlayer->formatted_player_name ?? 'Unknown Player') }}</div>
                                <div class="flex items-center space-x-1">
                                    <span class="text-xs text-gray-600">{{ is_array($topPlayer) ? ($topPlayer['team_code'] ?? 'UNK') : ($topPlayer->team_code ?? 'UNK') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-right">
                    <div class="flex items-center justify-end space-x-2">
                        <div class="text-2xl font-bold text-blue-600">{{ is_array($topPlayer) ? number_format($topPlayer['rating'] ?? 0) : ($topPlayer->formatted_rating ?? '0') }}</div>
                        @php
                            $topTrend = is_array($topPlayer) ? ($topPlayer['trend'] ?? '') : ($topPlayer->trend ?? '');
                        @endphp
                        @if($topTrend)
                            <span class="trend-indicator 
                                @if($topTrend === 'Up') text-green-500 @endif
                                @if($topTrend === 'Down') text-red-500 @endif
                                @if($topTrend === 'Flat' || $topTrend === '') text-gray-400 @endif
                                cursor-help text-lg" 
                                title="@if($topTrend === 'Up') Ranking Up @elseif($topTrend === 'Down') Ranking Down @else No Change @endif" 
                                data-tooltip="@if($topTrend === 'Up') Ranking Up @elseif($topTrend === 'Down') Ranking Down @else No Change @endif">
                                @if($topTrend === 'Up') ▲ @elseif($topTrend === 'Down') ▼ @else — @endif
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500">RATING</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Rankings Table -->
    <div>
        <table class="w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-2 py-1 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rank</th>
                    <th class="px-2 py-1 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-2 py-1 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Team</th>
                    <th class="px-2 py-1 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rating</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200" id="player-rankings-{{ $format }}-{{ $type }}">
                @forelse($rankings->take(10) as $ranking)
                    @if($ranking['rank'] > 1)
                        <tr class="hover:bg-gray-50">
                            <td class="px-2 py-1 text-sm font-medium text-gray-900">
                                <div class="flex items-center space-x-1">
                                    <span>{{$ranking->rank}}</span>
                                    @php
                                        $trend = $ranking->trend;
                                    @endphp
                                    @if($trend)
                                        <span class="trend-indicator 
                                            @if($trend === 'Up') text-green-500 @endif
                                            @if($trend === 'Down') text-red-500 @endif
                                            @if($trend === 'Flat' || $trend === '') text-gray-400 @endif
                                            cursor-help" 
                                            title="@if($trend === 'Up') Ranking Up @elseif($trend === 'Down') Ranking Down @else No Change @endif" 
                                            data-tooltip="@if($trend === 'Up') Ranking Up @elseif($trend === 'Down') Ranking Down @else No Change @endif">
                                            @if($trend === 'Up') ▲ @elseif($trend === 'Down') ▼ @else — @endif
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-2 py-1">
                                <div class="flex items-center">
                                    <div class="min-w-0 flex-1">
                                        <div class="text-xs font-medium text-gray-900 truncate">{{ is_array($ranking) ? ucwords(strtolower($ranking['player_name'] ?? 'Unknown Player')) : ($ranking->formatted_player_name ?? 'Unknown Player') }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-2 py-1">
                                <div class="flex items-center">
                                    <span class="text-xs text-gray-900">{{ is_array($ranking) ? ($ranking['team_code'] ?? 'UNK') : ($ranking->team_code ?? 'UNK') }}</span>
                                </div>
                            </td>
                            <td class="px-2 py-1 text-xs text-gray-900">
                                {{ is_array($ranking) ? number_format($ranking['rating'] ?? 0) : ($ranking->formatted_rating ?? '0') }}
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="4" class="px-2 py-1 text-center text-xs text-gray-500">
                            No rankings available
                        </td>
                    </tr>
                @endforelse
                
                <!-- Hidden rows for full list -->
                @foreach($rankings->skip(10) as $ranking)
                    <tr class="hover:bg-gray-50 hidden full-list-row">
                        <td class="px-2 py-1 text-sm font-medium text-gray-900">
                            <div class="flex items-center space-x-1">
                                <span>{{ $ranking->rank }}</span>
                                @php
                                    $trend = $ranking->trend;
                                @endphp
                                @if($trend)
                                    <span class="trend-indicator 
                                        @if($trend === 'Up') text-green-500 @endif
                                        @if($trend === 'Down') text-red-500 @endif
                                        @if($trend === 'Flat' || $trend === '') text-gray-400 @endif
                                        cursor-help" 
                                        title="@if($trend === 'Up') Ranking Up @elseif($trend === 'Down') Ranking Down @else No Change @endif" 
                                        data-tooltip="@if($trend === 'Up') Ranking Up @elseif($trend === 'Down') Ranking Down @else No Change @endif">
                                        @if($trend === 'Up') ▲ @elseif($trend === 'Down') ▼ @else — @endif
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-2 py-1">
                            <div class="flex items-center">
                                <div class="min-w-0 flex-1">
                                    <div class="text-xs font-medium text-gray-900 truncate">{{ is_array($ranking) ? ucwords(strtolower($ranking['player_name'] ?? 'Unknown Player')) : ($ranking->formatted_player_name ?? 'Unknown Player') }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-2 py-1">
                            <div class="flex items-center">
                                <span class="text-xs text-gray-900">{{ is_array($ranking) ? ($ranking['team_code'] ?? 'UNK') : ($ranking->team_code ?? 'UNK') }}</span>
                            </div>
                        </td>
                        <td class="px-2 py-1 text-xs text-gray-900">
                            {{ is_array($ranking) ? number_format($ranking['rating'] ?? 0) : ($ranking->formatted_rating ?? '0') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- View Full List Button -->
    <div class="mt-4 text-center">
        <button onclick="toggleFullList('player-rankings-{{ $format }}-{{ $type }}', this)" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-lg transition-colors duration-200">
            View Full List >
        </button>
    </div>
</div>