<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\FriendRequestController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\PostLikeController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/users/{user}', [UserController::class, 'show']);
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::patch('/profile', [ProfileController::class, 'update']);

    Route::apiResource('posts', PostController::class);
    Route::get('/posts/{post}/comments', [CommentController::class, 'index']);
    Route::post('/posts/{post}/comments', [CommentController::class, 'store']);
    Route::put('/comments/{comment}', [CommentController::class, 'update']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);

    Route::post('/posts/{post}/like', [PostLikeController::class, 'store']);
    Route::delete('/posts/{post}/like', [PostLikeController::class, 'destroy']);
    Route::get('/posts/{post}/likes', [PostLikeController::class, 'index']);

    Route::get('/friends', [FriendRequestController::class, 'friends']);
    Route::get('/friend-requests', [FriendRequestController::class, 'index']);
    Route::post('/friend-requests', [FriendRequestController::class, 'store']);
    Route::post('/friend-requests/{friend_request}/accept', [FriendRequestController::class, 'accept']);
    Route::post('/friend-requests/{friend_request}/reject', [FriendRequestController::class, 'reject']);
    Route::delete('/friend-requests/{friend_request}', [FriendRequestController::class, 'destroy']);
});
