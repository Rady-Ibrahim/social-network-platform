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
            </article>

            {{-- التعليقات --}}
            <div class="p-4 sm:p-6 bg-white shadow sm:rounded-lg" id="comments">
                <h3 class="font-medium text-gray-900 mb-3">
                    {{ __('Comments') }} ({{ $comments->total() }})
                </h3>

                @forelse ($comments as $comment)
                    <div class="py-3 border-b border-gray-100 last:border-0">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex gap-2 min-w-0">
                                <a href="{{ route('users.show', $comment->user) }}" class="font-medium text-gray-900 hover:underline shrink-0">
                                    {{ $comment->user->name }}
                                </a>
                                <div class="min-w-0">
                                    <p class="text-xs text-gray-500">{{ $comment->created_at->diffForHumans() }}</p>
                                    <p class="text-gray-700 mt-0.5">{{ $comment->body }}</p>
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