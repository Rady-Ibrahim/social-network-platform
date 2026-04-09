<nav x-data="{ open: false }" class="bg-white/80 backdrop-blur-md sticky top-0 z-50 border-b border-slate-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-20">
            <div class="flex items-center gap-8">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('posts.index') }}" class="transition-transform hover:scale-105">
                        <x-application-logo class="block h-10 w-auto fill-current text-indigo-600" />
                    </a>
                </div>

                <div class="hidden space-x-1 sm:-my-px sm:flex items-center">
                    <x-nav-link :href="route('posts.index')" :active="request()->routeIs('posts.*')" class="px-4 py-2 rounded-xl transition-all font-bold">
                        {{ __('الرئيسية') }}
                    </x-nav-link>
                    <x-nav-link :href="route('friends.index')" :active="request()->routeIs('friends.index')" class="px-4 py-2 rounded-xl transition-all font-bold">
                        {{ __('الأصدقاء') }}
                    </x-nav-link>
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6 gap-4">
                
                <div x-data="{
                    q: '',
                    loading: false,
                    results: [],
                    error: null,
                    open: false,
                    controller: null,
                    async search() {
                        const query = this.q.trim();
                        if (!query) { this.results = []; this.open = false; return; }
                        if (this.controller) this.controller.abort();
                        this.controller = new AbortController();
                        this.loading = true;
                        try {
                            const res = await fetch(`/api/users?q=${encodeURIComponent(query)}`, {
                                headers: { 'Accept': 'application/json' },
                                signal: this.controller.signal
                            });
                            const data = await res.json();
                            this.results = Array.isArray(data) ? data : (data.data ?? []);
                            this.open = true;
                        } catch (e) { if (e.name !== 'AbortError') this.error = 'خطأ في البحث'; }
                        finally { this.loading = false; }
                    }
                }" class="relative">
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-slate-400 group-focus-within:text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input type="search" x-model.debounce.400ms="q" @input="search" @focus="if(results.length) open = true"
                            class="block w-48 lg:w-72 pl-10 pr-4 py-2.5 bg-slate-50 border-none rounded-2xl text-sm font-medium focus:ring-4 focus:ring-indigo-500/10 transition-all"
                            placeholder="{{ __('ابحث عن أشخاص...') }}">
                    </div>

                    <div x-show="open" @click.outside="open = false" x-transition x-cloak
                        class="absolute right-0 mt-3 w-80 bg-white rounded-[2rem] shadow-2xl shadow-indigo-200/50 border border-slate-100 overflow-hidden z-50">
                        <div class="p-2">
                            <template x-if="loading">
                                <div class="p-4 flex justify-center"><div class="animate-spin h-5 w-5 border-2 border-indigo-500 border-t-transparent rounded-full"></div></div>
                            </template>
                            <template x-for="user in results" :key="user.id">
                                <a :href="`/users/${user.id}`" class="flex items-center gap-3 p-3 rounded-2xl hover:bg-slate-50 transition-colors group">
                                    <div class="h-10 w-10 rounded-xl bg-indigo-100 flex items-center justify-center overflow-hidden font-black text-indigo-500">
                                        <template x-if="user.avatar_url"><img :src="user.avatar_url" class="object-cover h-full w-full"></template>
                                        <template x-if="!user.avatar_url"><span x-text="user.name[0]"></span></template>
                                    </div>
                                    <div>
                                        <div class="text-sm font-black text-slate-800 group-hover:text-indigo-600 transition-colors" x-text="user.name"></div>
                                        <div class="text-[10px] text-slate-400 font-bold" x-text="user.email"></div>
                                    </div>
                                </a>
                            </template>
                        </div>
                    </div>
                </div>

                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="p-2.5 rounded-2xl bg-slate-50 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 transition-all relative">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                        <span x-show="$store.notifications.unreadCount() > 0" x-text="$store.notifications.unreadCount()"
                            class="absolute top-1.5 right-1.5 flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-rose-500 px-1 text-[9px] font-black text-white ring-2 ring-white"></span>
                    </button>

                    <div x-show="open" @click.outside="open = false" x-transition x-cloak
                        class="absolute right-0 mt-3 w-80 bg-white rounded-[2rem] shadow-2xl border border-slate-100 overflow-hidden z-50">
                        <div class="px-6 py-4 border-b border-slate-50 flex justify-between items-center bg-slate-50/50">
                            <span class="font-black text-slate-800 text-sm">{{ __('التنبيهات') }}</span>
                            <button @click="$store.notifications.markAllAsRead()" class="text-[10px] font-black text-indigo-500 uppercase tracking-wider">{{ __('مسح الكل') }}</button>
                        </div>
                        <div class="max-h-96 overflow-y-auto p-2">
                            <template x-if="$store.notifications.items.length === 0">
                                <div class="p-8 text-center"><p class="text-xs text-slate-400 font-bold">{{ __('لا توجد تنبيهات جديدة') }}</p></div>
                            </template>
                            <template x-for="notification in $store.notifications.items" :key="notification.id">
                                <div @click="/* منطق الانتقال */" :class="notification.read ? 'opacity-60' : 'bg-indigo-50/50'" 
                                    class="p-4 rounded-2xl mb-1 cursor-pointer hover:bg-slate-50 transition-all border border-transparent hover:border-slate-100">
                                    <p class="text-xs text-slate-800 font-bold mb-1" x-text="notification.message"></p>
                                    <span class="text-[9px] text-slate-400 uppercase font-black" x-text="notification.created_at"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="flex items-center gap-2 p-1 pr-3 rounded-2xl bg-slate-900 text-white hover:bg-slate-800 transition-all shadow-lg shadow-slate-200">
                            <span class="text-xs font-black px-1">{{ Auth::user()->name }}</span>
                            <div class="h-8 w-8 rounded-xl bg-white/10 flex items-center justify-center font-black text-xs">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="p-2">
                            <x-dropdown-link :href="route('profile.edit')" class="rounded-xl font-bold"> {{ __('الملف الشخصي') }} </x-dropdown-link>
                            <div class="border-t border-slate-50 my-1"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();" class="rounded-xl font-bold text-rose-500">
                                    {{ __('تسجيل الخروج') }}
                                </x-dropdown-link>
                            </form>
                        </div>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="p-2 rounded-xl text-slate-400 hover:bg-slate-100 transition-colors">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</nav>