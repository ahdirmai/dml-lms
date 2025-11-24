{{-- resources/views/user/courses/partials/sidebar.blade.php --}}
@props([
'pct' => 0,
'barColor' => 'bg-brand',
'status' => 'Not Started',
'statusBadgeClass' => 'text-gray-600 bg-gray-100',
'statusTextClass' => 'text-gray-600',
'totalModules' => 0,
'ctaHref' => '#',
'ctaLabel' => 'Mulai Belajar',

// NEW: gate pretest
'pretestGateActive'=> false,

// optional: info kursus
'course' => null,
'jenis' => 'E-learning Mandiri',
'estimatedDuration'=> null,
'validUntil' => null,

// optional: quick actions pre/post/review
'courseId' => null,
'hasPreTest' => false,
'hasPostTest' => false,
'preDone' => false,
'postDone' => false,
'preScore' => 0,
'postScore' => 0,
'canReview' => false,
'isAccessBlocked' => false,
'accessMessage' => null,
'hasReviewed' => false,
'reviewStars' => null,
])

<aside class="lg:sticky lg:top-20 space-y-4">
    <div class="bg-white p-5 sm:p-6 rounded-2xl shadow-xl border border-gray-100">
        {{-- STATUS --}}
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full {{ $statusBadgeClass }}">
                    <span class="w-2.5 h-2.5 rounded-full bg-current"></span>
                </span>
                <div>
                    <p class="text-xs text-gray-500">Status Kursus</p>
                    <p class="text-sm font-semibold {{ $statusTextClass }}">{{ $status }}</p>
                </div>
            </div>
        </div>

        {{-- PROGRESS MINI --}}
        <div class="mt-3 mb-4">
            <p class="text-xs text-gray-500 mb-1">
                Progres Anda
            </p>
            <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                <div class="{{ $barColor }} h-2.5 rounded-full transition-all duration-300" style="width: {{ $pct }}%">
                </div>
            </div>
            <div class="mt-1 flex justify-between text-[11px] text-gray-500">
                <span>{{ $pct }}% selesai</span>
                <span>{{ $totalModules }} modul</span>
            </div>
        </div>

        @php
        $ctaDisabled = ($course->require_pretest_before_content && $pretestGateActive) || $isAccessBlocked;
        $ctaTitle = '';
        if ($isAccessBlocked) {
            $ctaTitle = $accessMessage;
        } elseif ($ctaDisabled) {
            $ctaTitle = "Selesaikan pre-test untuk membuka materi.";
        }
        @endphp

        {{-- CTA utama (disable kalau pretest belum tercapai atau akses diblokir) --}}
        <a @if($ctaDisabled) href="javascript:void(0)" @else href="{{ $ctaHref }}" @endif class="w-full inline-flex items-center justify-center font-semibold py-2.5 rounded-xl text-sm transition shadow
                   {{ $ctaDisabled
                        ? 'bg-gray-300 text-gray-600 cursor-not-allowed pointer-events-none'
                        : 'bg-brand hover:brightness-95 text-white' }}" @if($ctaDisabled)
            title="{{ $ctaTitle }}" @endif>
            {{ $ctaLabel }}
        </a>

        {{-- Aksi cepat Pre-Test / Post-Test / Review --}}
        @if($courseId && ($hasPreTest || $hasPostTest || $canReview))
        <div class="mt-4 pt-4 border-t border-gray-100 space-y-3">
            <p class="text-xs font-bold text-gray-900 uppercase tracking-wide">Evaluasi & Review</p>
            <div class="space-y-2">
                {{-- PRE-TEST --}}
                @if($hasPreTest)
                    @if(!$preDone)
                        <button type="button" onclick="window.TestFlow?.startPreTest('{{ $courseId }}')" 
                            class="w-full flex items-center justify-between px-3 py-2 rounded-lg border border-blue-200 bg-blue-50 text-blue-700 hover:bg-blue-100 transition group">
                            <span class="text-xs font-semibold">Mulai Pre-Test</span>
                            <svg class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    @elseif($preScore < 100)
                        <button type="button" onclick="if(confirm('Apakah Anda yakin ingin mengerjakan ulang Pre-Test? Nilai sebelumnya akan tertimpa.')) window.TestFlow?.startPreTest('{{ $courseId }}')" 
                            class="w-full flex items-center justify-between px-3 py-2 rounded-lg border border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100 transition group">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-semibold">Ulangi Pre-Test</span>
                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-amber-200 text-amber-800 font-bold">{{ $preScore }}</span>
                            </div>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        </button>
                    @else
                        <div class="w-full flex items-center justify-between px-3 py-2 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-700 cursor-default opacity-75">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-semibold">Pre-Test Selesai</span>
                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-emerald-200 text-emerald-800 font-bold">100</span>
                            </div>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                    @endif
                @endif

                {{-- POST-TEST --}}
                @if($hasPostTest)
                    @if($pct < 100)
                        <div class="w-full flex items-center justify-between px-3 py-2 rounded-lg border border-gray-200 bg-gray-50 text-gray-400 cursor-not-allowed" title="Selesaikan 100% materi untuk membuka Post-Test">
                            <span class="text-xs font-semibold">Post-Test Terkunci</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                    @else
                        <button type="button" onclick="window.TestFlow?.startPostTest('{{ $courseId }}')" 
                            class="w-full flex items-center justify-between px-3 py-2 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition group">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-semibold">{{ $postDone ? 'Ulangi Post-Test' : 'Mulai Post-Test' }}</span>
                                @if($postDone)
                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-emerald-200 text-emerald-800 font-bold">{{ $postScore }}</span>
                                @endif
                            </div>
                            <svg class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    @endif
                @endif

                {{-- REVIEW --}}
                @if($canReview)
                    @if($hasReviewed)
                    <a href="{{ route('user.courses.certificate', $courseId) }}" target="_blank"
                        class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg border border-indigo-200 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition group">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="text-xs font-semibold">Lihat Sertifikat</span>
                        </div>
                        <svg class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                    @else
                    <button type="button" onclick="window.TestFlow?.openReview('{{ $courseId }}')" 
                        class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg border-2 border-amber-300 bg-gradient-to-r from-amber-50 to-yellow-50 text-amber-700 hover:from-amber-100 hover:to-yellow-100 shadow-sm hover:shadow-md transition-all group relative {{ $pct >= 100 ? 'animate-pulse' : '' }}">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 group-hover:rotate-12 transition-transform" fill="currentColor" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.967a1 1 0 00.95.69h4.178c.969 0 1.371 1.24.588 1.81l-3.383 2.46a1 1 0 00-.364 1.118l1.287 3.966c.3.922-.755 1.688-1.54 1.118l-3.383-2.46a1 1 0 00-1.175 0l-3.383 2.46c-.784.57-1.838-.196-1.539-1.118l1.287-3.966a1 1 0 00-.364-1.118L2.998 9.394c-.783-.57-.38-1.81.588-1.81h4.178a1 1 0 00.95-.69l1.286-3.967z"/></svg>
                            <span class="text-xs font-semibold">Beri Review</span>
                        </div>
                        <svg class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        @if($pct >= 100)
                        <span class="absolute -top-1 -right-1 flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-500"></span>
                        </span>
                        @endif
                    </button>
                    @endif
                @endif
            </div>
        </div>
        @endif

        <hr class="my-4">

        {{-- INFO KURSUS SINGKAT --}}
        <div class="space-y-2 text-xs text-gray-600">
            <p class="font-semibold text-gray-800">Info Singkat</p>

            <p>
                Jenis:
                <span class="font-medium text-gray-800">
                    {{ $jenis ?? ($slot ?? 'E-learning Mandiri') }}
                </span>
            </p>

            @php
            $est = $estimatedDuration ?? convert_seconds_to_duration(optional($course)->lessons_sum_duration_seconds ?? 0);
            $valid = $validUntil ?? optional($course)->valid_until;
            @endphp

            @if(!empty($est))
            <p>Estimasi:
                <span class="font-medium text-gray-800">{{ $est }}</span>
            </p>
            @endif

            @if(!empty($valid) && optional($course)->using_due_date)
            <p>Berlaku sampai:
                <span class="font-medium text-gray-800">
                    {{ \Carbon\Carbon::parse($valid)->format('d M Y') }}
                </span>
            </p>
            @endif

            @if($isAccessBlocked)
            <p class="text-rose-600 font-semibold mt-2">
                {{ $accessMessage }}
            </p>
            @endif
        </div>
    </div>
</aside>