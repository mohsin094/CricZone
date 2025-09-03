<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamRanking extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'category',
        'format',
        'rank',
        'team_name',
        'rating',
        'matches',
        'points',
        'team_flag_url',
        'team_code',
        'last_updated',
    ];

    protected $casts = [
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
     * Scope for filtering by format
     */
    public function scopeFormat($query, $format)
    {
        return $query->where('format', $format);
    }

    /**
     * Scope for getting top N teams
     */
    public function scopeTop($query, $limit = 10)
    {
        return $query->orderBy('rank')->limit($limit);
    }

    /**
     * Get team flag URL with fallback
     */
    public function getTeamFlagUrlAttribute($value)
    {
        if ($value) {
            return $value;
        }

        // Fallback to a default flag or generate one based on team code
        return "https://flagcdn.com/16x12/{$this->team_code}.png";
    }

    /**
     * Get formatted rating
     */
    public function getFormattedRatingAttribute()
    {
        return number_format($this->rating);
    }

    /**
     * Get ranking trend (up, down, or same)
     */
    public function getTrendAttribute()
    {
        if (!$this->metadata || !isset($this->metadata['previous_rank'])) {
            return 'same';
        }

        $previousRank = $this->metadata['previous_rank'];
        
        if ($this->rank < $previousRank) {
            return 'up';
        } elseif ($this->rank > $previousRank) {
            return 'down';
        }
        
        return 'same';
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
}
