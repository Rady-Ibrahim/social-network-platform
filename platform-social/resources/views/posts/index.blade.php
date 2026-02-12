<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Feed') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-slate-50">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            @if (session('error'))
                <x-alert type="error">
                    {{ session('error') }}
                </x-alert>
            @endif
            @if ($errors->any())
                <div class="space-y-1">
                    @foreach ($errors->all() as $err)
                        <x-alert type="error">
                            {{ $err }}
                        </x-alert>
                    @endforeach
                </div>
            @endif
            @if (session('status'))
                <x-alert type="success" rounded="full" class="inline-flex items-center gap-2">
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
                </x-alert>
            @endif

            <x-card padding="lg">
                <form id="post-form" action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label for="post-content" class="block text-sm font-semibold text-slate-800">{{ __('What\'s on your mind?') }}</label>
                        <textarea id="post-content" name="content" rows="3" class="mt-2 block w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-3 py-2 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0 transition" placeholder="{{ __('Write a post...') }}" required>{{ old('content') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('content')" />
                    </div>
                    <div>
                        <label for="post-image" class="block text-xs font-medium tracking-wide text-slate-600 uppercase">{{ __('Image (optional)') }}</label>
                        <input id="post-image" type="file" name="image" accept="image/*" class="mt-2 block w-full text-sm text-slate-700 file:mr-4 file:rounded-full file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-xs file:font-medium file:uppercase file:tracking-wide file:text-slate-700 hover:file:bg-slate-200 transition" />
                        <x-input-error class="mt-2" :messages="$errors->get('image')" />
                    </div>
                    <button type="submit" form="post-form" class="inline-flex items-center justify-center px-5 py-2.5 bg-indigo-600 border border-transparent rounded-full font-semibold text-xs text-white uppercase tracking-wide shadow-sm hover:bg-indigo-700 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-indigo-800 transition">
                        {{ __('Post') }}
                    </button>
                </form>
            </x-card>

            <div class="space-y-8">
                @forelse ($posts as $post)
                    <x-post-card :post="$post" />
                @empty
                    <x-card padding="lg" class="bg-white/90 text-center text-slate-500">
                        {{ __('No posts yet. Share something!') }}
                    </x-card>
                @endforelse
            </div>

            @if ($posts->hasPages())
                <div class="flex justify-center pt-4">
                    {{ $posts->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
