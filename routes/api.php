<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ArtistsController;
use App\Http\Controllers\SocialController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Auth Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function() {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/self', [AuthController::class, 'self']);
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
    Route::get('/users/stats', [SocialController::class, 'stats']);
});