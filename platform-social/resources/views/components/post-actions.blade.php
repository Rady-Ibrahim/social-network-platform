@props([
    'post',
])

<div {{ $attributes->merge(['class' => 'flex items-center gap-3 sm:gap-4 flex-wrap text-sm']) }}>
    <form action="{{ route('posts.like', $post) }}" method="post" class="inline">
        @csrf
        <button
            type="submit"
            class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold border {{ $post->is_liked_by_me ? 'border-slate-200 bg-slate-50 text-slate-400 cursor-not-allowed' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50 hover:border-slate-300' }} transition"
            {{ $post->is_liked_by_me ? 'disabled' : '' }}
        >
            {{ __('Like') }} ({{ $post->likes_count }})
        </button>
    </form>

    <form action="{{ route('posts.unlike', $post) }}" method="post" class="inline">
        @csrf
        @method('DELETE')
        <button
            type="submit"
            class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold border {{ $post->is_liked_by_me ? 'border-indigo-200 bg-indigo-50 text-indigo-700 hover:bg-indigo-100' : 'border-slate-200 bg-slate-50 text-slate-400 cursor-not-allowed' }} transition"
            {{ $post->is_liked_by_me ? '' : 'disabled' }}
        >
            {{ __('Unlike') }}
        </button>
    </form>

    <a href="{{ route('posts.show', $post) }}#comments"
       class="text-xs sm:text-sm text-slate-500 hover:text-indigo-600 hover:underline">
        {{ $post->comments_count }} {{ __('comments') }}
    </a>
</div>

