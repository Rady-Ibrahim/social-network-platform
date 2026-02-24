<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Post;
use App\Models\UserNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostLikeController extends Controller
{
    public function store(Request $request, Post $post): JsonResponse
    {
        $like = $post->likes()->firstOrCreate(['user_id' => $request->user()->id]);

        if ($like->wasRecentlyCreated) {
            // Store notification for post owner (if not the same as liker)
            $post->loadMissing('user');
            $postOwner = $post->user;
            if ($postOwner && $postOwner->id !== $request->user()->id) {
                $postOwner->notifications()->create([
                    'type' => 'like',
                    'message' => $request->user()->name . ' liked your post.',
                    'data' => [
                        'post_id' => $post->id,
                        'liker_id' => $request->user()->id,
                    ],
                ]);
            }
        }

        return response()->json(['message' => 'Liked'], 201);
    }

    public function destroy(Request $request, Post $post): JsonResponse
    {
        $post->likes()->where('user_id', $request->user()->id)->delete();

        return response()->json(null, 204);
    }

    public function index(Post $post): JsonResponse
    {
        $users = $post->likes()->with('user')->get()->pluck('user');

        return response()->json(UserResource::collection($users));
    }
}
