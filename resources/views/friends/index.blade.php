<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>
            <h2 class="font-black text-2xl text-gray-800 leading-tight">
                {{ __('قائمة الأصدقاء') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            @if (session('status'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" 
                     class="flex items-center p-4 mb-4 text-emerald-800 rounded-2xl bg-emerald-50 border border-emerald-100 shadow-sm transition-all">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293l-4 4a1 1 0 01-1.414 0l-2-2a1 1 0 111.414-1.414L9 10.586l3.293-3.293a1 1 0 111.414 1.414z"/></svg>
                    <span class="text-sm font-bold">
                        @switch(session('status'))
                            @case('friend-request-sent') {{ __('تم إرسال طلب الصداقة بنجاح.') }} @break
                            @case('friend-request-accepted') {{ __('تم قبول طلب الصداقة. لديكم الآن صديق جديد!') }} @break
                            @case('friend-request-rejected') {{ __('تم رفض طلب الصداقة.') }} @break
                            @case('friend-request-cancelled') {{ __('تم إلغاء الطلب.') }} @break
                        @endswitch
                    </span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-soft sm:rounded-[2rem] border border-gray-100">
                <div class="p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-black text-gray-900 flex items-center gap-2">
                            {{ __('طلبات الصداقة') }}
                            <span class="px-2.5 py-0.5 bg-indigo-100 text-indigo-700 text-xs rounded-full">{{ $pendingRequests->count() }}</span>
                        </h3>
                    </div>

                    @if ($pendingRequests->isEmpty())
                        <div class="text-center py-10">
                            <div class="bg-gray-50 w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                            </div>
                            <p class="text-gray-400 font-medium">{{ __('لا توجد طلبات معلقة حالياً.') }}</p>
                        </div>
                    @else
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($pendingRequests as $req)
                                <div class="relative group p-4 bg-gray-50 rounded-3xl border border-transparent hover:border-indigo-100 hover:bg-white hover:shadow-xl transition-all duration-300"
                                     x-data="friendRequestAcceptReject({
                                        acceptUrl: '{{ route('friend-requests.accept', $req) }}',
                                        rejectUrl: '{{ route('friend-requests.reject', $req) }}',
                                        csrf: '{{ csrf_token() }}'
                                     })">
                                    <div class="flex items-center gap-4 mb-4">
                                        <div class="relative">
                                            @if ($req->sender->avatarUrl())
                                                <img src="{{ $req->sender->avatarUrl() }}" class="h-14 w-14 rounded-2xl object-cover shadow-sm" />
                                            @else
                                                <div class="h-14 w-14 rounded-2xl bg-indigo-600 flex items-center justify-center text-white text-xl font-bold">
                                                    {{ strtoupper(substr($req->sender->name, 0, 1)) }}
                                                </div>
                                            @endif
                                            <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-amber-400 border-2 border-white rounded-full flex items-center justify-center">
                                                <span class="animate-ping absolute h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                                <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6z" /></svg>
                                            </div>
                                        </div>
                                        <div class="overflow-hidden">
                                            <a href="{{ route('users.show', $req->sender) }}" class="block font-black text-gray-900 truncate hover:text-indigo-600 transition-colors">{{ $req->sender->name }}</a>
                                            <span class="text-xs text-gray-400 font-medium">يريد مصادقتك</span>
                                        </div>
                                    </div>
                                    <div class="flex gap-2">
                                        <button @click="accept()" :disabled="loading" class="flex-1 py-2 bg-indigo-600 text-white text-xs font-black rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-100 transition-all disabled:opacity-50">
                                            {{ __('قبول') }}
                                        </button>
                                        <button @click="reject()" :disabled="loading" class="px-4 py-2 bg-white text-gray-500 text-xs font-bold rounded-xl border border-gray-100 hover:bg-rose-50 hover:text-rose-600 hover:border-rose-100 transition-all">
                                            {{ __('رفض') }}
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-soft sm:rounded-[2rem] border border-gray-100">
                <div class="p-8">
                    <h3 class="text-xl font-black text-gray-900 mb-8 flex items-center gap-2">
                        {{ __('أصدقاؤك الحاليون') }}
                        <span class="text-gray-300 font-light">/</span>
                        <span class="text-indigo-600">{{ $friends->count() }}</span>
                    </h3>

                    @if ($friends->isEmpty())
                        <p class="text-sm text-gray-400 italic">{{ __('لم تقم بإضافة أصدقاء بعد. ابدأ بالبحث عن أشخاص تعرفهم!') }}</p>
                    @else
                        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                            @foreach ($friends as $friend)
                                <div class="group text-center">
                                    <a href="{{ route('users.show', $friend) }}" class="block relative mb-3">
                                        <div class="relative inline-block">
                                            @if ($friend->avatarUrl())
                                                <img src="{{ $friend->avatarUrl() }}" class="h-20 w-20 rounded-[2rem] object-cover ring-4 ring-gray-50 group-hover:ring-indigo-100 transition-all duration-300 shadow-md" />
                                            @else
                                                <div class="h-20 w-20 rounded-[2rem] bg-gray-100 flex items-center justify-center text-gray-400 text-2xl font-black group-hover:bg-indigo-50 group-hover:text-indigo-400 transition-all">
                                                    {{ strtoupper(substr($friend->name, 0, 1)) }}
                                                </div>
                                            @endif
                                            <span class="absolute top-0 right-0 w-5 h-5 bg-emerald-500 border-4 border-white rounded-full"></span>
                                        </div>
                                    </a>
                                    <a href="{{ route('users.show', $friend) }}" class="font-black text-gray-800 group-hover:text-indigo-600 transition-colors block">{{ $friend->name }}</a>
                                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1 italic">صديق</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <style>
        .shadow-soft { shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.02); }
    </style>
</x-app-layout>