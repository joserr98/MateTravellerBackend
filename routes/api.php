<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\TripController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// USERS MANAGEMENT
Route::post('/login', [UserController::class, 'login']);
Route::get('/profile', [UserController::class, 'profile'])->middleware('auth:sanctum');
Route::patch('/users/{user}', [UserController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/users/{user}', [UserController::class, 'destroy'])->middleware('auth:sanctum');
Route::apiResource('/users', UserController::class)->only(['index','show','store']);

// TRIPS MANAGEMENT
Route::get('/trips/{trip}', [TripController::class, 'show']);
Route::put('/trips/{trip}', [TripController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/trips/{trip}', [TripController::class, 'destroy'])->middleware('auth:sanctum');
Route::apiResource('/trips', TripController::class)->only(['index','store']);