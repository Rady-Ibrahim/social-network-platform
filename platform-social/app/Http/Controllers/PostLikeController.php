<?php

namespace App\Http\Controllers;

use App\Events\PostLiked;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PostLikeController extends Controller
{
    public function store(Request $request, Post $post): RedirectResponse
    {
        $like = $post->likes()->firstOrCreate([
            'user_id' => $request->user()->id,
        ]);

        if ($like->wasRecentlyCreated) {
            event(new PostLiked(
                post: $post,
                likerId: $request->user()->id,
                likerName: $request->user()->name,
            ));
        }

        return back();
    }

    public function destroy(Request $request, Post $post): RedirectResponse
    {
        $post->likes()->where('user_id', $request->user()->id)->delete();

        return back();
    }
}
