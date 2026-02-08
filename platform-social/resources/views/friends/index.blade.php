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

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Pending requests') }}</h3>
                @if ($pendingRequests->isEmpty())
                    <p class="text-sm text-gray-500">{{ __('No pending friend requests.') }}</p>
                @else
                    <ul class="space-y-3">
                        @foreach ($pendingRequests as $req)
                            <li class="flex items-center justify-between gap-4 py-2 border-b border-gray-100 last:border-0">
                                <div class="flex items-center gap-3">
                                    @if ($req->sender->avatarUrl())
                                        <img src="{{ $req->sender->avatarUrl() }}" alt="" class="h-10 w-10 rounded-full object-cover" />
                                    @else
                                        <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center text-sm font-medium text-gray-500">
                                            {{ strtoupper(substr($req->sender->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <a href="{{ route('users.show', $req->sender) }}" class="font-medium text-gray-900 hover:underline">{{ $req->sender->name }}</a>
                                </div>
                                <div class="flex gap-2">
                                    <form action="{{ route('friend-requests.accept', $req) }}" method="post" class="inline">
                                        @csrf
                                        <x-primary-button type="submit" class="!py-1 !text-sm">{{ __('Accept') }}</x-primary-button>
                                    </form>
                                    <form action="{{ route('friend-requests.reject', $req) }}" method="post" class="inline">
                                        @csrf
                                        <x-secondary-button type="submit" class="!py-1 !text-sm">{{ __('Reject') }}</x-secondary-button>
                                    </form>
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
                                    <img src="{{ $friend->avatarUrl() }}" alt="" class="h-10 w-10 rounded-full object-cover" />
                                @else
                                    <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center text-sm font-medium text-gray-500">
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
