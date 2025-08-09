<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\UserFavoriteService;
use App\Utils\ApiResponseUtil;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class SocialController extends Controller
{
    public function __construct(
        protected UserFavoriteService $userFavoriteService
    ) {
        $this->userFavoriteService = $userFavoriteService;
    }

    public function follow(User $user)
    {
        try {
            $currentUser = auth()->user();
            throw_if(!$currentUser, \Exception::class, 'Unauthenticated', 401);

            if ($currentUser-> id === $user->id) {
                return ApiResponseUtil::error(
                    'You cannot follow yourself',
                    null,
                    400
                );
            }

            $currentUser->following()->syncWithoutDetaching([$user->id]);

            return ApiResponseUtil::success(
                "Now following {$user->username}",
                [
                    'following_count' => $currentUser->following()->count(),
                    'is_following' => true
                ]
            );
            
        } catch (\Exception $e) {
            $statusCode = is_int($e->getCode()) && $e->getCode() >= 100 ? $e->getCode() : 500;
            return ApiResponseUtil::error(
                $e->getMessage(),
                null,
                $statusCode
            );
        }
    }

        public function unfollow(User $user)
    {
        try {
            $currentUser = auth()->user();
            throw_if(!$currentUser, \Exception::class, 'Unauthenticated', 401);

            if ($currentUser-> id === $user->id) {
                return ApiResponseUtil::error(
                    'You cannot unfollow yourself',
                    null,
                    400
                );
            }

            $currentUser->following()->detach([$user->id]);

            return ApiResponseUtil::success(
                "Unfollowed {$user->username}",
                [
                    'following_count' => $currentUser->following()->count(),
                    'is_following' => false
                ]
            );

        } catch (\Exception $e) {
            $statusCode = is_int($e->getCode()) && $e->getCode() >= 100 ? $e->getCode() : 500;
            return ApiResponseUtil::error(
                $e->getMessage(),
                null,
                $statusCode
            );
        }
    }

    public function stats(User $user)
    {
        try {
            $user = auth()->user();
            throw_if(!$user, \Exception::class, 'Unauthenticated', 401);

            $data = [
                'username' => $user->username,
                'followers' => $user->followers()->count(),
                'following' => $user->following()->count(),
                'favorite_artists' => $this->userFavoriteService->getFavorites($user)
            ];

            return ApiResponseUtil::success('Social stats retrieved', $data);

        } catch (\Exception $e) {
            $statusCode = is_int($e->getCode()) && $e->getCode() >= 100 ? $e->getCode() : 500;
            return ApiResponseUtil::error(
                $e->getMessage(),
                null,
                $statusCode
            );
        }
    }
}
