<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Feed') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('error'))
                <div class="p-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
                    {{ session('error') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="p-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
                    @foreach ($errors->all() as $err)
                        <p>{{ $err }}</p>
                    @endforeach
                </div>
            @endif
            @if (session('status'))
                <p class="text-sm text-green-600">
                    @if (session('status') === 'post-created')
                        {{ __('Post created.') }}
                    @elseif (session('status') === 'post-updated')
                        {{ __('Post updated.') }}
                    @elseif (session('status') === 'post-deleted')
                        {{ __('Post deleted.') }}
                    @elseif (session('status') === 'comment-added')
                        {{ __('Comment added.') }}
                    @elseif (session('status') === 'comment-updated')
                        {{ __('Comment updated.') }}
                    @elseif (session('status') === 'comment-deleted')
                        {{ __('Comment deleted.') }}
                    @endif
                </p>
            @endif

            <div class="p-4 sm:p-6 bg-white shadow sm:rounded-lg">
                <form id="post-form" action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label for="post-content" class="block text-sm font-medium text-gray-700">{{ __('What\'s on your mind?') }}</label>
                        <textarea id="post-content" name="content" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="{{ __('Write a post...') }}" required>{{ old('content') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('content')" />
                    </div>
                    <div>
                        <label for="post-image" class="block text-sm font-medium text-gray-700">{{ __('Image (optional)') }}</label>
                        <input id="post-image" type="file" name="image" accept="image/*" class="mt-1 block w-full text-sm text-gray-700" />
                        <x-input-error class="mt-2" :messages="$errors->get('image')" />
                    </div>
                    <button type="submit" form="post-form" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                        {{ __('Post') }}
                    </button>
                </form>
            </div>

            <div class="space-y-4">
                @forelse ($posts as $post)
                    <article class="p-4 sm:p-6 bg-white shadow sm:rounded-lg">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-center gap-3 shrink-0">
                                @if ($post->user->avatarUrl())
                                    <a href="{{ route('users.show', $post->user) }}"><img src="{{ $post->user->avatarUrl() }}" alt="" class="h-10 w-10 rounded-full object-cover" /></a>
                                @else
                                    <a href="{{ route('users.show', $post->user) }}" class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center text-sm font-medium text-gray-500">{{ strtoupper(substr($post->user->name, 0, 1)) }}</a>
                                @endif
                                <div>
                                    <a href="{{ route('users.show', $post->user) }}" class="font-medium text-gray-900 hover:underline">{{ $post->user->name }}</a>
                                    <p class="text-xs text-gray-500">{{ $post->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                            @can('update', $post)
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('posts.edit', $post) }}" class="text-sm text-indigo-600 hover:underline">{{ __('Edit') }}</a>
                                    <form action="{{ route('posts.destroy', $post) }}" method="post" class="inline" onsubmit="return confirm('{{ __('Delete this post?') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm text-red-600 hover:underline">{{ __('Delete') }}</button>
                                    </form>
                                </div>
                            @endcan
                        </div>
                        <div class="mt-3 text-gray-800 whitespace-pre-line">{{ $post->content }}</div>

                        @if ($post->image_path)
                            <div class="mt-3">
                                <img src="{{ $post->imageUrl() }}" alt="" class="max-h-80 w-full rounded-lg object-cover">
                            </div>
                        @endif

                        <div class="mt-3 flex items-center gap-4 flex-wrap">
                            {{-- زر لايك --}}
                            <form action="{{ route('posts.like', $post) }}" method="post" class="inline">
                                @csrf
                                <button type="submit"
                                        class="text-sm font-medium {{ $post->is_liked_by_me ? 'text-gray-400 cursor-not-allowed' : 'text-gray-600 hover:underline' }}"
                                        {{ $post->is_liked_by_me ? 'disabled' : '' }}>
                                    {{ __('Like') }} ({{ $post->likes_count }})
                                </button>
                            </form>

                            {{-- زر أن لايك --}}
                            <form action="{{ route('posts.unlike', $post) }}" method="post" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="text-sm font-medium {{ $post->is_liked_by_me ? 'text-indigo-600 hover:underline' : 'text-gray-400 cursor-not-allowed' }}"
                                        {{ $post->is_liked_by_me ? '' : 'disabled' }}>
                                    {{ __('Unlike') }}
                                </button>
                            </form>

                            <a href="{{ route('posts.show', $post) }}#comments" class="text-sm text-gray-500 hover:underline">
                                {{ $post->comments_count }} {{ __('comments') }}
                            </a>
                        </div>

                        @if ($post->comments->isNotEmpty())
                            <div class="mt-3 pt-3 border-t border-gray-100 space-y-2" id="comments-{{ $post->id }}">
                                @foreach ($post->comments as $comment)
                                    <div class="flex gap-2 text-sm">
                                        <a href="{{ route('users.show', $comment->user) }}" class="font-medium text-gray-900 hover:underline shrink-0">{{ $comment->user->name }}</a>
                                        <span class="text-gray-700">{{ $comment->body }}</span>
                                    </div>
                                @endforeach
                                @if ($post->comments_count > 5)
                                    <a href="{{ route('posts.show', $post) }}#comments" class="text-sm text-gray-500 hover:underline">{{ __('View all comments') }}</a>
                                @endif
                            </div>
                        @endif

                        <form action="{{ route('comments.store', $post) }}" method="post" class="mt-3 pt-3 border-t border-gray-100">
                            @csrf
                            <div class="flex gap-2">
                                <input type="text" name="body" placeholder="{{ __('Write a comment...') }}" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" required />
                                <x-primary-button type="submit" class="!py-1 !text-sm shrink-0">{{ __('Comment') }}</x-primary-button>
                            </div>
                        </form>
                    </article>
                @empty
                    <div class="p-6 bg-white shadow sm:rounded-lg text-center text-gray-500">
                        {{ __('No posts yet. Share something!') }}
                    </div>
                @endforelse
            </div>

            @if ($posts->hasPages())
                <div class="flex justify-center">
                    {{ $posts->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
