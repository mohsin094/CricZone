<?php

namespace App\Services;

use App\Models\TeamRanking;
use App\Models\PlayerRanking;
use App\Models\Image;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class RankingService
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.cricbuzz.api_key');
        $this->baseUrl = 'https://cricbuzz-cricket2.p.rapidapi.com';
    }

    /**
     * Fetch and update all rankings from Cricbuzz API
     */
    public function updateAllRankings()
    {
        try {
            Log::info('Starting ranking update process');

            // Update team rankings
            $this->updateTeamRankings();

            // Update player rankings
            $this->updatePlayerRankings();

            Log::info('Ranking update process completed successfully');
            return true;
        } catch (\Exception $e) {
            Log::error('Error updating rankings', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Update team rankings for all categories and formats
     */
    public function updateTeamRankings()
    {
        $categories = ['men', 'women'];
        $formats = ['odi', 't20', 'test'];

        foreach ($categories as $category) {
            foreach ($formats as $format) {
                try {
                    $this->updateTeamRankingsForFormat($category, $format);
                } catch (\Exception $e) {
                    Log::error("Error updating team rankings for {$category} {$format}", [
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
    }

    /**
     * Update team rankings for specific category and format
     */
    public function updateTeamRankingsForFormat($category, $format)
    {
        try {
            $rankings = $this->fetchTeamRankingsFromAPI($category, $format);

            if (empty($rankings)) {
                Log::warning("No team rankings found for {$category} {$format}");
                return;
            }

            // Clear existing rankings for this category and format
            TeamRanking::where('category', $category)
                ->where('format', $format)
                ->delete();

            foreach ($rankings['rank'] as $ranking) {
                $teamName = $ranking['name'];
                
                // This will save the team image to the images table
                $this->getTeamFlagUrl($teamName, $ranking['imageId'] ?? null);
                
                TeamRanking::create([
                    'category' => $category,
                    'format' => $format,
                    'team_name' => $teamName,
                    'team_code' => $this->getTeamCode($teamName),
                    'rank' => $ranking['rank'],
                    'matches' => $ranking['matches'],
                    'rating' => $ranking['rating'] ?? null,
                    'points' => $ranking['points'] ?? null,
                    'last_updated' => $ranking['lastUpdatedOn'],
                ]);
            }

            Log::info("Updated team rankings for {$category} {$format}", [
                'count' => count($rankings)
            ]);
        } catch (\Exception $e) {
            Log::error("Error updating team rankings for {$category} {$format}", [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Update player rankings for all categories, types, and formats
     */
    public function updatePlayerRankings()
    {
        $categories = ['men', 'women'];
        $types = ['batter', 'bowler', 'all_rounder'];
        $formats = ['odi', 't20', 'test'];

        foreach ($categories as $category) {
            foreach ($types as $type) {
                foreach ($formats as $format) {
                    try {
                        $this->updatePlayerRankingsForType($category, $type, $format);
                    } catch (\Exception $e) {
                        Log::error("Error updating player rankings for {$category} {$type} {$format}", [
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Update player rankings for specific category, type, and format
     */
    public function updatePlayerRankingsForType($category, $type, $format)
    {
        try {
            $rankings = $this->fetchPlayerRankingsFromAPI($category, $type, $format);

            if (empty($rankings)) {
                Log::warning("No player rankings found for {$category} {$type} {$format}");
                return;
            }

            // Clear existing rankings for this category, type, and format
            PlayerRanking::where('category', $category)
                ->where('type', $type)
                ->where('format', $format)
                ->delete();

            // Store new rankings
            foreach ($rankings as $ranking) {
                PlayerRanking::create([
                    'category' => $category,
                    'type' => $type,
                    'format' => $format,
                    'player_name' => $ranking['player_name'],
                    'team_name' => $ranking['team_name'],
                    'team_code' => $ranking['team_code'],
                    'rank' => $ranking['rank'] ?? null,
                    'rating' => $ranking['rating'] ?? null,
                    'points' => $ranking['points'] ?? null,
                    'trend' => $ranking['trend'] ?? null,
                    'statistics' => $ranking['statistics'] ?? null,
                    'metadata' => $ranking['metadata'] ?? null,
                    'last_updated' => now(),
                ]);
            }

            Log::info("Updated player rankings for {$category} {$type} {$format}", [
                'count' => count($rankings)
            ]);
        } catch (\Exception $e) {
            Log::error("Error updating player rankings for {$category} {$type} {$format}", [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Fetch team rankings from Cricbuzz API
     * Note: Cricbuzz API doesn't have separate team rankings endpoint
     * We'll use the all-rounders endpoint as it contains team-based data
     */
    public function fetchTeamRankingsFromAPI($category, $format)
    {
        try {
            // Map our format names to Cricbuzz API format names

            $endpoint = "/stats/v1/rankings/teams";
            $params = [
                'formatType' => $format,
                'isWomen' => $category === 'women' ? '1' : '0'
            ];

            $response = Http::timeout(30)
                ->withHeaders([
                    'x-rapidapi-key' => $this->apiKey,
                    'x-rapidapi-host' => 'cricbuzz-cricket2.p.rapidapi.com'
                ])
                ->get($this->baseUrl . $endpoint, $params);
            if ($response->successful()) {
                $data = $response->json();
                return $data;
            }

            Log::error("Failed to fetch team rankings from API", [
                'category' => $category,
                'format' => $format,
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            // Return empty array if API fails
            return [];
        } catch (\Exception $e) {
            Log::error("Exception while fetching team rankings", [
                'category' => $category,
                'format' => $format,
                'error' => $e->getMessage()
            ]);
            // Return empty array if API fails
            return [];
        }
    }

    /**
     * Fetch player rankings from Cricbuzz API
     */
    public function fetchPlayerRankingsFromAPI($category, $type, $format)
    {
        try {
            // Map our format names to Cricbuzz API format names
            $apiType = $this->mapTypeToAPI($type);

            $endpoint = "/stats/v1/rankings/{$apiType}";
            $params = [
                'formatType' => $format,
                'isWomen' => $category === 'women' ? '1' : '0'
            ];

            $response = Http::timeout(30)
                ->withHeaders([
                    'x-rapidapi-key' => $this->apiKey,
                    'x-rapidapi-host' => 'cricbuzz-cricket2.p.rapidapi.com'
                ])
                ->get($this->baseUrl . $endpoint, $params);

            if ($response->successful()) {
                $data = $response->json();
                return $this->transformPlayerRankingsData($data, $category, $type, $format);
            }

            Log::error("Failed to fetch player rankings from API", [
                'category' => $category,
                'type' => $type,
                'format' => $format,
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            // Return empty array if API fails
            return [];
        } catch (\Exception $e) {
            Log::error("Exception while fetching player rankings", [
                'category' => $category,
                'type' => $type,
                'format' => $format,
                'error' => $e->getMessage()
            ]);
            // Return empty array if API fails
            return [];
        }
    }

    /**
     * Transform player rankings data from API response
     */
    private function transformPlayerRankingsData($data, $category, $type, $format)
    {
        $rankings = [];

        if (empty($data) || !isset($data['rank'])) {
            return $rankings;
        }

        foreach ($data['rank'] as $player) {
            $playerName = $player['name'] ?? 'Unknown Player';
            $countryName = $player['country'] ?? 'Unknown Team';
            
            // These calls will save images to the images table
            $playerImageUrl = $this->getPlayerImageUrl($player['faceImageId'] ?? null, $playerName);
            $teamFlagUrl = $this->getCountryFlagUrl($countryName, $player['countryId'] ?? null);
            
            $rankings[] = [
                'player_name' => $playerName,
                'team_name' => $countryName,
                'team_code' => $this->getTeamCode($countryName),
                'player_image_url' => $playerImageUrl,
                'team_flag_url' => $teamFlagUrl,
                'rank' => (int)($player['rank'] ?? 0),
                'trend' => $player['trend'] ?? '',
                'rating' => (int)($player['rating'] ?? 0),
                'points' => (int)($player['points'] ?? 0),
                'statistics' => $this->extractPlayerStatistics($player, $type),
                'metadata' => [
                    'difference' => $player['difference'] ?? null,
                    'trend' => $player['trend'] ?? 'Flat',
                    'lastUpdatedOn' => $player['lastUpdatedOn'] ?? null,
                    'player_id' => $player['id'] ?? null,
                    'country_id' => $player['countryId'] ?? null,
                ]
            ];
        }

        return $rankings;
    }

    /**
     * Extract player statistics based on type
     */
    private function extractPlayerStatistics($player, $type)
    {
        $stats = [];

        switch ($type) {
            case 'batter':
                $stats = [
                    'runs' => $player['runs'] ?? 0,
                    'matches' => $player['matches'] ?? 0,
                    'average' => $player['average'] ?? 0,
                    'strike_rate' => $player['strikeRate'] ?? 0,
                    'rating' => (int)($player['rating'] ?? 0),
                ];
                break;
            case 'bowler':
                $stats = [
                    'wickets' => $player['wickets'] ?? 0,
                    'matches' => $player['matches'] ?? 0,
                    'average' => $player['average'] ?? 0,
                    'economy' => $player['economy'] ?? 0,
                    'rating' => (int)($player['rating'] ?? 0),
                ];
                break;
            case 'all_rounder':
                $stats = [
                    'runs' => $player['runs'] ?? 0,
                    'wickets' => $player['wickets'] ?? 0,
                    'matches' => $player['matches'] ?? 0,
                    'rating' => (int)($player['rating'] ?? 0),
                ];
                break;
        }

        return $stats;
    }

    /**
     * Map type names to API type names
     */
    private function mapTypeToAPI($type)
    {
        $mapping = [
            'batter' => 'batsmen',
            'bowler' => 'bowlers',
            'all_rounder' => 'allrounders'
        ];

        return $mapping[$type] ?? $type;
    }

    /**
     * Get team code from team name
     */
    private function getTeamCode($teamName)
    {
        $teamCodes = [
            'India' => 'IND',
            'Australia' => 'AUS',
            'England' => 'ENG',
            'South Africa' => 'SA',
            'New Zealand' => 'NZ',
            'Pakistan' => 'PAK',
            'Sri Lanka' => 'SL',
            'Bangladesh' => 'BAN',
            'West Indies' => 'WI',
            'Afghanistan' => 'AFG',
            'Ireland' => 'IRE',
            'Zimbabwe' => 'ZIM',
            'Netherlands' => 'NED',
            'Scotland' => 'SCO',
            'Oman' => 'OMN',
            'Namibia' => 'NAM',
            'United States' => 'USA',
            'Canada' => 'CAN',
            'United Arab Emirates' => 'UAE',
            'Nepal' => 'NEP',
        ];

        return $teamCodes[$teamName] ?? substr(strtoupper($teamName), 0, 3);
    }

    /**
     * Get team flag URL using images table
     */
    private function getTeamFlagUrl($teamName, $imageId = null)
    {
        if ($imageId) {
            return Image::getTeamImageUrl($imageId, $teamName);
        }
        
        // Fallback to default flag if no imageId
        return '/images/default-flag.png';
    }

    /**
     * Get player image URL using images table
     */
    private function getPlayerImageUrl($faceImageId, $playerName = 'Unknown Player')
    {
        if (!$faceImageId) {
            return '/images/default-player.png';
        }

        return Image::getPlayerImageUrl($faceImageId, $playerName);
    }

    /**
     * Get country flag URL using images table
     */
    private function getCountryFlagUrl($countryName, $countryId = null)
    {
        if ($countryId) {
            return Image::getCountryImageUrl($countryId, $countryName);
        }
        
        // Fallback to default flag if no countryId
        return '/images/default-flag.png';
    }


    /**
     * Get team rankings from database
     */
    public function getTeamRankings($category, $format, $limit = null)
    {
        $query = TeamRanking::category($category)
            ->format($format)
            ->orderBy('rank');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Get player rankings from database
     */
    public function getPlayerRankings($category, $type, $format, $limit = null)
    {
        $query = PlayerRanking::category($category)
            ->type($type)
            ->format($format)
            ->orderBy('rank');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Get top player for a specific category, type, and format
     */
    public function getTopPlayer($category, $type, $format)
    {
        return PlayerRanking::category($category)
            ->type($type)
            ->format($format)
            ->orderBy('rank')
            ->first();
    }

    /**
     * Get top team for a specific category and format
     */
    public function getTopTeam($category, $format)
    {
        return TeamRanking::category($category)
            ->format($format)
            ->orderBy('rank')
            ->first();
    }



    /**
     * Check if rankings need update (older than 3 days)
     */
    public function needsUpdate($category, $format, $type = null)
    {
        $query = $type
            ? PlayerRanking::category($category)->type($type)->format($format)
            : TeamRanking::category($category)->format($format);

        $latest = $query->orderBy('last_updated', 'desc')->first();

        if (!$latest) {
            return true;
        }

        return $latest->last_updated->diffInDays(now()) >= 3;
    }
}
