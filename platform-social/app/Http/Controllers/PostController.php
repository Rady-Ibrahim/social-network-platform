<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PostController extends Controller
{
    /**
     * Feed: posts from current user and their friends.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $friendIds = $user->friendIds();
        $feedUserIds = array_merge([$user->id], $friendIds);

        $posts = Post::whereIn('user_id', $feedUserIds)
            ->with([
                'user',
                'comments' => fn ($q) => $q->latest()->limit(5)->with('user'),
            ])
            ->withCount(['comments', 'likes'])
            ->withExists(['likes as is_liked_by_me' => fn ($q) => $q->where('user_id', $user->id)])
            ->latest()
            ->paginate(15);

        return view('posts.index', [
            'posts' => $posts,
        ]);
    }

    public function show(Request $request, Post $post): View
    {
        $post->load(['user', 'likes.user']);
        $post->loadCount(['comments', 'likes']);
        if ($request->user()) {
            $post->setAttribute('is_liked_by_me', $post->likes()->where('user_id', $request->user()->id)->exists());
        }
        $comments = $post->comments()->with('user')->latest()->paginate(15);

        return view('posts.show', [
            'post' => $post,
            'comments' => $comments,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'content' => ['required', 'string', 'max:5000'],
        ], [
            'content.required' => __('Please write something to post.'),
            'content.max' => __('Post cannot exceed 5000 characters.'),
        ]);

        Post::create([
            'user_id' => $request->user()->id,
            'content' => $validated['content'],
        ]);

        return redirect()->route('posts.index')->with('status', 'post-created');
    }

    public function edit(Post $post): View|RedirectResponse
    {
        $this->authorize('update', $post);

        return view('posts.edit', [
            'post' => $post,
        ]);
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        $this->authorize('update', $post);

        $request->validate([
            'content' => ['required', 'string', 'max:5000'],
        ]);

        $post->update(['content' => $request->input('content')]);

        return redirect()->route('posts.index')->with('status', 'post-updated');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $this->authorize('delete', $post);

        $post->delete();

        return redirect()->route('posts.index')->with('status', 'post-deleted');
    }
}
