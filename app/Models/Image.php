<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Image extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'reference_id',
        'name',
        'image_data',
        'mime_type',
        'api_source',
        'last_checked',
    ];

    protected $casts = [
        'last_checked' => 'datetime',
    ];

    /**
     * Scope for filtering by type
     */
    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for filtering by reference ID
     */
    public function scopeReferenceId($query, $referenceId)
    {
        return $query->where('reference_id', $referenceId);
    }

    /**
     * Get or create image data URL
     */
    public static function getOrCreateImageDataUrl($type, $referenceId, $name, $fallbackUrl = null)
    {
        // First, try to find existing image
        $image = static::where('type', $type)
                      ->where('reference_id', $referenceId)
                      ->first();

        if ($image) {
            // Update last_checked timestamp
            $image->update(['last_checked' => now()]);
            return static::createDataUrl($image->image_data, $image->mime_type);
        }

        // If not found and we have a reference ID, fetch from API and create new entry
        if ($referenceId) {
            $imageData = static::fetchImageDataFromCricbuzz($referenceId);
            
            if ($imageData) {
                static::create([
                    'type' => $type,
                    'reference_id' => $referenceId,
                    'name' => $name,
                    'image_data' => $imageData['data'],
                    'mime_type' => $imageData['mime_type'],
                    'api_source' => 'cricbuzz',
                    'last_checked' => now(),
                ]);

                return static::createDataUrl($imageData['data'], $imageData['mime_type']);
            }
        }

        // Return fallback URL if no reference ID or API call failed
        return $fallbackUrl ?? '/images/default-' . $type . '.png';
    }

    /**
     * Create data URL from base64 image data
     */
    private static function createDataUrl($imageData, $mimeType)
    {
        return 'data:' . $mimeType . ';base64,' . $imageData;
    }

    /**
     * Fetch image data from Cricbuzz API using the provided imageId
     */
    private static function fetchImageDataFromCricbuzz($imageId)
    {
        if (!$imageId) {
            return null;
        }

        $apiKey = env('CRICBUZZ_API_KEY');
        $host = 'cricbuzz-cricket.p.rapidapi.com';
        $url = "https://{$host}/img/v1/i1/c{$imageId}/i.jpg";

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'x-rapidapi-key' => $apiKey,
                    'x-rapidapi-host' => $host
                ])
                ->get($url);

            if ($response->successful()) {
                // Get the image content and MIME type
                $imageContent = $response->body();
                $mimeType = $response->header('Content-Type') ?: 'image/jpeg';
                
                // Encode to base64
                $base64Data = base64_encode($imageContent);
                
                return [
                    'data' => $base64Data,
                    'mime_type' => $mimeType
                ];
            }

            Log::warning("Failed to fetch image from Cricbuzz API", [
                'image_id' => $imageId,
                'status' => $response->status(),
                'response' => $response->body()
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error("Exception while fetching image from Cricbuzz API", [
                'image_id' => $imageId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get player image data URL
     */
    public static function getPlayerImageUrl($faceImageId, $playerName)
    {
        return static::getOrCreateImageDataUrl('player', $faceImageId, $playerName, null);
    }

    /**
     * Get team image data URL
     */
    public static function getTeamImageUrl($imageId, $teamName)
    {
        return static::getOrCreateImageDataUrl('team', $imageId, $teamName, null);
    }

    /**
     * Get country image data URL
     */
    public static function getCountryImageUrl($countryId, $countryName)
    {
        return static::getOrCreateImageDataUrl('country', $countryId, $countryName, null);
    }
}
