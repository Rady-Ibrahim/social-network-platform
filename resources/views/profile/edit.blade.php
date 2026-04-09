<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-black text-2xl text-slate-900 leading-tight tracking-tight">
                {{ __('إعدادات الحساب') }}
            </h2>
            <div class="hidden sm:flex items-center gap-2 text-xs font-bold text-slate-400 uppercase tracking-widest">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                {{ __('اتصال آمن') }}
            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-[#f8fafc]">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- شبكة الإعدادات --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                {{-- الجانب الأيسر: معلومات توضيحية --}}
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-indigo-600 rounded-[2.5rem] p-8 text-white shadow-xl shadow-indigo-100">
                        <div class="mb-6">
                            @if(auth()->user()->avatarUrl())
                                <img src="{{ auth()->user()->avatarUrl() }}" class="h-20 w-20 rounded-3xl object-cover border-4 border-white/20">
                            @else
                                <div class="h-20 w-20 rounded-3xl bg-white/10 flex items-center justify-center text-3xl font-black">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                        <h3 class="text-xl font-black mb-2">{{ auth()->user()->name }}</h3>
                        <p class="text-indigo-100 text-sm leading-relaxed opacity-80">
                            {{ __('إدارة معلوماتك الشخصية، كلمة المرور، وتفضيلات الأمان الخاصة بحسابك في مكان واحد.') }}
                        </p>
                    </div>

                    <div class="bg-white rounded-[2rem] p-6 border border-slate-100 shadow-sm">
                        <h4 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                            <span class="w-2 h-2 bg-emerald-500 rounded-full"></span>
                            {{ __('حالة الحساب') }}
                        </h4>
                        <ul class="space-y-3">
                            <li class="flex items-center justify-between text-xs font-medium">
                                <span class="text-slate-400">{{ __('تاريخ الانضمام') }}</span>
                                <span class="text-slate-700">{{ auth()->user()->created_at->format('M Y') }}</span>
                            </li>
                            <li class="flex items-center justify-between text-xs font-medium">
                                <span class="text-slate-400">{{ __('المنشورات') }}</span>
                                <span class="text-slate-700">{{ auth()->user()->posts_count ?? '0' }}</span>
                            </li>
                        </ul>
                    </div>
                </div>

                {{-- الجانب الأيمن: نماذج التعديل --}}
                <div class="lg:col-span-2 space-y-8">
                    
                    {{-- نموذج المعلومات الأساسية --}}
                    <section class="bg-white shadow-[0_10px_40px_rgba(0,0,0,0.03)] sm:rounded-[2.5rem] border border-slate-100 overflow-hidden transition-all hover:shadow-md">
                        <div class="p-6 sm:p-10">
                            <div class="mb-8">
                                <h3 class="text-lg font-black text-slate-900">{{ __('المعلومات الشخصية') }}</h3>
                                <p class="text-sm text-slate-400 font-medium">{{ __('قم بتحديث اسمك وبريدك الإلكتروني.') }}</p>
                            </div>
                            <div class="max-w-xl">
                                @include('profile.partials.update-profile-information-form')
                            </div>
                        </div>
                    </section>

                    {{-- نموذج كلمة المرور --}}
                    <section class="bg-white shadow-[0_10px_40px_rgba(0,0,0,0.03)] sm:rounded-[2.5rem] border border-slate-100 overflow-hidden transition-all hover:shadow-md">
                        <div class="p-6 sm:p-10">
                            <div class="mb-8">
                                <h3 class="text-lg font-black text-slate-900">{{ __('أمان الحساب') }}</h3>
                                <p class="text-sm text-slate-400 font-medium">{{ __('تأكد من استخدام كلمة مرور قوية وطويلة للبقاء آمناً.') }}</p>
                            </div>
                            <div class="max-w-xl">
                                @include('profile.partials.update-password-form')
                            </div>
                        </div>
                    </section>

                    {{-- نموذج حذف الحساب --}}
                    <section class="bg-rose-50/30 border border-rose-100 sm:rounded-[2.5rem] overflow-hidden">
                        <div class="p-6 sm:p-10">
                            <div class="mb-8">
                                <h3 class="text-lg font-black text-rose-900">{{ __('منطقة الخطر') }}</h3>
                                <p class="text-sm text-rose-600/70 font-medium">{{ __('بمجرد حذف حسابك، سيتم حذف جميع بياناتك نهائياً. يرجى الحذر.') }}</p>
                            </div>
                            <div class="max-w-xl">
                                @include('profile.partials.delete-user-form')
                            </div>
                        </div>
                    </section>
                    
                </div>
            </div>
        </div>
    </div>
</x-app-layout>