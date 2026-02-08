<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StorePostRequest;
use App\Http\Requests\Api\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $friendIds = $user->friendIds();
        $feedUserIds = array_merge([$user->id], $friendIds);

        $posts = Post::whereIn('user_id', $feedUserIds)
            ->with('user')
            ->withCount(['comments', 'likes'])
            ->withExists(['likes as is_liked_by_me' => fn ($q) => $q->where('user_id', $user->id)])
            ->latest()
            ->paginate(15);

        return response()->json(PostResource::collection($posts));
    }

    public function store(StorePostRequest $request): JsonResponse
    {
        $post = $request->user()->posts()->create($request->validated());
        $post->load('user');

        return response()->json(new PostResource($post), 201);
    }

    public function show(Request $request, Post $post): JsonResponse
    {
        $post->load('user');
        $post->loadCount(['comments', 'likes']);
        $post->setAttribute('is_liked_by_me', $post->likes()->where('user_id', $request->user()->id)->exists());

        return response()->json(new PostResource($post));
    }

    public function update(UpdatePostRequest $request, Post $post): JsonResponse
    {
        $post->update($request->validated());
        $post->load('user');
        $post->loadCount(['comments', 'likes']);
        $post->setAttribute('is_liked_by_me', $post->likes()->where('user_id', $request->user()->id)->exists());

        return response()->json(new PostResource($post));
    }

    public function destroy(Request $request, Post $post): JsonResponse
    {
        $this->authorize('delete', $post);
        $post->delete();

        return response()->json(null, 204);
    }
}
