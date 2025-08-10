<?php

namespace App\Services;

use App\Models\User;

class UserFavoriteService
{
    public function __construct(
        protected MusicBrainzService $musicBrainz
    ) {}

    public function getFavorites(User $user)
    {
        $favorites = $user->favoriteArtists()
            ->orderBy('created_at', 'desc')
            ->get(['artist_mbid', 'created_at']);

        if ($favorites->isEmpty()) {
            return [];
        }

        $mbids = $favorites->pluck('artist_mbid')->all();
        $artistDetails = $this->musicBrainz->getArtistsDetails($mbids);

        return $favorites->map(function ($favorite) use ($artistDetails) {
            $details = $artistDetails[$favorite->artist_mbid] ?? [];

            return [
            'mbid'        => $favorite->artist_mbid,
            'added_at'    => $favorite->created_at->toIso8601String(),
            'name'        => $details['name'] ?? null,
            'type'        => $details['type'] ?? null,
            'country'     => $details['country'] ?? null,
            'description' => $details['disambiguation'] ?? null
            ];
        })->toArray();
    }
}