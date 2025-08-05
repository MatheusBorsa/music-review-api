<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ArtistsController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function() {
    Route::post('/logout', [AuthController::class, 'logout']);
});

//Artists
Route::get('/artists/search', [ArtistsController::class, 'search']);

Route::middleware('auth:sanctum')->group(function() {
    Route::post('/artists/favorites', [ArtistsController::class, 'addFavorite']);
    Route::delete('/artists/favorites', [ArtistsController::class, 'removeFavorite']);
    Route::get('/artists/favorites', [ArtistsController::class, 'listFavorites']);
});