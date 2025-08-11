<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Utils\ApiResponseUtil;
use Illuminate\Validation\ValidationException;
use App\Services\MusicBrainzService;

class ReviewController extends Controller
{   
    protected MusicBrainzService $musicBrainz;

    public function __construct(
        MusicBrainzService $musicBrainz)
    {
        $this->musicBrainz = $musicBrainz;
    }

    public function addReview(Request $request)
    {
        try {
            $validated = $request->validate([
                'track_mbid' => 'required|string|uuid',
                'rating' => 'required|numeric|min:0|max:5'
            ]);

            $user = auth()->user();
            throw_if(!$user, \Exception::class, 'Unauthenticated', 401);

            $exists = $user->trackReviews()
                ->where('track_mbid', $validated['track_mbid'])
                ->exists();

            if ($exists) {
                return ApiResponseUtil::error('Review for that track already exists',
            []);
            }

            $review = $user->trackReviews()->create([
                'track_mbid' => $validated['track_mbid'],
                'rating' => $validated['rating']
            ]);

            $trackDetails = $this->musicBrainz->getTrackDetails([$validated['track_mbid']]);
            $track = $trackDetails[$validated['track_mbid']] ?? null;

            $formattedTrack = [
                'mbid'          => $track['id'] ?? null,
                'title'         => $track['title'] ?? null,
                'length'        => isset($track['length'])
                    ? floor(($track['length'] / 1000) / 60) . ':' . str_pad(floor(($track['length'] / 1000) % 60), 2, '0', STR_PAD_LEFT)
                    : null,
                'artist'        => $track['artist-credit'][0]['artist']['name'] ?? null,
                'release'       => $track['releases'][0]['title'] ?? null,
                'disambiguation'=> $track['disambiguation'] ?? null,
            ];
            return ApiResponseUtil::success(
                "Review successfuly created",
                [
                'review' => $review,
                'track' => $formattedTrack
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
                $e->getCode() === 401 ? 'Unauthorized' : 'Failed to add review',
                ['error' => $e->getMessage()],
                $e->getCode() ?: 500
            );
        }
    }
}
