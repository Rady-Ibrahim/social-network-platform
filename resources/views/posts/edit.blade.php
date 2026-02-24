<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit post') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="p-4 sm:p-6 bg-white shadow sm:rounded-lg">
                <form action="{{ route('posts.update', $post) }}" method="post" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label for="content" class="block text-sm font-medium text-gray-700">{{ __('Content') }}</label>
                        <textarea id="content" name="content" rows="5" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>{{ old('content', $post->content) }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('content')" />
                    </div>
                    @if ($post->image_path)
                        <div>
                            <span class="block text-sm font-medium text-gray-700 mb-1">{{ __('Current image') }}</span>
                            <img src="{{ $post->imageUrl() }}" alt="" class="max-h-64 w-full rounded-lg object-cover">
                        </div>
                    @endif
                    <div>
                        <label for="image" class="block text-sm font-medium text-gray-700">{{ __('Replace image (optional)') }}</label>
                        <input id="image" type="file" name="image" accept="image/*" class="mt-1 block w-full text-sm text-gray-700">
                        <x-input-error class="mt-2" :messages="$errors->get('image')" />
                    </div>
                    <div class="flex gap-3">
                        <x-primary-button type="submit">{{ __('Update') }}</x-primary-button>
                        <a href="{{ route('posts.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">{{ __('Cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
