{{-- resources/views/dashboard/partials/leaderboards.blade.php --}}
@php
$getTopThree = function($data) {
$data = array_values($data ?? []);
return [
1 => $data[1] ?? null, // Rank 1
2 => $data[0] ?? null, // Rank 2
3 => $data[2] ?? null, // Rank 3
];
};

$topThreeCompleted = $getTopThree($leaderboardData['completedCourses'] ?? []);
$currentUserCompleted = collect($leaderboardData['completedCourses'] ?? [])->firstWhere('isYou', true);

$topThreePostTest = $getTopThree($leaderboardData['postTest'] ?? []);
$currentUserPostTest = collect($leaderboardData['postTest'] ?? [])->firstWhere('isYou', true);

$position = $userInfo['position'] ?? '';
@endphp

<div class="lg:col-span-2 flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4">
    {{-- Pelatihan terbanyak --}}
    <div class="bg-white p-4 rounded-2xl shadow-custom-soft border border-gray-100 flex flex-col md:w-1/2">
        <div class="subcard-header-count rounded-xl p-3 text-center text-base sm:text-lg font-bold">
            Pelatihan Terbanyak
        </div>
        <div class="p-3 pt-6 flex justify-around space-x-2">
            @foreach($topThreeCompleted as $rank => $item)
            @if($item)
            @php
            $rankOrderClass = $rank === 2 ? 'order-1' : ($rank === 1 ? 'order-2' : 'order-3');
            $scoreClass = $rank === 1 ? 'score-rank-1' : ($rank === 2 ? 'score-rank-2' : 'score-rank-3');
            $nameDisplay = explode(' ', $item['name'])[0] ?? 'User';
            @endphp
            <div class="flex flex-col items-center w-1/3 text-center p-2 {{ $rankOrderClass }}">
                <i data-lucide="rocket" class="w-8 h-8 text-accent mb-1"></i>
                <p class="font-medium text-xs text-gray-700">{{ $nameDisplay }}</p>
                <div class="text-[0.65rem] font-medium text-accent font-bold">
                    {{ $item['count'] }} Pelatihan
                </div>
                <div class="{{ $scoreClass }} score-box font-extrabold mt-3">
                    {{ $rank }}
                </div>
            </div>
            @endif
            @endforeach
        </div>

        @if($currentUserCompleted)
        <div class="text-sm font-semibold text-gray-500 mt-4 mb-2 border-t pt-3 px-3">
            Peringkat Anda
        </div>
        <div
            class="highlight-you rounded-lg flex justify-between items-center px-3 py-2 text-gray-800 border border-accent/30">
            <div class="flex items-center space-x-4">
                <span class="text-3xl font-extrabold text-accent w-10 text-center">
                    {{ $currentUserCompleted['rank'] }}
                </span>
                <div class="text-left">
                    <p class="font-bold text-sm text-accent">{{ $currentUserCompleted['name'] }}</p>
                    <p class="text-xs text-gray-600">{{ $position }}</p>
                </div>
            </div>
            <span class="text-sm font-bold text-gray-700">
                {{ $currentUserCompleted['count'] }} Pelatihan
            </span>
        </div>
        @endif
    </div>

    {{-- Nilai post test tertinggi --}}
    <div class="bg-white p-4 rounded-2xl shadow-custom-soft border border-gray-100 flex flex-col md:w-1/2">
        <div class="subcard-header-score rounded-xl p-3 text-center text-base sm:text-lg font-bold">
            Nilai Post Test Tertinggi
        </div>
        <div class="p-3 pt-6 flex justify-around space-x-2">
            @foreach($topThreePostTest as $rank => $item)
            @if($item)
            @php
            $rankOrderClass = $rank === 2 ? 'order-1' : ($rank === 1 ? 'order-2' : 'order-3');
            $scoreClass = $rank === 1 ? 'score-rank-1' : ($rank === 2 ? 'score-rank-2' : 'score-rank-3');
            $nameDisplay = explode(' ', $item['name'])[0] ?? 'User';
            @endphp
            <div class="flex flex-col items-center w-1/3 text-center p-2 {{ $rankOrderClass }}">
                <i data-lucide="trophy" class="w-8 h-8 text-brand mb-1"></i>
                <p class="font-medium text-xs text-gray-700">{{ $nameDisplay }}</p>
                <div class="text-[0.65rem] font-medium text-gray-500">
                    {{ $item['score'] }}
                </div>
                <div class="{{ $scoreClass }} score-box font-extrabold mt-3">
                    {{ $rank }}
                </div>
            </div>
            @endif
            @endforeach
        </div>

        @if($currentUserPostTest)
        <div class="text-sm font-semibold text-gray-500 mt-4 mb-2 border-t pt-3 px-3">
            Peringkat Anda
        </div>
        <div
            class="highlight-you rounded-lg flex justify-between items-center px-3 py-2 text-gray-800 border border-accent/30">
            <div class="flex items-center space-x-4">
                <span class="text-3xl font-extrabold text-accent w-10 text-center">
                    {{ $currentUserPostTest['rank'] }}
                </span>
                <div class="text-left">
                    <p class="font-bold text-sm text-accent">{{ $currentUserPostTest['name'] }}</p>
                    <p class="text-xs text-gray-600">{{ $position }}</p>
                </div>
            </div>
            <span class="text-sm font-bold text-gray-700">
                {{ $currentUserPostTest['score'] }}.0
            </span>
        </div>
        @endif
    </div>
</div>

@push('styles')
<style>
    .subcard-header-score {
        background-color: var(--color-green-prd, #DFF5E1);
        color: #1f2937;
    }

    .subcard-header-count {
        background-color: var(--color-red-prd, #FBE5E5);
        color: #1f2937;
    }

    .score-box {
        width: 100%;
        padding: 0.4rem 0.15rem;
        border-radius: 0.5rem;
        font-size: 1.25rem;
        font-weight: 800;
        text-align: center;
    }

    .score-rank-1 {
        background-color: var(--color-medal-gold, #FFC000);
        color: #333;
    }

    .score-rank-2 {
        background-color: var(--color-medal-silver, #C0C0C0);
        color: #333;
    }

    .score-rank-3 {
        background-color: var(--color-medal-bronze, #CD7F32);
        color: #fff;
    }

    .highlight-you {
        background-color: rgba(13, 148, 136, 0.10);
        font-weight: 700;
    }
</style>
@endpush