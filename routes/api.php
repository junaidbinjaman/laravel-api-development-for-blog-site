<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/registerUser', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
