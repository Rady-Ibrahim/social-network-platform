<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\FriendRequestController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PostLikeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CommentLikeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Events\TestBroadcast;


Route::get('/', [PostController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/dashboard', [PostController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');

Route::middleware('auth')->group(function () {
    Route::get('/users/search', [UserController::class, 'search'])->name('users.search');
    Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
    Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::get('/posts/{post}/edit', [PostController::class, 'edit'])->name('posts.edit');
    Route::put('/posts/{post}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');

    Route::post('/posts/{post}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::put('/comments/{comment}', [CommentController::class, 'update'])->name('comments.update');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

    Route::post('/comments/{comment}/like', [CommentLikeController::class, 'store'])->name('comments.like');
    Route::delete('/comments/{comment}/like', [CommentLikeController::class, 'destroy'])->name('comments.unlike');

    Route::post('/posts/{post}/like', [PostLikeController::class, 'store'])->name('posts.like');
    Route::delete('/posts/{post}/like', [PostLikeController::class, 'destroy'])->name('posts.unlike');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.markAllRead');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/friends', [FriendRequestController::class, 'index'])->name('friends.index');
    Route::post('/friend-requests', [FriendRequestController::class, 'store'])->name('friend-requests.store');
    Route::post('/friend-requests/{friend_request}/accept', [FriendRequestController::class, 'accept'])->name('friend-requests.accept');
    Route::post('/friend-requests/{friend_request}/reject', [FriendRequestController::class, 'reject'])->name('friend-requests.reject');
    Route::delete('/friend-requests/{friend_request}', [FriendRequestController::class, 'destroy'])->name('friend-requests.destroy');

    // Simple debug route to test broadcasting to the current user
    Route::get('/test-broadcast', function (Request $request) {
        event(new TestBroadcast($request->user()));

        return 'Broadcasted test notification';
    })->name('test-broadcast');
});

require __DIR__.'/auth.php';
