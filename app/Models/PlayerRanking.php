<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayerRanking extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'type',
        'format',
        'player_name',
        'team_name',
        'team_code',
        'player_image_url',
        'rank',
        'rating',
        'trend',
        'points',
        'statistics',
        'metadata',
        'last_updated',
    ];

    protected $casts = [
        'statistics' => 'array',
        'metadata' => 'array',
        'last_updated' => 'datetime',
    ];

    /**
     * Scope for filtering by category
     */
    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope for filtering by type
     */
    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for filtering by format
     */
    public function scopeFormat($query, $format)
    {
        return $query->where('format', $format);
    }

    /**
     * Scope for getting top N players
     */
    public function scopeTop($query, $limit = 10)
    {
        return $query->orderBy('rank')->limit($limit);
    }

    /**
     * Get player image URL with fallback
     */
    public function getPlayerImageUrlAttribute($value)
    {
        if ($value) {
            return $value;
        }

        // Fallback to a default player image
        return '/images/default-player.png';
    }

    /**
     * Get formatted rating
     */
    public function getFormattedRatingAttribute()
    {
        return number_format($this->rating);
    }

    /**
     * Get trend icon
     */
    public function getTrendIconAttribute()
    {
        switch ($this->trend) {
            case 'up':
                return '↗';
            case 'down':
                return '↘';
            default:
                return '→';
        }
    }

    /**
     * Get ranking change
     */
    public function getRankingChangeAttribute()
    {
        if (!$this->metadata || !isset($this->metadata['previous_rank'])) {
            return 0;
        }

        return $this->metadata['previous_rank'] - $this->rank;
    }

    /**
     * Get player statistics for display
     */
    public function getDisplayStatisticsAttribute()
    {
        if (!$this->statistics) {
            return [];
        }

        $stats = $this->statistics;
        $displayStats = [];

        switch ($this->type) {
            case 'batter':
                $displayStats = [
                    'runs' => $stats['runs'] ?? 0,
                    'matches' => $stats['matches'] ?? 0,
                    'average' => $stats['average'] ?? 0,
                    'strike_rate' => $stats['strike_rate'] ?? 0,
                ];
                break;
            case 'bowler':
                $displayStats = [
                    'wickets' => $stats['wickets'] ?? 0,
                    'matches' => $stats['matches'] ?? 0,
                    'average' => $stats['average'] ?? 0,
                    'economy' => $stats['economy'] ?? 0,
                ];
                break;
            case 'all_rounder':
                $displayStats = [
                    'runs' => $stats['runs'] ?? 0,
                    'wickets' => $stats['wickets'] ?? 0,
                    'matches' => $stats['matches'] ?? 0,
                    'rating' => $this->rating,
                ];
                break;
        }

        return $displayStats;
    }

    /**
     * Get formatted player name
     */
    public function getFormattedPlayerNameAttribute()
    {
        return ucwords(strtolower($this->player_name));
    }
}
