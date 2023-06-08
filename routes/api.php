<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\TripController;
use App\Http\Controllers\TripUserController;

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
    Route::post('/trips', [TripController::class, 'store']);
    Route::post('/trips/{trip}', [TripController::class, 'join']);
    Route::put('/trips/{trip}', [TripController::class, 'update']);
    Route::delete('/trips/{trip}', [TripController::class, 'destroy']);
});
Route::get('/trips/{trip}', [TripController::class, 'show']);
Route::apiResource('/trips', TripController::class)->only(['index']);

// USER TRIPS ROUTES
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/trips/users/{user}', [TripUserController::class, 'findTripsFromUser']);
    Route::get('/users/trips/{trip}', [TripUserController::class, 'findUsersFromTrip']);
});
