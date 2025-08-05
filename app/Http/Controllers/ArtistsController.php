<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\MusicBrainzService;
use App\Utils\ApiResponseUtil;

class ArtistsController extends Controller
{
    protected MusicBrainzService $musicBrainz;

    public function __construct(MusicBrainzService $musicBrainz)
    {
       $this->musicBrainz = $musicBrainz; 
    }

    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:2',
            'limit' => 'sometimes|integer|min:1|max:100'
        ]);

        try {
            $artists = $this->musicBrainz->searchArtists(
                $request->query('query'),
                $request->query('limit', 10)
            );

            return ApiResponseUtil::success(
                'Artists retrieved successfully',
                $this->formatArtists($artists)   
            );
        } catch (\Exception $e) {
            return ApiResponseUtil::error(
                'Failed to fetch artists',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    public function addFavorite(Request $request)
    {
        $validated = $request->validate([
            'artist_mbid' => 'required|string|uuid'
        ]);

        $user = auth()->user();

        $user->favoriteArtists()->create([
            'artist_mbid' => $validated['artist_mbid']
        ]);

        return ApiResponseUtil::success("Artist added to the favorites", [
            'favorites' => $user->favoriteArtists()->pluck('artist_mbid')
        ]);
    }

    protected function formatArtists(array $artists)
    {
        return collect($artists)->map(function ($artist) {
            return [
                'mbid' => $artist['id'],
                'name' => $artist['name'],
                'type' => $artist['type'] ?? null,
                'country' => $artist['country'] ?? null,
                'description' => $artist['disambiguation'] ?? null
            ];
        })->toArray();
    }
}

