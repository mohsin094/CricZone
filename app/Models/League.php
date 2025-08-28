<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class League extends Model
{
    use HasFactory;

    protected $fillable = [
        'league_key',
        'league_name',
        'league_year',
        'cached_at'
    ];

    protected $casts = [
        'cached_at' => 'datetime'
    ];

    public function matches()
    {
        return $this->hasMany(CricketMatch::class, 'league_key', 'league_key');
    }
}

