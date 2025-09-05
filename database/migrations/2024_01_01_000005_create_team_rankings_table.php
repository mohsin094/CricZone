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
        Schema::create('team_rankings', function (Blueprint $table) {
            $table->id();
            $table->string('category'); // 'men' or 'women'
            $table->string('format'); // 'odi', 't20', 'test'
            $table->integer('rank')->nullable();
            $table->string('team_name');
            $table->integer('rating')->nullable();
            $table->integer('matches')->nullable();
            $table->integer('points')->nullable();
            $table->string('team_code', 3)->nullable(); // 3-letter country code
            $table->timestamp('last_updated');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_rankings');
    }
};
