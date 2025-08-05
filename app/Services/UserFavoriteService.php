<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\User;

class UserFavoriteService
{
    public function __construct(
        protected MusicBrainzService $musicBrainz
    ) {}

    public function getFavorites(User $user, bool $withDetails = false)
    {
        return $user->favoriteArtists()
            ->orderBy('created_at', 'desc')
            ->get(['artist_mbid', 'created_at'])
            ->map(function ($favorite) {
                $details = $this->musicBrainz->getArtistDetails($favorite->artist_mbid);

                return [
                    'mbid' => $favorite->artist_mbid,
                    'added_at' => $favorite->created_at->toIso8601String(),
                    'details' => $details
                ];
            })
        ->toArray();
    }
}