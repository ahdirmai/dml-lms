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
                        $type = $ls['kind'] ?? 'youtube';
                        @endphp
                        <li>
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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                @endif
                            </a>
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