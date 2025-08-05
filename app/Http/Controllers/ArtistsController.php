<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\MusicBrainzService;
use App\Utils\ApiResponseUtil;
use Illuminate\Validation\ValidationException;

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
        try {
            $validated = $request->validate([
                'artist_mbid' => 'required|string|uuid'
            ]);

            $user = auth()->user();
            throw_if(!$user, \Exception::class, 'Unauthenticated', 401);

            $exists = $user->favoriteArtists()
                ->where('artist_mbid', $validated['artist_mbid'])
                ->exists();

            if ($exists) {
                return ApiResponseUtil::success('Artist already in favorites', [
                    'favorites' => $user->favoriteArtists()->pluck('artist_mbid')
                ]);
            }

            $user->favoriteArtists()->create([
                'artist_mbid' => $validated['artist_mbid']
            ]);

            return ApiResponseUtil::success(
                "Artist added to the favorites",
                [
                'favorites' => $user->favoriteArtists()->pluck('artist_mbid')
                ], 201
            );
        } catch (ValidationException $e) {
            return ApiResponseUtil::error(
                'Validation failed',
                $e->errors(),
                422
            );
        } catch (\Exception $e) {
            return ApiResponseUtil::error(
                $e->getCode() === 401 ? 'Unauthorized' : 'Failed to add favorite',
                ['error' => $e->getMessage()],
                $e->getCode() ?: 500
            );
        }
    }

    public function removeFavorite(Request $request)
    {
        try {
            $validated = $request->validate([
                'artist_mbid' => 'required|string|uuid'
            ]);

            $user = auth()->user();
            throw_if(!$user, \Exception::class, 'Unauthenticated', 401);

            $deleted = $user->favoriteArtists()
                ->where('artist_mbid', $validated['artist_mbid'])
                ->delete();

            if ($deleted === 0) {
                return ApiResponseUtil::error(
                    'Artist not found in favorites',
                    null,
                    404
                );
            }

            $user->favoriteArtists()
                ->where('artist_mbid', $validated['artist_mbid'])
                ->delete();

            return ApiResponseUtil::success('Artist removed from favorites');
        } catch (ValidationException $e) {
            return ApiResponseUtil::error(
                'Validation failed',
                $e->errors(),
                422
            );
        } catch (\Exception $e) {
            return ApiResponseUtil::error(
                $e->getCode() === 401 ? 'Unauthorized' : 'Failed to remove favorite',
                ['error' => $e->getMessage()],
                $e->getCode() ?: 500
            );
        }
    }

    public function listFavorites(Request $request)
    {
        try {
            $user = auth()->user();
            throw_if(!$user, \Exception::class, 'Unauthenticated', 401);

            $favorites = $user->favoriteArtists()
                ->orderBy('created_at', 'desc')
                ->get(['artist_mbid', 'created_at']);

            $artists = $favorites->map(function ($favorite) {
                try {
                    $artistData = $this->musicBrainz->getArtist($favorite->artist_mbid);

                    return [
                        'mbid' => $favorite->artist_mbid,
                        'name' => $artistData['name'] ?? 'Unknown Artist'
                    ];
                } catch (\Exception $e) {
                    return [
                        'mbid' => $favorite->artist_mbid,
                        'name' => 'Error loading artist',
                        'error' => $e->getMessage()
                    ];
                }
            });

            return ApiResponseUtil::success(
                'Favorite artists retrieved',
                $artists->filter()->values()->toArray()
            );
        } catch (\Exception $e) {
            return ApiResponseUtil::error(
                $e->getCode() === 401 ? 'Unauthorized' : 'Failed to retrieve favorites',
                ['error' => $e->getMessage()],
                $e->getCode() ?: 500
            );
        }
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

