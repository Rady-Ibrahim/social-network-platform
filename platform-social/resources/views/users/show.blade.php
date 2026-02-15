<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $user->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="flex flex-col sm:flex-row items-start gap-6">
                    <div class="shrink-0">
                        @if ($user->avatarUrl())
                            <img src="{{ $user->avatarUrl() }}" alt="" class="h-24 w-24 rounded-full object-cover" />
                        @else
                            <div class="h-24 w-24 rounded-full bg-gray-200 flex items-center justify-center text-2xl font-medium text-gray-500">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <h1 class="text-xl font-semibold text-gray-900">{{ $user->name }}</h1>
                        @if ($user->bio)
                            <p class="mt-2 text-gray-600 whitespace-pre-line">{{ $user->bio }}</p>
                        @else
                            <p class="mt-2 text-sm text-gray-500">{{ __('No bio yet.') }}</p>
                        @endif
                        @auth
                            @if (auth()->id() === $user->id)
                                <a href="{{ route('profile.edit') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">{{ __('Edit profile') }}</a>
                            @else
                                @php
                                    $frState = $areFriends ? 'friends' : ($friendRequestFromThem ? 'pending_from_them' : ($friendRequestFromMe ? 'pending_from_me' : 'add_friend'));
                                @endphp
                                <div
                                    class="mt-4"
                                    x-data="friendRequestProfile({
                                        state: '{{ $frState }}',
                                        receiverId: {{ $user->id }},
                                        storeUrl: '{{ route('friend-requests.store') }}',
                                        acceptUrl: {{ $friendRequestFromThem ? "'" . route('friend-requests.accept', $friendRequestFromThem) . "'" : 'null' }},
                                        rejectUrl: {{ $friendRequestFromThem ? "'" . route('friend-requests.reject', $friendRequestFromThem) . "'" : 'null' }},
                                        destroyUrl: {{ $friendRequestFromMe ? "'" . route('friend-requests.destroy', $friendRequestFromMe) . "'" : 'null' }},
                                        csrf: '{{ csrf_token() }}'
                                    })"
                                >
                                    <template x-if="state === 'friends'">
                                        <span class="inline-flex items-center px-4 py-2 bg-gray-200 rounded-md text-xs font-medium text-gray-700">{{ __('Friends') }}</span>
                                    </template>
                                    <template x-if="state === 'pending_from_them'">
                                        <div class="flex gap-2">
                                            <button type="button" @click="acceptRequest()" :disabled="loading" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 disabled:opacity-50">{{ __('Accept request') }}</button>
                                            <button type="button" @click="rejectRequest()" :disabled="loading" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 disabled:opacity-50">{{ __('Reject') }}</button>
                                        </div>
                                    </template>
                                    <template x-if="state === 'pending_from_me'">
                                        <button type="button" @click="cancelRequest()" :disabled="loading" class="inline-flex items-center px-4 py-2 bg-gray-200 rounded-md text-xs font-medium text-gray-700 hover:bg-gray-300 disabled:opacity-50">{{ __('Cancel request') }}</button>
                                    </template>
                                    <template x-if="state === 'add_friend'">
                                        <button type="button" @click="sendRequest()" :disabled="loading" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 disabled:opacity-50" x-text="loading ? '…' : '{{ __('Add friend') }}'"></button>
                                    </template>
                                </div>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
