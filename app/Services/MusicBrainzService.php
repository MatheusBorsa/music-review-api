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

    //Helper methods

    private function request(string $endpoint, array $params = [])
    {
        $response = Http::withHeaders([
            'User-Agent' => $this->userAgent
        ])->get($this->baseUri . $endpoint, array_merge(['fmt' => 'json'], $params));

        return $response->json();
    }

    private function bulkRequest(array $mbids, string $type, array $params = [])
    {
        $responses = Http::pool(function ($pool) use ($mbids, $type, $params) {
            return array_map(function ($mbid) use ($pool, $type, $params) {
                return $pool->withHeaders([
                    'User-Agent' => $this->userAgent
                ])->get($this->baseUri . "{$type}/{$mbid}", array_merge([
                    'fmt' => 'json'
                ], $params));
            }, $mbids); 
        });

        $results = [];
        foreach ($responses as $index => $response) {
            if ($response->successful())  {
                $results[$mbids[$index]] = $response->json();
            }
        }
        return $results;
    }

    //Artitst
    public function searchArtists(string $query, int $limit = 10)
    {
        return $this->request('artist/', [
            'query' => $query,
            'limit' => $limit
        ])['artists'] ?? [];
    }

    public function getArtistsDetails(array $mbids)
    {
        return $this->bulkRequest($mbids, 'artist', [
            'inc' => 'url-rels+release-groups'
        ]);
    }

    //Albums
        public function searchAlbums(string $query, int $limit = 10)
    {
        return $this->request('release-group/', [
            'query' => $query,
            'limit' => $limit
        ])['release-groups'] ?? [];
    }

    public function getAlbumsDetails(array $mbids)
    {
        return $this->bulkRequest($mbids, 'release-group', [
            'inc' => 'url-rels+releases'
        ]);
    }

    //Tracks
        public function searchTracks(string $query, int $limit = 10)
    {
        return $this->request('recording/', [
            'query' => $query,
            'limit' => $limit
        ])['recordings'] ?? [];
    }

    public function getTrackDetails(array $mbids)
    {
        return $this->bulkRequest($mbids, 'recording', [
            'inc' => 'artist-credits+url-rels+releases'
        ]);
    }
}