{{-- resources/views/user/courses/partials/curriculum.blade.php --}}
@props([
'modules' => [],
'pretestGateActive' => false,
])

<section class="bg-white rounded-2xl shadow-custom-soft border border-gray-100 p-5 sm:p-6">
    <div class="flex items-center justify-between mb-3">
        <h3 class="text-lg sm:text-xl font-bold text-brand">Kurikulum Kursus</h3>
        <span class="text-xs text-gray-500">
            {{ count($modules) }} modul
        </span>
    </div>

    @if(empty($modules))
    <p class="text-sm text-gray-500">Belum ada modul yang terdaftar untuk kursus ini.</p>
    @else
    <div class="space-y-3">
        @foreach($modules as $modIndex => $m)
        @php
        $lessons = $m['lessons'] ?? [];
        $doneCount = collect($lessons)->where('is_done', true)->count();
        $totalCount = count($lessons);
        @endphp

        <div class="border border-gray-100 rounded-2xl overflow-hidden">
            <button type="button"
                class="w-full flex items-center justify-between px-4 py-3 bg-gray-50 hover:bg-gray-100 text-left">
                <div class="flex items-center gap-3">
                    <span
                        class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-brand/10 text-xs font-bold text-brand">
                        {{ $modIndex + 1 }}
                    </span>
                    <div>
                        <p class="text-sm sm:text-base font-semibold text-gray-800">
                            {{ $m['title'] ?? 'Modul' }}
                        </p>
                        <p class="text-[11px] text-gray-500">
                            {{ $doneCount }}/{{ $totalCount }} pelajaran selesai
                        </p>
                    </div>
                </div>
                <svg class="w-4 h-4 text-gray-400 transform transition-transform duration-200 group-[.open]:rotate-180"
                    viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <div class="border-t border-gray-100 bg-white">
                @if(empty($lessons))
                <p class="px-4 py-3 text-xs text-gray-500">Belum ada pelajaran.</p>
                @else
                <ul class="divide-y divide-gray-100">
                    @foreach($lessons as $ls)
                    @php
                    $isDone = $ls['is_done'] ?? false;
                    $isLocked = $ls['is_locked'] ?? false;
                    $type = $ls['type'] ?? 'video';
                    $icon = $type === 'quiz' ? '❓' : '▶';
                    @endphp
                    <li class="px-4 py-2.5">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <span
                                    class="inline-flex items-center justify-center w-6 h-6 rounded-full border text-[11px]
                                                              {{ $isDone ? 'border-emerald-500 text-emerald-600 bg-emerald-50'
                                                                         : ($type === 'quiz'
                                                                             ? 'border-rose-300 text-rose-500 bg-rose-50'
                                                                             : 'border-gray-300 text-gray-500 bg-gray-50') }}">
                                    {{ $icon }}
                                </span>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium truncate
                                                               {{ $isDone ? 'text-emerald-700' : 'text-gray-800' }}">
                                        {{ $ls['title'] ?? 'Pelajaran' }}
                                    </p>
                                    <p class="text-[11px] text-gray-500">
                                        {{ $ls['duration'] ?? '-' }}
                                        @if(($ls['questions'] ?? 0) > 0)
                                        • {{ $ls['questions'] }} pertanyaan
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                @if($isDone)
                                <span class="inline-flex items-center gap-1 text-[11px] text-emerald-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    Selesai
                                </span>
                                @elseif($isLocked)
                                <span class="inline-flex items-center gap-1 text-[11px] text-amber-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 11c.828 0 1.5.672 1.5 1.5S12.828 14 12 14s-1.5-.672-1.5-1.5S11.172 11 12 11z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 8V7a5 5 0 10-10 0v1M5 9h14v10H5z" />
                                    </svg>
                                    Terkunci
                                </span>
                                @else
                                <a href="{{ route('user.lessons.show', $ls['id']) }}"
                                    class="inline-flex items-center px-3 py-1.5 rounded-full text-[11px] font-semibold bg-brand/10 text-brand hover:bg-brand/20">
                                    Buka
                                </a>
                                @endif
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ul>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif
</section>