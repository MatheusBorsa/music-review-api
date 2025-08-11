<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ArtistsController;
use App\Http\Controllers\SocialController;
use App\Http\Controllers\TrackController;
use App\Http\Controllers\ReviewController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Auth Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function() {
    Route::post('/logout', [AuthController::class, 'logout']);
});

// Artists Routes
Route::get('/artists/search', [ArtistsController::class, 'search']);

Route::middleware('auth:sanctum')->group(function() {
    Route::post('/artists/favorites', [ArtistsController::class, 'addFavorite']);
    Route::delete('/artists/favorites', [ArtistsController::class, 'removeFavorite']);
    Route::get('/artists/favorites', [ArtistsController::class, 'listFavorites']);
});

// Social Media Routes
Route::middleware('auth:sanctum')->group(function() {
    Route::post('/users/{user}/follow', [SocialController::class, 'follow']);
    Route::post('/users/{user}/unfollow', [SocialController::class, 'unfollow']);
    Route::get('/users/stats/{user}', [SocialController::class, 'stats']);
});

//Reviews
Route::middleware('auth:sanctum')->group(function() {
    Route::post('/users/reviews', [ReviewController::class, 'addReview']);
});
//Tracks
Route::get('/tracks/search', [TrackController::class, 'search']);
Route::get('/tracks/{track}', [TrackController::class, 'show']);

//Feed