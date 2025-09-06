<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('player_rankings', function (Blueprint $table) {
            $table->id();
            $table->string('category'); // 'men' or 'women'
            $table->string('type'); // 'batter', 'bowler', 'all_rounder'
            $table->string('format'); // 'odi', 't20', 'test'
            $table->string('player_name');
            $table->string('team_name');
            $table->string('team_code', 3); // 3-letter country code
            $table->integer('rank')->nullable();
            $table->integer('rating')->nullable();
            $table->integer('points')->nullable();
            $table->string('trend')->nullable();
            $table->json('statistics')->nullable(); // Additional stats like runs, wickets, etc.
            $table->json('metadata')->nullable(); // Additional data like trend, previous rank, etc.
            $table->timestamp('last_updated');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_rankings');
    }
};
