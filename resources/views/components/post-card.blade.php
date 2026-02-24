@props([
    'post',
])

<x-card hoverable {{ $attributes }}>
    <div class="flex items-start justify-between gap-4">
        <div class="flex items-center gap-3 shrink-0">
            <a href="{{ route('users.show', $post->user) }}">
                <x-avatar :user="$post->user" size="lg" />
            </a>
            <div>
                <a href="{{ route('users.show', $post->user) }}"
                   class="font-semibold text-slate-900 hover:text-indigo-600 hover:underline">
                    {{ $post->user->name }}
                </a>
                <p class="mt-0.5 text-xs text-slate-500">
                    {{ $post->created_at->diffForHumans() }}
                </p>
            </div>
        </div>

        @can('update', $post)
            <div class="flex items-center gap-2 text-xs sm:text-sm">
                <a href="{{ route('posts.edit', $post) }}"
                   class="inline-flex items-center px-3 py-1 rounded-full border border-slate-200 text-xs font-medium text-slate-700 bg-white hover:bg-slate-50 hover:border-slate-300 transition">
                    {{ __('Edit') }}
                </a>
                <form action="{{ route('posts.destroy', $post) }}" method="post" class="inline"
                      onsubmit="return confirm('{{ __('Delete this post?') }}');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="inline-flex items-center px-3 py-1 rounded-full border border-transparent text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 hover:border-red-200 transition">
                        {{ __('Delete') }}
                    </button>
                </form>
            </div>
        @endcan
    </div>

    <div class="mt-3 text-[0.95rem] leading-relaxed text-slate-800 whitespace-pre-line">
        {{ $post->content }}
    </div>

    @if ($post->image_path)
    <div class="mt-4 rounded-2xl overflow-hidden bg-slate-100">
        <a href="{{ $post->imageUrl() }}" target="_blank" rel="noopener" class="block">
            <img src="{{ $post->imageUrl() }}" alt="" class="w-full max-h-[420px] object-cover cursor-pointer">
        </a>
    </div>
@endif

    <x-post-actions :post="$post" class="mt-4" />

    @if ($post->comments->isNotEmpty())
        <div class="mt-4 pt-3 border-t border-slate-100 space-y-2" id="comments-{{ $post->id }}">
            @foreach ($post->comments as $comment)
                <x-comment :comment="$comment" />
            @endforeach

            @if ($post->comments_count > 5)
                <a href="{{ route('posts.show', $post) }}#comments"
                   class="text-xs sm:text-sm text-slate-500 hover:text-indigo-600 hover:underline">
                    {{ __('View all comments') }}
                </a>
            @endif
        </div>
    @endif

    <x-comment-form :post="$post" class="mt-4 pt-3 border-t border-slate-100" />
</x-card>

