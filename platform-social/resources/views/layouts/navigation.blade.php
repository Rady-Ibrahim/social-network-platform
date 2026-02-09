<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('posts.index') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex items-center">
                    <x-nav-link :href="route('posts.index')" :active="request()->routeIs('posts.*')">
                        {{ __('Feed') }}
                    </x-nav-link>
                    <x-nav-link :href="route('friends.index')" :active="request()->routeIs('friends.index')">
                        {{ __('Friends') }}
                    </x-nav-link>

                    <!-- User search -->
                    <div
                        x-data="{
                            q: '',
                            loading: false,
                            results: [],
                            error: null,
                            open: false,
                            controller: null,
                            async search() {
                                const query = this.q.trim();
                                if (!query) {
                                    this.results = [];
                                    this.error = null;
                                    this.open = false;
                                    return;
                                }

                                // إلغاء أي طلب قديم
                                if (this.controller) {
                                    this.controller.abort();
                                }
                                this.controller = new AbortController();

                                this.loading = true;
                                this.error = null;
                                try {
                                    const res = await fetch(`/api/users?q=${encodeURIComponent(query)}`, {
                                        headers: {
                                            'Accept': 'application/json',
                                            'X-Requested-With': 'XMLHttpRequest',
                                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? '',
                                        },
                                        credentials: 'same-origin',
                                        signal: this.controller.signal,
                                    });

                                    if (!res.ok) {
                                        this.results = [];
                                        this.error = `Error ${res.status}`;
                                        this.open = true;
                                        return;
                                    }

                                    const data = await res.json();
                                    this.results = Array.isArray(data) ? data : (data.data ?? []);
                                    this.open = this.results.length > 0;
                                } catch (e) {
                                    if (e.name !== 'AbortError') {
                                        console.error(e);
                                    }
                                } finally {
                                    this.loading = false;
                                }
                            }
                        }"
                        class="relative ms-6"
                    >
                        <div class="flex items-center">
                            <input
                                type="search"
                                x-model.debounce.400ms="q"
                                @input.debounce.400ms="search"
                                @focus="open = results.length > 0"
                                @keydown.escape.window="open = false"
                                placeholder="{{ __('Search users...') }}"
                                class="block w-52 lg:w-64 rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                            <svg
                                class="h-4 w-4 text-gray-400 -ms-6 pointer-events-none"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 100-15 7.5 7.5 0 000 15z" />
                            </svg>
                        </div>

                        <!-- Results dropdown -->
                        <div
                            x-cloak
                            x-show="open"
                            @click.outside="open = false"
                            x-transition
                            class="absolute z-40 mt-2 w-72 lg:w-80 rounded-md bg-white shadow-lg border border-gray-200"
                        >
                            <template x-if="loading">
                                <div class="px-3 py-2 text-xs text-gray-500">
                                    {{ __('Searching...') }}
                                </div>
                            </template>
                            <template x-if="!loading && error">
                                <div class="px-3 py-2 text-xs text-red-600">
                                    <span class="font-semibold">{{ __('Search error:') }}</span>
                                    <span x-text="error"></span>
                                </div>
                            </template>
                            <template x-if="!loading && !error && results.length === 0">
                                <div class="px-3 py-2 text-xs text-gray-500">
                                    {{ __('No users found.') }}
                                </div>
                            </template>
                            <template x-for="user in results" :key="user.id">
                                <a
                                    :href="`/users/${user.id}`"
                                    class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                    @click="open = false"
                                >
                                    <div
                                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gray-200 text-xs font-medium text-gray-600 overflow-hidden"
                                    >
                                        <template x-if="user.avatar_url">
                                            <img :src="user.avatar_url" alt="" class="h-full w-full object-cover" />
                                        </template>
                                        <template x-if="!user.avatar_url">
                                            <span x-text="(user.name || '?').charAt(0).toUpperCase()"></span>
                                        </template>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-medium text-gray-900" x-text="user.name"></div>
                                        <div class="text-xs text-gray-500" x-text="user.email ?? ''"></div>
                                    </div>
                                </a>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 space-x-4">
                {{-- Notifications --}}
                <div x-data="{ open: false }" class="relative">
                    <button
                        type="button"
                        @click="open = !open; $store.notifications.markAllAsRead()"
                        class="relative inline-flex items-center justify-center rounded-full bg-white p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        aria-label="Notifications"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span
                            x-show="$store.notifications.unreadCount() > 0"
                            x-text="$store.notifications.unreadCount()"
                            class="absolute -top-1 -right-1 inline-flex items-center justify-center rounded-full bg-red-500 px-1.5 text-[10px] font-semibold leading-4 text-white"
                        ></span>
                    </button>

                    <div
                        x-cloak
                        x-show="open"
                        @click.outside="open = false"
                        x-transition
                        class="absolute right-0 mt-2 w-80 max-w-xs origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none z-50"
                    >
                        <div class="px-4 py-2 border-b border-gray-100 flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700">
                                {{ __('Notifications') }}
                            </span>
                            <button
                                type="button"
                                class="text-xs text-indigo-600 hover:underline"
                                @click="$store.notifications.markAllAsRead()"
                            >
                                {{ __('Mark all as read') }}
                            </button>
                        </div>
                        <div class="max-h-80 overflow-y-auto">
                            <template x-if="$store.notifications.items.length === 0">
                                <div class="px-4 py-3 text-sm text-gray-500">
                                    {{ __('No notifications yet.') }}
                                </div>
                            </template>
                            <template x-for="notification in $store.notifications.items" :key="notification.id">
                                <div
                                    class="px-4 py-3 text-sm border-b border-gray-100 last:border-0"
                                    :class="notification.read ? 'bg-white' : 'bg-indigo-50'"
                                >
                                    <div class="text-gray-800" x-text="notification.message"></div>
                                    <div class="mt-1 text-xs text-gray-500" x-text="notification.created_at ?? ''"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- User menu --}}
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('posts.index')" :active="request()->routeIs('posts.*')">
                {{ __('Feed') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('friends.index')" :active="request()->routeIs('friends.index')">
                {{ __('Friends') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
