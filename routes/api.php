<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\CategoryController;

Route::post('/registerUser', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user/{id}', [AuthController::class, 'getUserData']);
    Route::put('/user/{id}', [AuthController::class, 'updateUserData']);

    Route::resource('/category', CategoryController::class);
});

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/category/{category}', [CategoryController::class, 'show']);
