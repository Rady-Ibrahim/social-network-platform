@props([
    'post',
])

<form
    action="{{ route('comments.store', $post) }}"
    method="post"
    {{ $attributes->merge(['class' => '']) }}
>
    @csrf
    <div class="flex gap-2">
        <input
            type="text"
            name="body"
            placeholder="{{ __('Write a comment...') }}"
            class="block w-full rounded-full border border-slate-200 bg-slate-50/80 px-3 py-2 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0 transition"
            required
        />
        <x-primary-button
            type="submit"
            class="!py-1.5 !px-4 !text-xs sm:!text-sm shrink-0 rounded-full"
        >
            {{ __('Comment') }}
        </x-primary-button>
    </div>
</form>

