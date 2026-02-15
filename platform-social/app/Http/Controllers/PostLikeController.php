<?php

namespace App\Http\Controllers;

use App\Events\PostLiked;
use App\Models\Post;
use App\Models\UserNotification;
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
