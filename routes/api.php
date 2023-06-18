<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\TripController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\TripUserController;

// USERS ROUTES
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/users/profile', [UserController::class, 'show']);
    Route::patch('/users/{user}', [UserController::class, 'update']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);
});
Route::get('/users', [UserController::class, 'index']);
Route::get('/users/pages', [UserController::class, 'userPagination']);
Route::get('/users/filter', [UserController::class, 'userByFilter']);
Route::post('/login', [UserController::class, 'login']);
Route::post('/users', [UserController::class, 'store']);

// TRIPS ROUTES
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/trips', [TripController::class, 'store']);
    Route::put('/trips/{trip}', [TripController::class, 'update']);
    Route::delete('/trips/{trip}', [TripController::class, 'destroy']);
});
Route::get('/trips/{trip}', [TripController::class, 'show']);
Route::get('/trips/pages', [TripController::class, 'tripPagination']);
Route::get('/trips', [TripController::class, 'index']);

// USER TRIPS ROUTES
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/trips/{trip}', [TripUserController::class, 'join']);
    Route::get('/trips/users/{user}', [TripUserController::class, 'findTripsFromUser']);
    Route::get('/users/organizer/trips/{trip}', [TripUserController::class, 'findOrganizerFromTrip']);
    Route::get('/users/travelers/trips/{trip}', [TripUserController::class, 'findTravelersFromTrip']);
});
    
// MESSAGE ROUTES
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/messages/{user}', [MessageController::class, 'index']);
    Route::post('/messages', [MessageController::class, 'store']);
});