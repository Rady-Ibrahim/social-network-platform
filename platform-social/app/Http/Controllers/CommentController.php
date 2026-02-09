<?php

namespace App\Http\Controllers;

use App\Events\CommentCreated;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, Post $post): RedirectResponse
    {
        $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $comment = $post->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $request->input('body'),
        ]);

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
