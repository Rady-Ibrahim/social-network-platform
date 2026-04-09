<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm">
            <a href="{{ route('posts.index') }}" class="text-slate-400 hover:text-indigo-600 transition-colors font-bold">{{ __('الخلاصات') }}</a>
            <svg class="w-4 h-4 text-slate-300 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            <h2 class="font-black text-slate-900 leading-tight">
                {{ __('تعديل المنشور') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12 bg-[#f8fafc]">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-[0_10px_40px_rgba(0,0,0,0.03)] sm:rounded-[2.5rem] border border-slate-100 overflow-hidden"
                 x-data="{ 
                    imagePreview: '{{ $post->imageUrl() }}',
                    fileChosen(event) {
                        const file = event.target.files[0];
                        if (!file) return;
                        const reader = new FileReader();
                        reader.readAsDataURL(file);
                        reader.onload = e => this.imagePreview = e.target.result;
                    }
                 }">
                
                <div class="p-6 sm:p-10">
                    <form action="{{ route('posts.update', $post) }}" method="post" enctype="multipart/form-data" class="space-y-8">
                        @csrf
                        @method('PUT')

                        {{-- نص المنشور --}}
                        <div class="space-y-2">
                            <label for="content" class="text-xs font-black uppercase tracking-[0.2em] text-slate-400 ml-2">
                                {{ __('محتوى المنشور') }}
                            </label>
                            <textarea id="content" name="content" rows="6" 
                                class="block w-full rounded-3xl border-slate-100 bg-slate-50/50 p-5 text-slate-800 font-medium focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all resize-none" 
                                placeholder="{{ __('ماذا تريد أن تغير؟') }}" required>{{ old('content', $post->content) }}</textarea>
                            <x-input-error :messages="$errors->get('content')" class="mt-2" />
                        </div>

                        {{-- قسم الصورة --}}
                        <div class="space-y-4">
                            <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400 ml-2">
                                {{ __('صورة المنشور') }}
                            </label>
                            
                            <div class="relative group rounded-[2rem] overflow-hidden border-2 border-dashed border-slate-100 bg-slate-50 p-2 transition-all hover:border-indigo-200">
                                {{-- عرض الصورة الحالية أو الجديدة المرفوعة --}}
                                <template x-if="imagePreview">
                                    <div class="relative">
                                        <img :src="imagePreview" class="w-full max-h-[400px] object-cover rounded-[1.5rem] shadow-sm">
                                        <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center rounded-[1.5rem]">
                                            <p class="text-white text-xs font-bold bg-black/40 px-4 py-2 rounded-full backdrop-blur-sm">انقر بالأسفل لتغيير الصورة</p>
                                        </div>
                                    </div>
                                </template>

                                <template x-if="!imagePreview">
                                    <div class="py-12 text-center">
                                        <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                        <p class="text-slate-400 text-sm font-medium">لا توجد صورة لهذا المنشور</p>
                                    </div>
                                </template>
                            </div>

                            <div class="flex items-center gap-4">
                                <label class="cursor-pointer inline-flex items-center gap-2 px-6 py-3 bg-white border border-slate-200 rounded-2xl font-bold text-xs text-slate-600 uppercase tracking-widest hover:bg-slate-50 hover:border-indigo-200 transition-all">
                                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                                    {{ __('رفع صورة جديدة') }}
                                    <input type="file" name="image" class="hidden" @change="fileChosen" accept="image/*">
                                </label>
                                @if($post->image_path)
                                    <p class="text-[10px] font-bold text-amber-500 uppercase tracking-tighter">
                                        * ستيم استبدال الصورة الحالية عند الحفظ
                                    </p>
                                @endif
                            </div>
                            <x-input-error :messages="$errors->get('image')" class="mt-2" />
                        </div>

                        {{-- أزرار التحكم --}}
                        <div class="flex items-center justify-between pt-6 border-t border-slate-50">
                            <a href="{{ url()->previous() }}" class="text-sm font-bold text-slate-400 hover:text-slate-600 transition-colors">
                                {{ __('إلغاء') }}
                            </a>
                            
                            <button type="submit" class="inline-flex items-center justify-center px-10 py-4 bg-indigo-600 border border-transparent rounded-2xl font-black text-xs text-white uppercase tracking-[0.2em] shadow-lg shadow-indigo-100 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all">
                                {{ __('حفظ التغييرات') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>