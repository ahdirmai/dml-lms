<aside id="desktop-sidebar" class="hidden lg:block desktop-sidebar">
    <div class="sticky top-24">
        <div
            class="bg-white rounded-2xl shadow-custom-soft border border-gray-100 p-4 lg:p-5 max-h-[calc(100vh-7rem)] overflow-y-auto">
            <div class="flex items-center justify-between gap-2">
                <h2 class="text-base lg:text-lg font-bold text-gray-800">Materi Kursus</h2>
                <a href="{{ route('user.courses.show', $course->id) }}" class="text-[11px] text-brand hover:underline">
                    Lihat Semua
                </a>
            </div>
            <div class="mt-4 space-y-4">
                @php
                $previousLessonCompleted = true;
                @endphp
                @forelse(($modules ?? []) as $mod)
                <div class="rounded-2xl border border-gray-100 overflow-hidden bg-soft/40">
                    <div
                        class="bg-gray-50 px-3.5 py-2.5 text-[13px] font-semibold text-gray-700 flex items-center justify-between">
                        <span class="line-clamp-1">
                            {{ $mod['title'] ?? 'Modul' }}
                        </span>
                    </div>
                    <ul class="divide-y divide-gray-100">
                        @foreach(($mod['lessons'] ?? []) as $ls)
                        @php
                        $isActive = ($lesson->id ?? null) === ($ls['id'] ?? null);
                        $isDone = $ls['is_done'] ?? false;
                        $isAccessible = $ls['is_accessible'] ?? true;

                        // Enforce sequential check in view
                        if (! $previousLessonCompleted) {
                            $isAccessible = false;
                        }

                        // Update tracker
                        $previousLessonCompleted = $isDone;

                        $type = $ls['kind'] ?? 'youtube';
                        @endphp
                        <li>
                            @if($isAccessible)
                                <a href="{{ route('user.lessons.show', $ls['id']) }}"
                                    class="flex items-center gap-3 px-3.5 py-2.5 text-[13px] {{ $isActive ? 'bg-brand/8 text-brand font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
                                    @if($type === 'quiz')
                                    <span title="Quiz"
                                        class="inline-flex items-center justify-center w-6 h-6 text-xs rounded-full border {{ $isActive ? 'border-brand text-brand bg-white' : 'border-gray-300 text-gray-400 bg-white' }}">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </span>
                                    @elseif($type === 'gdrive' || $type === 'pdf')
                                    <span title="Dokumen"
                                        class="inline-flex items-center justify-center w-6 h-6 text-xs rounded-full border {{ $isActive ? 'border-brand text-brand bg-white' : 'border-gray-300 text-gray-400 bg-white' }}">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    </span>
                                    @elseif($type === 'text')
                                    <span title="Artikel"
                                        class="inline-flex items-center justify-center w-6 h-6 text-xs rounded-full border {{ $isActive ? 'border-brand text-brand bg-white' : 'border-gray-300 text-gray-400 bg-white' }}">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path></svg>
                                    </span>
                                    @else
                                    <span title="Video"
                                        class="inline-flex items-center justify-center w-6 h-6 text-[11px] rounded-full border {{ $isActive ? 'border-brand text-brand bg-white' : 'border-gray-300 text-gray-400 bg-white' }}">▶</span>
                                    @endif
                                    <span class="flex-1 line-clamp-2">{{ $ls['title'] ?? 'Pelajaran' }}</span>
                                    @if($isDone)
                                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    @endif
                                </a>
                            @else
                                <div class="flex items-center gap-3 px-3.5 py-2.5 text-[13px] text-gray-400 cursor-not-allowed opacity-60"
                                    data-lesson-id="{{ $ls['id'] }}"
                                    title="Selesaikan pelajaran sebelumnya terlebih dahulu">
                                    @if($type === 'quiz')
                                    <span title="Quiz"
                                        class="inline-flex items-center justify-center w-6 h-6 text-xs rounded-full border border-gray-300 text-gray-400 bg-white">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </span>
                                    @elseif($type === 'gdrive' || $type === 'pdf')
                                    <span title="Dokumen"
                                        class="inline-flex items-center justify-center w-6 h-6 text-xs rounded-full border border-gray-300 text-gray-400 bg-white">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    </span>
                                    @elseif($type === 'text')
                                    <span title="Artikel"
                                        class="inline-flex items-center justify-center w-6 h-6 text-xs rounded-full border border-gray-300 text-gray-400 bg-white">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path></svg>
                                    </span>
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
        </div>
    </div>
</aside>