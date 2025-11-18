{{-- resources/views/dashboard/partials/performance-summary.blade.php --}}
@php
$overall = (int)($performance['overallProgress'] ?? 0);
$completed = (int)($performance['completed'] ?? 0);
$total = (int)($performance['total'] ?? 0);
$inProgress = (int)($performance['inProgress'] ?? 0);

$barColor = $overall === 100 ? 'bg-green-500' : 'bg-brand';
@endphp

<section class="grid grid-cols-1 mb-6">
    <div class="bg-white p-5 sm:p-6 rounded-2xl shadow-custom-soft border border-gray-100 h-full">
        <h2 class="text-lg sm:text-xl font-bold mb-4 text-brand">
            Ringkasan Performa Pelatihan
        </h2>

        <div class="mb-6">
            <div class="flex justify-between items-center mb-1">
                <span class="text-xs sm:text-sm font-medium text-gray-600">
                    Progres Keseluruhan Pelatihan ({{ $overall }}%)
                </span>
                <span class="text-xs sm:text-sm font-semibold text-brand">
                    {{ $completed }} / {{ $total }} Kelas Selesai
                </span>
            </div>
            <div class="w-full bg-soft rounded-full h-2.5 overflow-hidden">
                <div class="h-2.5 rounded-full {{ $barColor }}" style="width:{{ $overall }}%"></div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
            <div class="p-4 bg-green-50 rounded-xl shadow-sm border border-green-200">
                <x-ui.icon name="check-circle" class="w-7 h-7 mx-auto mb-1 text-green-600" />
                <div class="text-2xl sm:text-3xl font-bold text-dark">{{ $completed }}</div>
                <p class="text-xs text-gray-600 font-medium mt-1">Kelas Selesai</p>
            </div>

            <div class="p-4 bg-accent/10 rounded-xl shadow-sm border border-accent/20">
                <x-ui.icon name="clock" class="w-7 h-7 mx-auto mb-1 text-accent" />
                <div class="text-2xl sm:text-3xl font-bold text-dark">{{ $inProgress }}</div>
                <p class="text-xs text-gray-600 font-medium mt-1">Sedang Berjalan</p>
            </div>

            <div class="p-4 bg-brand/10 rounded-xl shadow-sm border border-brand/20">
                <x-ui.icon name="package" class="w-7 h-7 mx-auto mb-1 text-brand" />
                <div class="text-2xl sm:text-3xl font-bold text-dark">{{ $total }}</div>
                <p class="text-xs text-gray-600 font-medium mt-1">Total Kelas Ditugaskan</p>
            </div>
        </div>
    </div>
</section>