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
        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // 'player', 'team', 'country'
            $table->string('reference_id'); // faceImageId, imageId, countryId
            $table->string('name'); // player name, team name, country name
            $table->longText('image_data')->nullable();
            $table->string('mime_type')->default('image/jpeg');
            $table->string('api_source')->default('cricbuzz'); // API source
            $table->timestamp('last_checked')->nullable(); // When we last verified the image exists
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['type', 'reference_id']);
            $table->index('name');
            $table->unique(['type', 'reference_id']); // Prevent duplicates
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('images');
    }
};
