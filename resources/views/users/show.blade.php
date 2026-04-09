<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="h-2 w-2 rounded-full bg-indigo-500 animate-pulse"></div>
            <h2 class="font-black text-xl text-slate-900 leading-tight tracking-tight">
                {{ __('ملف التعريف') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12 bg-[#f8fafc]">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            
            {{-- بطاقة الهوية العلوية --}}
            <div class="relative bg-white shadow-[0_20px_50px_rgba(0,0,0,0.04)] sm:rounded-[3rem] border border-slate-100 overflow-hidden">
                
                {{-- خلفية جمالية علوية (Cover Photo Decor) --}}
                <div class="h-40 w-full bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 opacity-90"></div>

                <div class="px-6 pb-10 sm:px-12">
                    <div class="relative flex flex-col sm:flex-row items-end gap-6 -mt-16">
                        
                        {{-- الصورة الشخصية بتصميم عائم --}}
                        <div class="shrink-0 p-2 bg-white rounded-[2.5rem] shadow-xl">
                            @if ($user->avatarUrl())
                                <img src="{{ $user->avatarUrl() }}" alt="{{ $user->name }}" class="h-32 w-32 sm:h-40 sm:w-40 rounded-[2rem] object-cover" />
                            @else
                                <div class="h-32 w-32 sm:h-40 sm:w-40 rounded-[2rem] bg-slate-100 flex items-center justify-center text-4xl font-black text-indigo-300">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                            @endif
                        </div>

                        {{-- معلومات المستخدم الأساسية --}}
                        <div class="flex-1 pb-2 text-center sm:text-right">
                            <h1 class="text-3xl font-black text-slate-900 tracking-tight">{{ $user->name }}</h1>
                            <div class="flex flex-wrap justify-center sm:justify-start items-center gap-3 mt-2">
                                <span class="px-3 py-1 bg-indigo-50 text-indigo-600 text-[10px] font-black uppercase tracking-widest rounded-full border border-indigo-100">
                                    {{ __('عضو نشط') }}
                                </span>
                                <span class="text-slate-400 text-xs font-medium flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    {{ __('انضم في') }} {{ $user->created_at->format('M Y') }}
                                </span>
                            </div>
                        </div>

                        {{-- أزرار التفاعل (Edit or Add Friend) --}}
                        <div class="pb-2">
                            @auth
                                @if (auth()->id() === $user->id)
                                    <a href="{{ route('profile.edit') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-slate-900 text-white rounded-2xl font-bold text-xs uppercase tracking-widest hover:bg-slate-800 transition-all shadow-lg shadow-slate-200">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                        {{ __('تعديل البروفايل') }}
                                    </a>
                                @else
                                    {{-- نظام طلبات الصداقة المطور مع Alpine.js --}}
                                    @php
                                        $frState = $areFriends ? 'friends' : ($friendRequestFromThem ? 'pending_from_them' : ($friendRequestFromMe ? 'pending_from_me' : 'add_friend'));
                                    @endphp
                                    <div x-data="friendRequestProfile({
                                            state: '{{ $frState }}',
                                            receiverId: {{ $user->id }},
                                            storeUrl: '{{ route('friend-requests.store') }}',
                                            acceptUrl: {{ $friendRequestFromThem ? "'" . route('friend-requests.accept', $friendRequestFromThem) . "'" : 'null' }},
                                            rejectUrl: {{ $friendRequestFromThem ? "'" . route('friend-requests.reject', $friendRequestFromThem) . "'" : 'null' }},
                                            destroyUrl: {{ $friendRequestFromMe ? "'" . route('friend-requests.destroy', $friendRequestFromMe) . "'" : 'null' }},
                                            csrf: '{{ csrf_token() }}'
                                        })">
                                        
                                        <template x-if="state === 'friends'">
                                            <button class="inline-flex items-center gap-2 px-6 py-3 bg-emerald-50 text-emerald-600 rounded-2xl font-black text-xs uppercase border border-emerald-100 cursor-default">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" /><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" /></svg>
                                                {{ __('صديق') }}
                                            </button>
                                        </template>

                                        <template x-if="state === 'pending_from_them'">
                                            <div class="flex gap-2">
                                                <button @click="acceptRequest()" :disabled="loading" class="px-6 py-3 bg-indigo-600 text-white rounded-2xl font-black text-xs uppercase shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-all">
                                                    {{ __('قبول الطلب') }}
                                                </button>
                                                <button @click="rejectRequest()" :disabled="loading" class="px-4 py-3 bg-white border border-slate-200 text-slate-600 rounded-2xl font-black text-xs uppercase hover:bg-slate-50 transition-all">
                                                    {{ __('رفض') }}
                                                </button>
                                            </div>
                                        </template>

                                        <template x-if="state === 'pending_from_me'">
                                            <button @click="cancelRequest()" :disabled="loading" class="px-6 py-3 bg-slate-100 text-slate-500 rounded-2xl font-black text-xs uppercase hover:bg-rose-50 hover:text-rose-600 transition-all group">
                                                <span class="group-hover:hidden">{{ __('تم إرسال الطلب') }}</span>
                                                <span class="hidden group-hover:block">{{ __('إلغاء الطلب؟') }}</span>
                                            </button>
                                        </template>

                                        <template x-if="state === 'add_friend'">
                                            <button @click="sendRequest()" :disabled="loading" class="inline-flex items-center gap-2 px-8 py-3 bg-indigo-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-indigo-700 shadow-lg shadow-indigo-100 transition-all">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                                                <span x-text="loading ? 'جاري الإرسال...' : 'إضافة صديق'"></span>
                                            </button>
                                        </template>
                                    </div>
                                @endif
                            @endauth
                        </div>
                    </div>

                    {{-- قسم النبذة الشخصية (Bio) --}}
                    <div class="mt-12 pt-8 border-t border-slate-50">
                        <h2 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mb-4">{{ __('حول المستخدم') }}</h2>
                        <div class="bg-slate-50/50 rounded-[2rem] p-6 sm:p-8">
                            @if ($user->bio)
                                <p class="text-slate-700 leading-relaxed font-medium text-lg italic">
                                    "{{ $user->bio }}"
                                </p>
                            @else
                                <p class="text-slate-400 text-sm italic">{{ __('لا توجد نبذة شخصية حتى الآن.') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- قسم المنشورات الخاصة بالمستخدم --}}
            <div class="mt-12 space-y-6">
                <div class="flex items-center justify-between px-4">
                    <h3 class="font-black text-slate-800 text-lg tracking-tight">{{ __('المنشورات') }}</h3>
                    <div class="h-px flex-1 mx-4 bg-slate-100"></div>
                </div>
                
                {{-- هنا يتم استدعاء قائمة المنشورات كما في الـ Feed --}}
                <div class="space-y-8">
                    @forelse ($user->posts as $post)
                         <x-post-card :post="$post" />
                    @empty
                        <div class="text-center py-12 bg-white rounded-[2rem] border border-slate-100">
                             <p class="text-slate-400 font-bold italic">{{ __('لم يقم بنشر أي شيء بعد.') }}</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</x-app-layout>