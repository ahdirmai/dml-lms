<div id="offcanvas-backdrop" class="fixed inset-0 bg-black/40 z-40 hidden"></div>
<aside id="offcanvas"
    class="fixed z-50 inset-y-0 left-0 w-[88%] max-w-[22rem] bg-white shadow-xl border-r border-gray-100 p-4 sm:p-5 offcanvas-enter hidden">
    <div class="flex items-center justify-between mb-3 sm:mb-4">
        <h3 class="text-base sm:text-lg font-bold text-gray-800">Materi Kursus</h3>
        <button id="btn-close-sidebar"
            class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <div class="space-y-4 overflow-y-auto max-h-[calc(100vh-6rem)] pr-1">
        @forelse(($modules ?? []) as $mod)
        <div class="rounded-2xl border border-gray-100 overflow-hidden bg-soft/40">
            <div class="bg-gray-50 px-3.5 py-2.5 text-[13px] font-semibold text-gray-700">
                {{ $mod['title'] ?? 'Modul' }}
            </div>
            <ul class="divide-y divide-gray-100">
                @foreach(($mod['lessons'] ?? []) as $ls)
                @php
                $isActive = ($lesson->id ?? null) === ($ls['id'] ?? null);
                $isDone = $ls['is_done'] ?? false;
                $isAccessible = $ls['is_accessible'] ?? true;
                $type = $ls['kind'] ?? 'youtube';
                @endphp
                <li>
                    @if($isAccessible)
                        <a href="{{ route('user.lessons.show', $ls['id']) }}"
                            class="flex items-center gap-3 px-3.5 py-2.5 text-[13px] {{ $isActive ? 'bg-brand/8 text-brand font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
                            @if($type === 'quiz')
                            <span title="Quiz"
                                class="inline-flex items-center justify-center w-6 h-6 text-xs rounded-full border {{ $isActive ? 'border-brand text-brand bg-white' : 'border-gray-300 text-gray-400 bg-white' }}">?</span>
                            @else
                            <span
                                class="inline-flex items-center justify-center w-6 h-6 text-[11px] rounded-full border {{ $isActive ? 'border-brand text-brand bg-white' : 'border-gray-300 text-gray-400 bg-white' }}">▶</span>
                            @endif
                            <span class="flex-1 line-clamp-2">{{ $ls['title'] ?? 'Pelajaran' }}</span>
                            @if($isDone)
                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            @endif
                        </a>
                    @else
                        <div class="flex items-center gap-3 px-3.5 py-2.5 text-[13px] text-gray-400 cursor-not-allowed opacity-60"
                            data-lesson-id="{{ $ls['id'] }}"
                            title="Selesaikan pelajaran sebelumnya terlebih dahulu">
                            @if($type === 'quiz')
                            <span title="Quiz"
                                class="inline-flex items-center justify-center w-6 h-6 text-xs rounded-full border border-gray-300 text-gray-400 bg-white">?</span>
                            @else
                            <span
                                class="inline-flex items-center justify-center w-6 h-6 text-[11px] rounded-full border border-gray-300 text-gray-400 bg-white">▶</span>
                            @endif
                            <span class="flex-1 line-clamp-2">{{ $ls['title'] ?? 'Pelajaran' }}</span>
                            <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                    @endif
                </li>
                @endforeach
            </ul>
        </div>
        @empty
        <div class="text-sm text-gray-500">Belum ada modul.</div>
        @endforelse
    </div>
</aside>