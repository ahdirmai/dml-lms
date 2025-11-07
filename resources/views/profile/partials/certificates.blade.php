{{-- resources/views/profile/partials/certificates.blade.php --}}
@php
/** @var array<int, array> $certificates */
    $certificates = $certificates ?? [
    [
    'title' => 'Mastering Vue 3',
    'issuer' => 'Pro Code Kreator',
    'issued_at' => '2025-10-12',
    'score' => 92,
    'certificate_id' => 'DML-VUE3-9X2K4',
    'file_url' => '#', // ganti ke route storage/pdf jika sudah ada
    'verify_url' => '#', // ganti ke route verifikasi jika sudah ada
    'status' => 'Valid',
    ],
    [
    'title' => 'Laravel Testing Fundamentals',
    'issuer' => 'DML Academy',
    'issued_at' => '2025-08-03',
    'score' => 88,
    'certificate_id' => 'DML-LTS-7HG21',
    'file_url' => '#',
    'verify_url' => '#',
    'status' => 'Valid',
    ],
    [
    'title' => 'Time Series Forecasting with Prophet',
    'issuer' => 'DataLab',
    'issued_at' => '2025-04-28',
    'score' => 95,
    'certificate_id' => 'DATA-PRO-PR-5521',
    'file_url' => '#',
    'verify_url' => '#',
    'status' => 'Valid',
    ],
    ];
    @endphp

    <div class="bg-white rounded-2xl border border-gray-100 shadow-custom-soft p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-bold text-gray-700">Sertifikat Saya</h3>
            @if(count($certificates) > 0)
            <span class="text-xs font-semibold text-gray-500">{{ count($certificates) }} sertifikat</span>
            @endif
        </div>

        @if(empty($certificates))
        <div class="rounded-xl border border-dashed border-gray-200 p-6 text-center">
            <p class="text-sm text-gray-500">Belum ada sertifikat. Selesaikan kursus untuk mendapatkan sertifikat 🎓</p>
        </div>
        @else
        {{-- List compact; batasi tinggi agar tidak memanjang; scroll jika banyak --}}
        <div class="space-y-3 max-h-[28rem] overflow-auto pr-1">
            @foreach($certificates as $i => $c)
            <div class="rounded-xl border border-gray-100 p-4 hover:bg-gray-50 transition">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-sm font-extrabold text-gray-900 leading-snug truncate">
                            {{ $c['title'] ?? '—' }}
                        </p>
                        <p class="text-xs text-gray-600">
                            Penerbit: <span class="font-medium">{{ $c['issuer'] ?? '—' }}</span>
                            <span class="mx-1">•</span>
                            Tgl Terbit: {{ \Illuminate\Support\Carbon::parse($c['issued_at'] ?? now())->format('d M Y')
                            }}
                        </p>

                        <div class="mt-1 flex flex-wrap items-center gap-2 text-xs">
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded-lg bg-blue-50 text-blue-700 font-semibold">
                                Skor {{ $c['score'] ?? '—' }}
                            </span>
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded-lg bg-emerald-50 text-emerald-700 font-semibold">
                                {{ $c['status'] ?? 'Valid' }}
                            </span>
                            <span class="text-gray-500">ID: <span class="font-mono text-gray-700">{{
                                    $c['certificate_id'] ?? '—' }}</span></span>
                        </div>
                    </div>

                    <div class="flex flex-col items-end gap-2 shrink-0">
                        <div class="flex items-center gap-2">
                            <a href="{{ $c['file_url'] ?? '#' }}" target="_blank"
                                class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-gray-100 text-gray-800 hover:bg-gray-200 transition">
                                Lihat
                            </a>
                            <a href="{{ $c['file_url'] ?? '#' }}" download
                                class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-500 text-white hover:bg-blue-600 transition">
                                Unduh
                            </a>
                        </div>
                        <a href="{{ $c['verify_url'] ?? '#' }}" target="_blank"
                            class="text-[11px] text-blue-600 hover:underline">
                            Verifikasi Sertifikat
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- CTA umum (opsional) --}}
        <div class="mt-4 flex items-center justify-between">
            <a href="#" class="text-xs font-semibold text-blue-600 hover:underline">Lihat semua sertifikat</a>
            <a href="#"
                class="inline-flex items-center px-3 py-2 rounded-xl text-xs font-semibold bg-gray-100 text-gray-800 hover:bg-gray-200 transition">
                Ekspor CSV
            </a>
        </div>
        @endif
    </div>
