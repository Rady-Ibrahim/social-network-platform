<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-black text-2xl text-slate-900 leading-tight tracking-tight">
                {{ __('آخر الأخبار') }}
            </h2>
            <div class="flex -space-x-2">
                <div class="w-8 h-8 rounded-full border-2 border-white bg-emerald-500 shadow-sm"></div>
                <div class="w-8 h-8 rounded-full border-2 border-white bg-indigo-500 shadow-sm"></div>
                <div class="w-8 h-8 rounded-full border-2 border-white bg-slate-200 flex items-center justify-center text-[10px] font-bold text-slate-500 shadow-sm">+12</div>
            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-[#f8fafc]">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            @if (session('status') || session('error') || $errors->any())
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
                    @if (session('error') || $errors->any())
                        <div class="p-4 rounded-3xl bg-rose-50 border border-rose-100 text-rose-700 text-sm font-bold shadow-sm flex items-center gap-3">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                            {{ session('error') ?? __('حدث خطأ ما، يرجى المراجعة.') }}
                        </div>
                    @else
                        <div class="p-4 rounded-3xl bg-emerald-50 border border-emerald-100 text-emerald-700 text-sm font-bold shadow-sm flex items-center gap-3">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293l-4 4a1 1 0 01-1.414 0l-2-2a1 1 0 111.414-1.414L9 10.586l3.293-3.293a1 1 0 111.414 1.414z" clip-rule="evenodd" /></svg>
                            {{ __('تمت العملية بنجاح!') }}
                        </div>
                    @endif
                </div>
            @endif

            <div class="bg-white shadow-[0_10px_40px_rgba(0,0,0,0.04)] sm:rounded-[2.5rem] border border-slate-100 overflow-hidden" 
                 x-data="{ 
                    content: '', 
                    imagePreview: null,
                    fileChosen(event) {
                        const file = event.target.files[0];
                        if (!file) return;
                        const reader = new FileReader();
                        reader.readAsDataURL(file);
                        reader.onload = e => this.imagePreview = e.target.result;
                    }
                 }">
                <div class="p-6 sm:p-8">
                    <form action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        <div class="flex gap-4">
                            <div class="shrink-0">
                                @if(auth()->user()->avatarUrl())
                                    <img src="{{ auth()->user()->avatarUrl() }}" class="h-12 w-12 rounded-2xl object-cover shadow-sm">
                                @else
                                    <div class="h-12 w-12 rounded-2xl bg-indigo-100 flex items-center justify-center text-indigo-600 font-black text-lg">
                                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1">
                                <textarea name="content" x-model="content" rows="3" 
                                    class="block w-full border-none p-0 focus:ring-0 text-lg font-medium text-slate-800 placeholder:text-slate-300 resize-none bg-transparent" 
                                    placeholder="{{ __('بماذا تفكر يا ' . auth()->user()->name . '؟') }}" required></textarea>
                            </div>
                        </div>

                        <template x-if="imagePreview">
                            <div class="relative mt-4 rounded-[2rem] overflow-hidden border border-slate-100 group">
                                <img :src="imagePreview" class="w-full max-h-80 object-cover">
                                <button type="button" @click="imagePreview = null; $refs.imageInput.value = ''" 
                                    class="absolute top-4 right-4 bg-white/90 backdrop-blur p-2 rounded-full text-rose-600 shadow-xl hover:bg-rose-600 hover:text-white transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>
                        </template>

                        <div class="flex items-center justify-between pt-4 border-t border-slate-50">
                            <div class="flex items-center gap-2">
                                <label class="cursor-pointer group flex items-center gap-2 px-4 py-2 rounded-full bg-slate-50 hover:bg-indigo-50 transition-all">
                                    <svg class="w-5 h-5 text-slate-400 group-hover:text-indigo-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    <span class="text-xs font-black text-slate-500 group-hover:text-indigo-600 uppercase tracking-widest">{{ __('صورة') }}</span>
                                    <input type="file" name="image" class="hidden" x-ref="imageInput" @change="fileChosen" accept="image/*">
                                </label>
                            </div>

                            <button type="submit" 
                                :disabled="!content.trim()"
                                class="inline-flex items-center justify-center px-8 py-3 bg-indigo-600 border border-transparent rounded-2xl font-black text-xs text-white uppercase tracking-[0.2em] shadow-lg shadow-indigo-100 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-30 disabled:shadow-none transition-all">
                                {{ __('نشر الآن') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="space-y-10">
                @forelse ($posts as $post)
                    <div class="transition-all duration-500 transform">
                        <x-post-card :post="$post" />
                    </div>
                @empty
                    <div class="py-20 text-center bg-white rounded-[2.5rem] border-2 border-dashed border-slate-100">
                        <div class="mb-4 inline-flex p-4 bg-slate-50 rounded-3xl">
                            <svg class="w-12 h-12 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10l4 4v10a2 2 0 01-2 2z" /><path d="M14 3v5h5" /><path d="M8 13h8" /><path d="M8 17h8" /><path d="M10 9H8" /></svg>
                        </div>
                        <p class="text-slate-400 font-bold italic tracking-tight">{{ __('لا توجد منشورات حتى الآن. كن أول من يشارك!') }}</p>
                    </div>
                @endforelse
            </div>

            @if ($posts->hasPages())
                <div class="flex justify-center py-8">
                    <div class="inline-flex p-1 bg-white rounded-2xl shadow-sm border border-slate-100">
                        {{ $posts->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>