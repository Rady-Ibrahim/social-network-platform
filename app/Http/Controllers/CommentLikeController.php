<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentLikeController extends Controller
{
    public function store(Request $request, Comment $comment): RedirectResponse
    {
        $comment->likes()->firstOrCreate([
            'user_id' => $request->user()->id,
        ]);

        return back();
    }

    public function destroy(Request $request, Comment $comment): RedirectResponse
    {
        $comment->likes()->where('user_id', $request->user()->id)->delete();

        return back();
    }
}

