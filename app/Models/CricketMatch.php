<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CricketMatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_key',
        'event_date_start',
        'event_date_stop',
        'event_time',
        'event_home_team',
        'home_team_key',
        'event_away_team',
        'away_team_key',
        'event_service_home',
        'event_service_away',
        'event_home_final_result',
        'event_away_final_result',
        'event_home_rr',
        'event_away_rr',
        'event_status',
        'event_status_info',
        'league_name',
        'league_key',
        'league_round',
        'league_season',
        'event_live',
        'event_type',
        'event_toss',
        'event_man_of_match',
        'event_stadium',
        'event_home_team_logo',
        'event_away_team_logo',
        'scorecard_data',
        'comments_data',
        'wickets_data',
        'extra_data',
        'lineups_data',
        'cached_at'
    ];

    protected $casts = [
        'event_date_start' => 'date',
        'event_date_stop' => 'date',
        'event_live' => 'boolean',
        'scorecard_data' => 'array',
        'comments_data' => 'array',
        'wickets_data' => 'array',
        'extra_data' => 'array',
        'lineups_data' => 'array',
        'cached_at' => 'datetime'
    ];

    public function scopeLive($query)
    {
        return $query->where('event_live', true);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('event_date_start', today());
    }

    public function scopeUpcoming($query)
    {
        return $query->where('event_date_start', '>', now());
    }

    public function scopeFinished($query)
    {
        return $query->where('event_status', 'Finished');
    }
}

