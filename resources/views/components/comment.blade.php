@props([
    'comment',
])

<div {{ $attributes->merge(['class' => 'flex gap-2 text-sm']) }}>
    <a href="{{ route('users.show', $comment->user) }}"
       class="font-medium text-slate-900 hover:text-indigo-600 hover:underline shrink-0">
        {{ $comment->user->name }}
    </a>
    <span class="text-slate-700">
        {{ $comment->body }}
    </span>
</div>

