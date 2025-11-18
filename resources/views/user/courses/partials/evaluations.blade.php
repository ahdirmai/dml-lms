{{-- resources/views/user/courses/partials/evaluations.blade.php --}}
@props([
'pre' => ['score'=>0,'total'=>100,'date'=>'-','badge'=>'—','desc'=>'—'],
'post' => ['score'=>0,'total'=>100,'date'=>'-','badge'=>'—','desc'=>'—'],
])

<section class="grid grid-cols-1 md:grid-cols-2 gap-4">
    {{-- PRETEST --}}
    <div class="bg-white rounded-2xl shadow-custom-soft border border-gray-100 p-5 sm:p-6">
        <p class="text-[11px] font-extrabold text-gray-500 tracking-wider uppercase">Evaluasi Awal</p>
        <h4 class="text-lg sm:text-xl font-bold text-gray-900 mt-0.5">Pre-Test</h4>

        <div class="mt-3 flex items-end gap-3">
            <div>
                <div class="text-3xl sm:text-4xl font-extrabold text-blue-600">
                    {{ $pre['score'] }}/{{ $pre['total'] }}
                </div>
                <div class="text-[11px] text-gray-500 mt-0.5">Tanggal: {{ $pre['date'] }}</div>
            </div>
            <span
                class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-blue-50 text-blue-600">
                {{ $pre['badge'] }}
            </span>
        </div>

        <p class="mt-3 text-sm text-gray-600">
            {{ $pre['desc'] }}
        </p>

        <p class="mt-3 text-[11px] text-gray-400">
            Pre-test membantu mengukur pemahaman awal sebelum memulai materi.
        </p>
    </div>

    {{-- POSTTEST --}}
    <div class="bg-white rounded-2xl shadow-custom-soft border border-gray-100 p-5 sm:p-6">
        <p class="text-[11px] font-extrabold text-gray-500 tracking-wider uppercase">Evaluasi Akhir</p>
        <h4 class="text-lg sm:text-xl font-bold text-gray-900 mt-0.5">Post-Test</h4>

        <div class="mt-3 flex items-end gap-3">
            <div>
                <div class="text-3xl sm:text-4xl font-extrabold text-emerald-600">
                    {{ $post['score'] }}/{{ $post['total'] }}
                </div>
                <div class="text-[11px] text-gray-500 mt-0.5">Tanggal: {{ $post['date'] }}</div>
            </div>
            <span
                class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-emerald-50 text-emerald-600">
                {{ $post['badge'] }}
            </span>
        </div>

        <p class="mt-3 text-sm text-gray-600">
            {{ $post['desc'] }}
        </p>

        <p class="mt-3 text-[11px] text-gray-400">
            Post-test mengukur peningkatan kompetensi setelah seluruh materi selesai.
        </p>
    </div>
</section>