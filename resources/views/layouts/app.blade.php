<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            [x-cloak] { display: none !important; }
            body {
                font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, -apple-system, sans-serif;
                background-color: #f8fafc; /* لون خلفية مريح للعين */
            }
        </style>
    </head>
    <body class="antialiased selection:bg-indigo-100 selection:text-indigo-700">
        
        @auth
            <script>
                window.App = {
                    userId: {{ auth()->id() }},
                    userName: @json(auth()->user()->name),
                };
            </script>
        @endauth

        <div class="min-h-screen relative">
            
            {{-- زينة خلفية (Ambient Decor) - تعطي لمسة جمالية خفيفة خلف المحتوى --}}
            <div class="fixed inset-0 overflow-hidden pointer-events-none -z-10">
                <div class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] rounded-full bg-indigo-50/50 blur-[120px]"></div>
                <div class="absolute top-[20%] -right-[5%] w-[30%] h-[30%] rounded-full bg-purple-50/50 blur-[100px]"></div>
            </div>

            {{-- شريط التنقل --}}
            @include('layouts.navigation')

            @isset($header)
                <header class="relative overflow-hidden">
                    <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
                        <div class="relative z-10">
                            {{ $header }}
                        </div>
                    </div>
                </header>
            @endisset

            <main class="relative">
                {{-- نستخدم أنيميشن خفيف عند انتقال الصفحات إذا كنت تستخدم Livewire أو نكتفي بتنظيم الفراغات --}}
                <div class="pb-20">
                    {{ $slot }}
                </div>
            </main>

            {{-- تذييل بسيط (Optional Footer) --}}
            <footer class="py-10 text-center">
                <p class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-300">
                    &copy; {{ date('Y') }} {{ config('app.name') }}. Built for the future.
                </p>
            </footer>
        </div>

        {{-- نظام التنبيهات العائمة (Toasts) - اختياري إذا أردت استخدامه لاحقاً --}}
        <div id="toast-container" class="fixed bottom-5 right-5 z-[100] space-y-3"></div>

    </body>
</html>