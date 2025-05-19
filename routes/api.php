<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// user register
Route::post('/user/register', [\App\Http\Controllers\Api\AuthController::class, 'userRegister']);

// restaurant register
Route::post('/restaurant/register', [\App\Http\Controllers\Api\AuthController::class, 'registerRestaurant']);

// driver register
Route::post('/driver/register', [\App\Http\Controllers\Api\AuthController::class, 'registerDriver']);

// login
Route::post('/login', [\App\Http\Controllers\Api\AuthController::class, 'login']);

// logout
Route::post('/logout', [\App\Http\Controllers\Api\AuthController::class, 'logout'])->middleware('auth:sanctum');

// update latlong
Route::put('/user/update/latlong', [\App\Http\Controllers\Api\AuthController::class, 'updateLatLong'])->middleware('auth:sanctum');

// get all restaurants
Route::get('/restaurants', [\App\Http\Controllers\Api\AuthController::class, 'getAllRestaurants'])->middleware('auth:sanctum');
