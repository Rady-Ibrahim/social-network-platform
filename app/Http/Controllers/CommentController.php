<?php

namespace App\Http\Controllers;

use App\Events\CommentCreated;
use App\Models\Comment;
use App\Models\Post;
use App\Models\UserNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, Post $post): RedirectResponse
    {
        $request->validate([
            'body' => ['required', 'string', 'max:2000'],
            'parent_id' => ['nullable', 'integer', 'exists:comments,id'],
        ]);

        $parentId = $request->input('parent_id');

        if ($parentId) {
            // تأكد أن التعليق الأب يخص نفس البوست
            $parent = Comment::where('post_id', $post->id)->where('id', $parentId)->firstOrFail();
        }

        $comment = $post->comments()->create([
            'user_id' => $request->user()->id,
            'parent_id' => $parentId,
            'body' => $request->input('body'),
        ]);

        // Store notification for post owner (if not the same as commenter)
        $post->loadMissing('user');
        $postOwner = $post->user;
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

        event(new CommentCreated($comment));

        return back()->with('status', 'comment-added');
    }

    public function update(Request $request, Comment $comment): RedirectResponse
    {
        $this->authorize('update', $comment);

        $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $comment->update(['body' => $request->input('body')]);

        return back()->with('status', 'comment-updated');
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return back()->with('status', 'comment-deleted');
    }
}
