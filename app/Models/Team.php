<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_key',
        'team_name',
        'team_logo',
        'cached_at'
    ];

    protected $casts = [
        'cached_at' => 'datetime'
    ];

    public function homeMatches()
    {
        return $this->hasMany(CricketMatch::class, 'home_team_key', 'team_key');
    }

    public function awayMatches()
    {
        return $this->hasMany(CricketMatch::class, 'away_team_key', 'team_key');
    }

    public function allMatches()
    {
        return $this->homeMatches()->union($this->awayMatches());
    }
}

