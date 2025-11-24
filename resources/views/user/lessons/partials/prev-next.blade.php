<div
    class="bg-white rounded-2xl shadow-custom-soft border border-gray-100 px-3.5 py-3 sm:px-4 sm:py-3.5 flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3 justify-between">
    @if($prev)
    <a href="{{ route('user.lessons.show', $prev['id']) }}"
        class="inline-flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-2xl bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs sm:text-sm font-semibold">
        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        <span class="line-clamp-1">{{ $prev['title'] ?? 'Sebelumnya' }}</span>
    </a>
    @else
    <span class="text-[11px] text-gray-400">Awal modul</span>
    @endif

    @if($next)
        @if($isCompleted ?? false)
            <a href="{{ route('user.lessons.show', $next['id']) }}"
                class="inline-flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-2xl bg-brand hover:brightness-95 text-white text-xs sm:text-sm font-semibold">
                <span class="line-clamp-1">{{ $next['title'] ?? 'Berikutnya' }}</span>
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        @else
            <div class="inline-flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-2xl bg-gray-300 text-gray-500 text-xs sm:text-sm font-semibold cursor-not-allowed opacity-60"
                title="Selesaikan pelajaran ini terlebih dahulu">
                <span class="line-clamp-1">{{ $next['title'] ?? 'Berikutnya' }}</span>
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </div>
        @endif
    @else
        {{-- Jika tidak ada lesson berikutnya, cek apakah ada Post Test --}}
        @if(($course->posttest ?? false) && ($isCompleted ?? false))
            @if($enrollment->certificate ?? false)
            <a href="{{ route('user.courses.show', $course->id) }}"
                class="inline-flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-2xl bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs sm:text-sm font-semibold">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                <span class="line-clamp-1">Kembali ke Course</span>
            </a>
            @else
            <a href="{{ route('user.courses.show', ['course' => $course->id, 'open' => 'posttest']) }}"
                class="inline-flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-2xl bg-brand hover:brightness-95 text-white text-xs sm:text-sm font-semibold">
                <span class="line-clamp-1">Lanjut ke Post Test</span>
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>
            @endif
        @else
            <span class="text-[11px] text-gray-400 text-right">Tidak ada pelajaran berikutnya</span>
        @endif
    @endif
</div>