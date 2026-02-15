<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Post;
use App\Models\UserNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index(Post $post): JsonResponse
    {
        $comments = $post->comments()->with('user')->latest()->paginate(15);

        return response()->json(CommentResource::collection($comments));
    }

    public function store(StoreCommentRequest $request, Post $post): JsonResponse
    {
        $parentId = $request->input('parent_id');

        if ($parentId) {
            Comment::where('post_id', $post->id)->where('id', $parentId)->firstOrFail();
        }

        $comment = $post->comments()->create([
            'user_id' => $request->user()->id,
            'parent_id' => $parentId,
            'body' => $request->input('body'),
        ]);
        $comment->load('user', 'post.user');

        // Store notification for post owner (if not the same as commenter)
        $postOwner = $comment->post?->user;
        if ($postOwner && $postOwner->id !== $request->user()->id) {
            $postOwner->notifications()->create([
                'type' => 'comment',
                'message' => $request->user()->name . ' commented on your post.',
                'data' => [
                    'post_id' => $post->id,
                    'comment_id' => $comment->id,
                ],
            ]);
        }

        return response()->json(new CommentResource($comment), 201);
    }

    public function update(Request $request, Comment $comment): JsonResponse
    {
        $this->authorize('update', $comment);
        $request->validate(['body' => ['required', 'string', 'max:2000']]);
        $comment->update(['body' => $request->input('body')]);
        $comment->load('user');

        return response()->json(new CommentResource($comment));
    }

    public function destroy(Comment $comment): JsonResponse
    {
        $this->authorize('delete', $comment);
        $comment->delete();

        return response()->json(null, 204);
    }
}
