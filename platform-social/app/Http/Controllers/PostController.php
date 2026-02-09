<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

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
            'image' => ['nullable', 'image', 'max:2048'],
        ], [
            'content.required' => __('Please write something to post.'),
            'content.max' => __('Post cannot exceed 5000 characters.'),
            'image.image' => __('The file must be an image.'),
            'image.max' => __('Image size cannot exceed 2MB.'),
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('posts', 'public');
        }

        Post::create([
            'user_id' => $request->user()->id,
            'content' => $validated['content'],
            'image_path' => $imagePath,
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

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:5000'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        $data = [
            'content' => $validated['content'],
        ];

        if ($request->hasFile('image')) {
            if ($post->image_path) {
                Storage::disk('public')->delete($post->image_path);
            }

            $data['image_path'] = $request->file('image')->store('posts', 'public');
        }

        $post->update($data);

        return redirect()->route('posts.index')->with('status', 'post-updated');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $this->authorize('delete', $post);

        $post->delete();

        return redirect()->route('posts.index')->with('status', 'post-deleted');
    }
}
