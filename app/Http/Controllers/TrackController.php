<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\MusicBrainzService;
use App\Utils\ApiResponseUtil;

class TrackController extends Controller
{
    protected MusicBrainzService $musicBrainz;

    public function __construct(
        MusicBrainzService $musicBrainz,
    )
    {
        $this->musicBrainz = $musicBrainz;
    }

    //Helper method
    protected function formatTracks(array $tracks)
    {
        return array_map(function ($track) {
            return [
            'mbid'     => $track['id'] ?? null,
            'title'    => $track['title'] ?? null,
            'length'   => isset($track['length'])
                ? floor(($track['length'] / 1000) / 60) . ':' . str_pad(floor(($track['length'] / 1000) % 60), 2, '0', STR_PAD_LEFT)
                : null,
            'artist'   => $track['artist-credit'][0]['name'] ?? null,
            'release'  => $track['releases'][0]['title'] ?? null
            ];
        }, $tracks);
    }

        public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:2',
            'limit' => 'sometimes|integer|min:1|max:100'
        ]);

        try {
            $artists = $this->musicBrainz->searchTracks(
                $request->query('query'),
                $request->query('limit', 10)
            );

            return ApiResponseUtil::success(
                'Tracks retrieved successfully',
                $this->formatTracks($artists)   
            );
        } catch (\Exception $e) {
            return ApiResponseUtil::error(
                'Failed to fetch artists',
                ['error' => $e->getMessage()],
                500
            );
        }
    }
}