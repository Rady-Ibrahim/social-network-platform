<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Friends') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            @if (session('status'))
                <p class="text-sm text-green-600">
                    @if (session('status') === 'friend-request-sent')
                        {{ __('Friend request sent.') }}
                    @elseif (session('status') === 'friend-request-accepted')
                        {{ __('Friend request accepted.') }}
                    @elseif (session('status') === 'friend-request-rejected')
                        {{ __('Friend request rejected.') }}
                    @elseif (session('status') === 'friend-request-cancelled')
                        {{ __('Friend request cancelled.') }}
                    @endif
                </p>
            @endif

            @if ($errors->any())
                <div class="p-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
                    @foreach ($errors->all() as $err)
                        <p>{{ $err }}</p>
                    @endforeach
                </div>
            @endif

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Pending requests') }}</h3>
                @if ($pendingRequests->isEmpty())
                    <p class="text-sm text-gray-500">{{ __('No pending friend requests.') }}</p>
                @else
                    <ul class="space-y-3" id="pending-friend-requests-list">
                        @foreach ($pendingRequests as $req)
                            <li
                                class="flex items-center justify-between gap-4 py-2 border-b border-gray-100 last:border-0"
                                x-data="friendRequestAcceptReject({
                                    acceptUrl: '{{ route('friend-requests.accept', $req) }}',
                                    rejectUrl: '{{ route('friend-requests.reject', $req) }}',
                                    csrf: '{{ csrf_token() }}'
                                })"
                            >
                                <div class="flex items-center gap-3">
                                    @if ($req->sender->avatarUrl())
                                        <div class="h-10 w-10 shrink-0 overflow-hidden rounded-full">
                                            <img src="{{ $req->sender->avatarUrl() }}" alt="" class="h-full w-full object-cover" width="40" height="40" />
                                        </div>
                                    @else
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gray-200 text-sm font-medium text-gray-500">
                                            {{ strtoupper(substr($req->sender->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <a href="{{ route('users.show', $req->sender) }}" class="font-medium text-gray-900 hover:underline">{{ $req->sender->name }}</a>
                                </div>
                                <div class="flex gap-2">
                                    <button type="button" @click="accept()" :disabled="loading" class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-3 py-1 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2 disabled:opacity-50">{{ __('Accept') }}</button>
                                    <button type="button" @click="reject()" :disabled="loading" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-3 py-1 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50">{{ __('Reject') }}</button>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Your friends') }}</h3>
                @if ($friends->isEmpty())
                    <p class="text-sm text-gray-500">{{ __('You have no friends yet.') }}</p>
                @else
                    <ul class="grid gap-3 sm:grid-cols-2">
                        @foreach ($friends as $friend)
                            <li class="flex items-center gap-3 py-2">
                                @if ($friend->avatarUrl())
                                    <div class="h-10 w-10 shrink-0 overflow-hidden rounded-full">
                                        <img src="{{ $friend->avatarUrl() }}" alt="" class="h-full w-full object-cover" width="40" height="40" />
                                    </div>
                                @else
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gray-200 text-sm font-medium text-gray-500">
                                        {{ strtoupper(substr($friend->name, 0, 1)) }}
                                    </div>
                                @endif
                                <a href="{{ route('users.show', $friend) }}" class="font-medium text-gray-900 hover:underline">{{ $friend->name }}</a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
