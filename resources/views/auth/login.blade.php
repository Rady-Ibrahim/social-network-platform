<x-guest-layout>
    <div class="min-h-[500px] flex flex-col justify-center py-6 sm:py-10">
        {{-- رأس الصفحة الترحيبي --}}
        <div class="text-center mb-10">
            <h1 class="text-3xl font-black text-slate-900 tracking-tight mb-2">
                {{ __('مرحباً بك مجدداً!') }}
            </h1>
            <p class="text-slate-500 font-medium text-sm">
                {{ __('سجل دخولك لمتابعة ما فاتك من أخبار.') }}
            </p>
        </div>

        <x-auth-session-status class="mb-6 p-4 rounded-2xl bg-emerald-50 text-emerald-700 text-sm font-bold border border-emerald-100 shadow-sm" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="space-y-6">
            @csrf

            <div class="space-y-1">
                <label for="email" class="text-xs font-black uppercase tracking-[0.15em] text-slate-400 ml-1">
                    {{ __('البريد الإلكتروني') }}
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-indigo-500 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206" /></svg>
                    </div>
                    <input id="email" type="email" name="email" :value="old('email')" required autofocus 
                        class="block w-full pl-11 pr-4 py-4 rounded-2xl border-slate-100 bg-slate-50/50 text-slate-900 font-bold placeholder:text-slate-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all outline-none" 
                        placeholder="name@example.com" autocomplete="username">
                </div>
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="space-y-1" x-data="{ show: false }">
                <div class="flex items-center justify-between ml-1">
                    <label for="password" class="text-xs font-black uppercase tracking-[0.15em] text-slate-400">
                        {{ __('كلمة المرور') }}
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-[11px] font-black text-indigo-500 hover:text-indigo-700 uppercase tracking-tighter transition-colors">
                            {{ __('نسيت كلمة المرور؟') }}
                        </a>
                    @endif
                </div>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-indigo-500 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                    </div>
                    <input id="password" :type="show ? 'text' : 'password'" name="password" required 
                        class="block w-full pl-11 pr-12 py-4 rounded-2xl border-slate-100 bg-slate-50/50 text-slate-900 font-bold placeholder:text-slate-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all outline-none" 
                        placeholder="••••••••" autocomplete="current-password">
                    
                    {{-- زر إظهار/إخفاء كلمة المرور --}}
                    <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-indigo-500 transition-colors">
                        <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                        <svg x-show="show" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88L4.273 4.273M19.727 19.727l-5.852-5.852" /></svg>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="flex items-center">
                <label for="remember_me" class="group relative flex items-center cursor-pointer select-none">
                    <input id="remember_me" type="checkbox" name="remember" class="peer h-5 w-5 opacity-0 absolute">
                    <div class="h-6 w-6 rounded-lg border-2 border-slate-200 bg-white peer-checked:bg-indigo-600 peer-checked:border-indigo-600 transition-all flex items-center justify-center">
                        <svg class="w-4 h-4 text-white opacity-0 peer-checked:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                    </div>
                    <span class="ms-3 text-sm font-bold text-slate-500 group-hover:text-slate-700 transition-colors">{{ __('ابقِني متصلاً') }}</span>
                </label>
            </div>

            {{-- زر الدخول --}}
            <div class="pt-2">
                <button type="submit" class="w-full inline-flex items-center justify-center px-8 py-4 bg-indigo-600 border border-transparent rounded-[1.25rem] font-black text-xs text-white uppercase tracking-[0.25em] shadow-lg shadow-indigo-100 hover:bg-indigo-700 hover:-translate-y-0.5 focus:outline-none focus:ring-4 focus:ring-indigo-500/20 active:translate-y-0 transition-all">
                    {{ __('تسجيل الدخول') }}
                </button>
            </div>

            {{-- رابط التسجيل الجديد --}}
            <div class="text-center mt-6">
                <p class="text-sm text-slate-400 font-medium">
                    {{ __('ليس لديك حساب؟') }}
                    <a href="{{ route('register') }}" class="text-indigo-600 font-black hover:underline underline-offset-4 ms-1">
                        {{ __('أنشئ حساباً الآن') }}
                    </a>
                </p>
            </div>
        </form>
    </div>
</x-guest-layout>