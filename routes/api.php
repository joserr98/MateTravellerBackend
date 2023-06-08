<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\TripController;

// USERS ROUTES
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/users/profile', [UserController::class, 'show']);
    Route::patch('/users/{user}', [UserController::class, 'update']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);
});
Route::post('/login', [UserController::class, 'login']);
Route::apiResource('/users', UserController::class)->only(['index','store']);

// TRIPS ROUTES
Route::middleware(['auth:sanctum'])->group(function () {
    Route::put('/trips', [TripController::class, 'store']);
    Route::put('/trips/{trip}', [TripController::class, 'update']);
    Route::delete('/trips/{trip}', [TripController::class, 'destroy']);
});
Route::get('/trips/{trip}', [TripController::class, 'show']);
Route::apiResource('/trips', TripController::class)->only(['index']);