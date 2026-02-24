<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <a href="{{ route('posts.index') }}" class="text-indigo-600 hover:underline">{{ __('Feed') }}</a>
            <span class="text-gray-400 mx-2">/</span>
            {{ __('Post') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- البوست نفسه --}}
            <article class="p-4 sm:p-6 bg-white shadow sm:rounded-lg">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-center gap-3 shrink-0">
                        @if ($post->user->avatarUrl())
                            <a href="{{ route('users.show', $post->user) }}" class="block h-10 w-10 overflow-hidden rounded-full">
                                <img src="{{ $post->user->avatarUrl() }}" alt="" class="h-full w-full object-cover">
                            </a>
                        @else
                            <a href="{{ route('users.show', $post->user) }}" class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-200 text-sm font-medium text-gray-500">
                                {{ strtoupper(substr($post->user->name, 0, 1)) }}
                            </a>
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

                <div class="mt-3 text-gray-800 whitespace-pre-line">
                    {{ $post->content }}
                </div>

                @if ($post->image_path)
                    <div class="mt-3">
                        <img src="{{ $post->imageUrl() }}" alt="" class="max-h-96 w-full rounded-lg object-cover">
                    </div>
                @endif

                {{-- الإعجابات --}}
                <div class="mt-4 border-t border-gray-100 pt-3">
                    <h3 class="font-medium text-gray-900 text-sm mb-2">
                        {{ __('Likes') }} ({{ $post->likes_count }})
                    </h3>

                    @if ($post->likes->isEmpty())
                        <p class="text-sm text-gray-500">
                            {{ __('No one has liked this post yet.') }}
                        </p>
                    @else
                        @php
                            $likeUsers = $post->likes->pluck('user')->filter()->unique('id');
                            $visibleUsers = $likeUsers->take(10);
                            $remainingCount = $likeUsers->count() - $visibleUsers->count();
                        @endphp

                        <div class="flex flex-wrap gap-2 text-sm text-gray-700">
                            @foreach ($visibleUsers as $user)
                                <a href="{{ route('users.show', $user) }}"
                                   class="inline-flex items-center gap-2 px-2 py-1 rounded-full bg-gray-100 hover:bg-gray-200">
                                    @if ($user->avatarUrl())
                                        <span class="inline-block h-6 w-6 rounded-full overflow-hidden">
                                            <img src="{{ $user->avatarUrl() }}" alt="" class="h-full w-full object-cover">
                                        </span>
                                    @else
                                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-gray-200 text-[10px] font-semibold text-gray-600">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </span>
                                    @endif
                                    <span class="font-medium text-gray-800">{{ $user->name }}</span>
                                </a>
                            @endforeach

                            @if ($remainingCount > 0)
                                <span class="text-sm text-gray-500">
                                    {{ __('and :count more', ['count' => $remainingCount]) }}
                                </span>
                            @endif
                        </div>
                    @endif
                </div>
            </article>

            {{-- التعليقات --}}
            <div class="p-4 sm:p-6 bg-white shadow sm:rounded-lg" id="comments">
                <h3 class="font-medium text-gray-900 mb-3">
                    {{ __('Comments') }} ({{ $comments->total() }})
                </h3>

                @forelse ($comments as $comment)
                    <div class="py-3 border-b border-gray-100 last:border-0" x-data="{ showReply: false }">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex gap-2 min-w-0">
                                <a href="{{ route('users.show', $comment->user) }}" class="font-medium text-gray-900 hover:underline shrink-0">
                                    {{ $comment->user->name }}
                                </a>
                                <div class="min-w-0">
                                    <p class="text-xs text-gray-500">{{ $comment->created_at->diffForHumans() }}</p>
                                    <p class="text-gray-700 mt-0.5">{{ $comment->body }}</p>

                                    {{-- أزرار لايك ورد على التعليق --}}
                                    <div class="mt-1 flex items-center gap-3 text-xs text-gray-500"
                                         data-comment-actions
                                         data-comment-id="{{ $comment->id }}"
                                         data-liked="{{ ($comment->is_liked_by_me ?? false) ? '1' : '0' }}"
                                         data-likes-count="{{ $comment->likes_count ?? $comment->likes()->count() }}"
                                         data-like-text="{{ __('Like') }}"
                                         data-liked-text="{{ __('Liked') }}"
                                         data-unlike-text="{{ __('Unlike') }}">
                                        <form action="{{ route('comments.like', $comment) }}" method="post" class="inline" data-comment-like-form>
                                            @csrf
                                            <button type="submit"
                                                    class="js-comment-like-btn inline-flex items-center gap-1 {{ $comment->is_liked_by_me ?? false ? 'text-indigo-600' : 'text-gray-500 hover:text-indigo-600' }}">
                                                <span class="js-comment-like-text">{{ $comment->is_liked_by_me ?? false ? __('Liked') : __('Like') }}</span>
                                                <span class="text-[11px]">
                                                    (<span class="js-comment-likes-count">{{ $comment->likes_count ?? $comment->likes()->count() }}</span>)
                                                </span>
                                            </button>
                                        </form>

                                        <form action="{{ route('comments.unlike', $comment) }}" method="post" class="inline" data-comment-unlike-form>
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="js-comment-unlike-btn text-gray-500 hover:text-red-600 {{ ($comment->is_liked_by_me ?? false) ? '' : 'hidden' }}">
                                                {{ __('Unlike') }}
                                            </button>
                                        </form>

                                        <button type="button"
                                                class="text-gray-500 hover:text-indigo-600"
                                                @click="showReply = !showReply">
                                            {{ __('Reply') }}
                                        </button>
                                    </div>

                                    {{-- ردود على التعليق --}}
                                    @if ($comment->replies->isNotEmpty())
                                        <div class="mt-2 space-y-2">
                                            @foreach ($comment->replies as $reply)
                                                <div class="flex items-start gap-2">
                                                    <span class="text-xs text-gray-400">↳</span>
                                                    <div class="min-w-0 flex-1">
                                                        <a href="{{ route('users.show', $reply->user) }}" class="font-medium text-gray-900 hover:underline text-sm">
                                                            {{ $reply->user->name }}
                                                        </a>
                                                        <p class="text-[11px] text-gray-500">{{ $reply->created_at->diffForHumans() }}</p>
                                                        <p class="text-gray-700 text-sm mt-0.5">{{ $reply->body }}</p>

                                                        <div class="mt-1 flex items-center gap-3 text-[11px] text-gray-500"
                                                             data-comment-actions
                                                             data-comment-id="{{ $reply->id }}"
                                                             data-liked="{{ ($reply->is_liked_by_me ?? false) ? '1' : '0' }}"
                                                             data-likes-count="{{ $reply->likes_count ?? $reply->likes()->count() }}"
                                                             data-like-text="{{ __('Like') }}"
                                                             data-liked-text="{{ __('Liked') }}"
                                                             data-unlike-text="{{ __('Unlike') }}">
                                                            <form action="{{ route('comments.like', $reply) }}" method="post" class="inline" data-comment-like-form>
                                                                @csrf
                                                                <button type="submit"
                                                                        class="js-comment-like-btn inline-flex items-center gap-1 {{ $reply->is_liked_by_me ?? false ? 'text-indigo-600' : 'text-gray-500 hover:text-indigo-600' }}">
                                                                    <span class="js-comment-like-text">{{ $reply->is_liked_by_me ?? false ? __('Liked') : __('Like') }}</span>
                                                                    <span>
                                                                        (<span class="js-comment-likes-count">{{ $reply->likes_count ?? $reply->likes()->count() }}</span>)
                                                                    </span>
                                                                </button>
                                                            </form>

                                                            <form action="{{ route('comments.unlike', $reply) }}" method="post" class="inline" data-comment-unlike-form>
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit"
                                                                        class="js-comment-unlike-btn text-gray-500 hover:text-red-600 {{ ($reply->is_liked_by_me ?? false) ? '' : 'hidden' }}">
                                                                    {{ __('Unlike') }}
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    {{-- فورم الرد على التعليق --}}
                                    <div class="mt-2" x-show="showReply" x-cloak>
                                        <form action="{{ route('comments.store', $post) }}" method="post" class="space-y-2">
                                            @csrf
                                            <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                                            <textarea name="body" rows="2" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="{{ __('Write a reply...') }}" required></textarea>
                                            <x-primary-button type="submit" class="!py-1 !text-xs">
                                                {{ __('Reply') }}
                                            </x-primary-button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @can('delete', $comment)
                                <form action="{{ route('comments.destroy', $comment) }}" method="post" class="inline"
                                      onsubmit="return confirm('{{ __('Delete this comment?') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-red-600 hover:underline">
                                        {{ __('Delete') }}
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">{{ __('No comments yet.') }}</p>
                @endforelse

                {{-- فورم إضافة تعليق جديد --}}
                <form action="{{ route('comments.store', $post) }}" method="post" class="mt-4 space-y-2">
                    @csrf
                    <textarea name="body" rows="2" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="{{ __('Write a comment...') }}" required></textarea>
                    <x-primary-button type="submit" class="!py-1 !text-sm">
                        {{ __('Comment') }}
                    </x-primary-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>