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
        Schema::create('leagues', function (Blueprint $table) {
            $table->id();
            $table->string('league_key')->unique();
            $table->string('league_name');
            $table->string('league_year');
            $table->timestamp('cached_at')->nullable();
            $table->timestamps();
            
            $table->index('league_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leagues');
    }
};

