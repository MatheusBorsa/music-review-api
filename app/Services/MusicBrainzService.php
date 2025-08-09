<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class MusicBrainzService
{
    protected string $baseUri = 'https://musicbrainz.org/ws/2/';
    protected string $userAgent;

    public function __construct()
    {
        $this->userAgent = config('services.musicbrainz.user_agent');
    }

    public function searchArtists(string $query, int $limit = 10): array
    {
        $response = Http::withHeaders([
            'User-Agent' => $this->userAgent
        ])->get($this->baseUri . 'artist/', [
            'query' => $query,
            'fmt' => 'json',
            'limit' => $limit
        ]);

        return $response->json()['artists'] ?? [];
    }

        public function getArtist(string $mbid): array
    {
        $response = Http::withHeaders([
            'User-Agent' => $this->userAgent
        ])->get($this->baseUri . "artist/{$mbid}", [
            'fmt' => 'json',
            'inc' => 'url-rels+release-groups'
        ]);

        return $response->json();
    }

    public function getArtistsDetails(array $mbids)
    {
        $responses = Http::pool(callback: function ($pool) use ($mbids) {
            return array_map(function ($mbid) use ($pool) {
                return $pool->withHeaders([
                    'User-Agent' => $this->userAgent
                ])->get($this->baseUri . "artist/{$mbid}", [
                    'fmt' => 'json',
                    'inc' => 'url-rels+release-groups'
                ]);
            }, $mbids);
        });

        $results = [];
        foreach ($responses as $index => $response) {
            if ($response->successful()) {
                $results[$mbids[$index]] = $response->json();
            }
        }
        return $results;
    }
}