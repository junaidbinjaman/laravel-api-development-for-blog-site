<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;

Route::post('/registerUser', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user/{id}', [AuthController::class, 'getUserData']);
    Route::put('/user/{id}', [AuthController::class, 'updateUserData']);

    Route::apiResource('/category', CategoryController::class);
    Route::apiResource('/post', PostController::class);
    Route::apiResource('/comment', CommentController::class)->only(['store', 'update', 'destroy']);
    Route::put('/comment/{comment}/approve', [CommentController::class, 'approveComment']);
    Route::put('/comment/{comment}/archive', [CommentController::class, 'rejectComment']);
});

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/category/{category}', [CategoryController::class, 'show']);

Route::get('/post', [PostController::class, 'index']);
Route::get('/posts/{slug}', [PostController::class, 'show']);
Route::apiResource('/comment', CommentController::class)->only(['index', 'show']);
Route::get('/comment/post/{postId}', [CommentController::class, 'getCommentsByPostId']);
