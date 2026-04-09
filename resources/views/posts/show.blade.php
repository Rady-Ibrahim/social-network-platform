<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <nav class="flex items-center text-sm font-bold tracking-tight">
                <a href="{{ route('posts.index') }}" class="text-gray-400 hover:text-indigo-600 transition-colors">{{ __('الخلاصات') }}</a>
                <svg class="w-5 h-5 text-gray-300 mx-2 rtl:rotate-180" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 11H3a1 1 0 110-2h7.586l-3.293-3.293a1 1 0 011.414-1.414l5 5a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                <span class="text-indigo-600">{{ __('عرض المنشور') }}</span>
            </nav>
        </div>
    </x-slot>

    <div class="py-12 bg-[#f8fafc]">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            {{-- Post Card --}}
            <article class="bg-white shadow-[0_10px_40px_rgba(0,0,0,0.03)] sm:rounded-[2.5rem] overflow-hidden border border-gray-100">
                <div class="p-6 sm:p-8">
                    {{-- Author Header --}}
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-4">
                            <div class="relative">
                                @if ($post->user->avatarUrl())
                                    <img src="{{ $post->user->avatarUrl() }}" class="h-12 w-12 rounded-2xl object-cover ring-4 ring-indigo-50" />
                                @else
                                    <div class="h-12 w-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-black shadow-lg">
                                        {{ strtoupper(substr($post->user->name, 0, 1)) }}
                                    </div>
                                @endif
                                <span class="absolute -bottom-1 -right-1 w-4 h-4 bg-emerald-500 border-2 border-white rounded-full"></span>
                            </div>
                            <div>
                                <a href="{{ route('users.show', $post->user) }}" class="block font-black text-gray-900 hover:text-indigo-600 transition-colors">
                                    {{ $post->user->name }}
                                </a>
                                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-widest">
                                    {{ $post->created_at->diffForHumans() }}
                                </span>
                            </div>
                        </div>

                        @can('update', $post)
                            <div class="flex gap-2" x-data="{ open: false }">
                                <a href="{{ route('posts.edit', $post) }}" class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                </a>
                                <form action="{{ route('posts.destroy', $post) }}" method="post" onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
                                    @csrf @method('DELETE')
                                    <button class="p-2 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </form>
                            </div>
                        @endcan
                    </div>

                    {{-- Content --}}
                    <div class="text-lg text-gray-800 leading-relaxed font-medium whitespace-pre-line mb-6">
                        {{ $post->content }}
                    </div>

                    @if ($post->image_path)
                        <div class="rounded-[2rem] overflow-hidden border border-gray-100 shadow-inner mb-6">
                            <img src="{{ $post->imageUrl() }}" class="w-full object-cover hover:scale-[1.02] transition-transform duration-700" />
                        </div>
                    @endif

                    {{-- Like Stats --}}
                    <div class="flex items-center justify-between py-4 border-t border-gray-50">
                        <div class="flex items-center -space-x-3 space-x-reverse">
                            @php $likeUsers = $post->likes->pluck('user')->unique('id'); @endphp
                            @foreach ($likeUsers->take(5) as $user)
                                <img src="{{ $user->avatarUrl() ?? 'https://ui-avatars.com/api/?name='.urlencode($user->name) }}" 
                                     class="w-8 h-8 rounded-full border-2 border-white object-cover" title="{{ $user->name }}">
                            @endforeach
                            @if($likeUsers->count() > 5)
                                <div class="w-8 h-8 rounded-full bg-gray-100 border-2 border-white flex items-center justify-center text-[10px] font-black text-gray-500">
                                    +{{ $likeUsers->count() - 5 }}
                                </div>
                            @endif
                            <span class="text-xs font-bold text-gray-400 mr-4">
                                {{ $post->likes_count }} {{ __('تفاعلوا مع هذا') }}
                            </span>
                        </div>
                    </div>
                </div>
            </article>

            {{-- Comments Section --}}
            <section class="space-y-6" id="comments">
                <div class="flex items-center justify-between px-4">
                    <h3 class="text-xl font-black text-gray-900">
                        {{ __('التعليقات') }} <span class="text-indigo-600 text-sm ml-1">{{ $comments->total() }}</span>
                    </h3>
                </div>

                {{-- Comment Input --}}
                <div class="bg-white p-4 rounded-[2rem] shadow-sm border border-gray-100">
                    <form action="{{ route('comments.store', $post) }}" method="post" class="relative group">
                        @csrf
                        <textarea name="body" rows="1" 
                            class="block w-full pl-20 pr-4 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-100 focus:bg-white transition-all resize-none font-medium" 
                            placeholder="{{ __('أضف تعليقك هنا...') }}" required></textarea>
                        <button type="submit" class="absolute left-2 top-2 bottom-2 px-6 bg-indigo-600 text-white rounded-xl font-black text-xs uppercase hover:bg-indigo-700 transition-all">
                            {{ __('نشر') }}
                        </button>
                    </form>
                </div>

                {{-- Comments List --}}
                <div class="space-y-4">
                    @forelse ($comments as $comment)
                        <div class="bg-white p-6 rounded-[2rem] border border-gray-50 shadow-sm" x-data="{ showReply: false }">
                            <div class="flex items-start gap-4">
                                <img src="{{ $comment->user->avatarUrl() ?? 'https://ui-avatars.com/api/?name='.urlencode($comment->user->name) }}" class="h-10 w-10 rounded-xl object-cover shrink-0" />
                                <div class="flex-1">
                                    <div class="flex items-center justify-between mb-1">
                                        <a href="{{ route('users.show', $comment->user) }}" class="font-black text-sm text-gray-900 hover:text-indigo-600">{{ $comment->user->name }}</a>
                                        <span class="text-[10px] font-bold text-gray-400 uppercase">{{ $comment->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="text-gray-700 text-sm leading-relaxed mb-3">{{ $comment->body }}</p>

                                    {{-- Actions --}}
                                    <div class="flex items-center gap-4">
                                        {{-- Like Logic (Simplified for UI) --}}
                                        <button class="text-[11px] font-black uppercase tracking-tighter transition-colors {{ $comment->is_liked_by_me ? 'text-indigo-600' : 'text-gray-400 hover:text-indigo-600' }}">
                                            {{ $comment->is_liked_by_me ? __('تم الإعجاب') : __('إعجاب') }} ({{ $comment->likes_count }})
                                        </button>
                                        <button @click="showReply = !showReply" class="text-[11px] font-black uppercase tracking-tighter text-gray-400 hover:text-indigo-600 transition-colors">
                                            {{ __('رد') }}
                                        </button>
                                        @can('delete', $comment)
                                            <form action="{{ route('comments.destroy', $comment) }}" method="post">
                                                @csrf @method('DELETE')
                                                <button class="text-[11px] font-black uppercase tracking-tighter text-gray-300 hover:text-rose-500 transition-colors">{{ __('حذف') }}</button>
                                            </form>
                                        @endcan
                                    </div>

                                    {{-- Replies --}}
                                    @if ($comment->replies->isNotEmpty())
                                        <div class="mt-4 space-y-4 border-r-2 border-indigo-50 pr-4">
                                            @foreach ($comment->replies as $reply)
                                                <div class="flex items-start gap-3">
                                                    <img src="{{ $reply->user->avatarUrl() ?? 'https://ui-avatars.com/api/?name='.urlencode($reply->user->name) }}" class="h-8 w-8 rounded-lg object-cover shadow-sm" />
                                                    <div class="flex-1 bg-gray-50/50 p-3 rounded-2xl">
                                                        <div class="flex items-center justify-between mb-1">
                                                            <span class="font-black text-xs text-gray-900">{{ $reply->user->name }}</span>
                                                            <span class="text-[9px] font-bold text-gray-400">{{ $reply->created_at->diffForHumans() }}</span>
                                                        </div>
                                                        <p class="text-xs text-gray-600 leading-normal">{{ $reply->body }}</p>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    {{-- Reply Input --}}
                                    <div class="mt-4" x-show="showReply" x-cloak x-transition>
                                        <form action="{{ route('comments.store', $post) }}" method="post" class="flex gap-2">
                                            @csrf
                                            <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                                            <input type="text" name="body" class="flex-1 bg-gray-50 border-none rounded-xl text-xs font-medium focus:ring-2 focus:ring-indigo-100" placeholder="اكتب ردك هنا..." required>
                                            <button class="px-4 py-2 bg-gray-900 text-white rounded-xl text-[10px] font-black uppercase hover:bg-black transition-all">رد</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12 bg-white rounded-[2rem] border border-dashed border-gray-200">
                            <p class="text-gray-400 font-medium italic">{{ __('كن أول من يعلق على هذا المنشور!') }}</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-app-layout>