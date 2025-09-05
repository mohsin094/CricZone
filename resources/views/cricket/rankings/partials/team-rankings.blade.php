@php
// Get rankings data from controller
$rankings = $rankingsData[$format]['rankings'] ?? collect();
$topTeam = $rankingsData[$format]['topRanking'] ?? null;
@endphp
@if(!$rankings->isEmpty())
<div class="p-4">
    <!-- Format Title -->
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-900">{{ strtoupper($format) }}</h3>
    </div>

    <!-- Top Team Display -->
    @if($topTeam)
    <div class="mb-4 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="text-3xl font-bold text-blue-600">1</div>
                <div>
                    <div class="flex items-center space-x-2">
                        @php
                        $topTeamFlagUrl = is_array($topTeam) ? ($topTeam['team_flag_url'] ?? null) : ($topTeam->team_flag_url ?? null);
                        @endphp
                        @if($topTeamFlagUrl)
                        <img src="{{ $topTeamFlagUrl }}" alt="{{ is_array($topTeam) ? ($topTeam['team_name'] ?? 'Unknown Team') : ($topTeam->team_name ?? 'Unknown Team') }}" class="w-6 h-4 team-flag">
                        @endif
                        <span class="text-lg font-bold text-gray-900">{{ is_array($topTeam) ? ($topTeam['team_code'] ?? 'UNK') : ($topTeam->team_code ?? 'UNK') }}</span>
                    </div>
                    <p class="text-sm text-gray-600">{{ is_array($topTeam) ? ($topTeam['team_name'] ?? 'Unknown Team') : ($topTeam->team_name ?? 'Unknown Team') }}</p>
                </div>
            </div>
            <div class="text-right">
                <div class="text-2xl font-bold text-blue-600">{{ is_array($topTeam) ? number_format($topTeam['rating'] ?? 0) : ($topTeam->formatted_rating ?? '0') }}</div>
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
                    <th class="px-2 py-1 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Team</th>
                    <th class="px-2 py-1 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rating</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200" id="team-rankings-{{ $format }}">
                @forelse($rankings->take(10) as $ranking)
                @if($ranking['rank'] > 1)
                <tr class="hover:bg-gray-50">
                    <td class="px-2 py-1 text-sm font-medium text-gray-900">
                        {{ is_array($ranking) ? ($ranking['rank'] ?? 0) : ($ranking->rank ?? 0) }}
                    </td>
                    <td class="px-2 py-1">
                        <div class="flex items-center">
                            @php
                            $teamFlagUrl = is_array($ranking) ? ($ranking['team_flag_url'] ?? null) : ($ranking->team_flag_url ?? null);
                            @endphp
                            @if($teamFlagUrl)
                            <img src="{{ $teamFlagUrl }}" alt="{{ is_array($ranking) ? ($ranking['team_name'] ?? 'Unknown Team') : ($ranking->team_name ?? 'Unknown Team') }}" class="w-5 h-3 team-flag mr-2">
                            @endif
                            <div class="min-w-0 flex-1">
                                <div class="text-xs font-medium text-gray-900 truncate">{{ is_array($ranking) ? ($ranking['team_code'] ?? 'UNK') : ($ranking->team_code ?? 'UNK') }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-2 py-1 text-xs text-gray-900">
                        {{ is_array($ranking) ? number_format($ranking['rating'] ?? 0) : ($ranking->formatted_rating ?? '0') }}
                    </td>
                </tr>
                @endif
                @empty
                <tr>
                    <td colspan="3" class="px-2 py-1 text-center text-xs text-gray-500">
                        No rankings available
                    </td>
                </tr>
                @endforelse

                <!-- Hidden rows for full list -->
                @foreach($rankings->skip(10) as $ranking)
                <tr class="hover:bg-gray-50 hidden full-list-row">
                    <td class="px-2 py-1 text-sm font-medium text-gray-900">
                        {{ is_array($ranking) ? ($ranking['rank'] ?? 0) : ($ranking->rank ?? 0) }}
                    </td>
                    <td class="px-2 py-1">
                        <div class="flex items-center">
                            @php
                            $teamFlagUrl = is_array($ranking) ? ($ranking['team_flag_url'] ?? null) : ($ranking->team_flag_url ?? null);
                            @endphp
                            @if($teamFlagUrl)
                            <img src="{{ $teamFlagUrl }}" alt="{{ is_array($ranking) ? ($ranking['team_name'] ?? 'Unknown Team') : ($ranking->team_name ?? 'Unknown Team') }}" class="w-5 h-3 team-flag mr-2">
                            @endif
                            <div class="min-w-0 flex-1">
                                <div class="text-xs font-medium text-gray-900 truncate">{{ is_array($ranking) ? ($ranking['team_code'] ?? 'UNK') : ($ranking->team_code ?? 'UNK') }}</div>
                            </div>
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
        <button onclick="toggleFullList('team-rankings-{{ $format }}', this)" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-lg transition-colors duration-200">
            View Full List >
        </button>
    </div>
</div>
@endif