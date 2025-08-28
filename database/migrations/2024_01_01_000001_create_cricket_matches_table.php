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
        Schema::create('cricket_matches', function (Blueprint $table) {
            $table->id();
            $table->string('event_key')->unique();
            $table->date('event_date_start');
            $table->date('event_date_stop');
            $table->string('event_time')->nullable();
            $table->string('event_home_team');
            $table->string('home_team_key');
            $table->string('event_away_team');
            $table->string('away_team_key');
            $table->string('event_service_home')->nullable();
            $table->string('event_service_away')->nullable();
            $table->string('event_home_final_result')->nullable();
            $table->string('event_away_final_result')->nullable();
            $table->string('event_home_rr')->nullable();
            $table->string('event_away_rr')->nullable();
            $table->string('event_status');
            $table->text('event_status_info')->nullable();
            $table->string('league_name');
            $table->string('league_key');
            $table->string('league_round')->nullable();
            $table->string('league_season');
            $table->boolean('event_live')->default(false);
            $table->string('event_type');
            $table->text('event_toss')->nullable();
            $table->string('event_man_of_match')->nullable();
            $table->string('event_stadium')->nullable();
            $table->string('event_home_team_logo')->nullable();
            $table->string('event_away_team_logo')->nullable();
            $table->json('scorecard_data')->nullable();
            $table->json('comments_data')->nullable();
            $table->json('wickets_data')->nullable();
            $table->json('extra_data')->nullable();
            $table->json('lineups_data')->nullable();
            $table->timestamp('cached_at')->nullable();
            $table->timestamps();
            
            $table->index(['event_live', 'event_date_start']);
            $table->index(['league_key', 'event_date_start']);
            $table->index(['home_team_key', 'away_team_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cricket_matches');
    }
};

