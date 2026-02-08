<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PostLikeController extends Controller
{
    public function store(Request $request, Post $post): RedirectResponse
    {
        $post->likes()->firstOrCreate([
            'user_id' => $request->user()->id,
        ]);

        return back();
    }

    public function destroy(Request $request, Post $post): RedirectResponse
    {
        $post->likes()->where('user_id', $request->user()->id)->delete();

        return back();
    }
}
