{{-- resources/views/dashboard/index.blade.php --}}
<x-app-layout :title="$title ?? 'Dashboard'">
    {{-- Header --}}
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    @php
    $displayName = isset($userInfo['name']) && is_string($userInfo['name'])
    ? explode(' ', trim($userInfo['name']))[0]
    : (auth()->user()->name ?? 'Pengguna');
    @endphp

    <div id="content-area" class="space-y-8">
        {{-- HERO: sapaan & tagline --}}
        @include('user.dashboard.partials.hero', [
        'displayName' => $displayName,
        ])

        {{-- RINGKASAN PERFORMA --}}
        @include('user.dashboard.partials.performance-summary', [
        'performance' => $performance ?? [],
        ])

        {{-- LEADERBOARD + AI ASSISTANT --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            {{-- Leaderboards (2 kolom) --}}
            @include('user.dashboard.partials.leaderboards', [
            'leaderboardData' => $leaderboardData ?? [],
            'userInfo' => $userInfo ?? [],
            ])

            {{-- AI Assistant (1 kolom) --}}
            @include('user.dashboard.partials.ai-assistant')
        </div>

        {{-- SEMUA KELAS --}}
        @include('user.dashboard.partials.courses-section', [
        'courses' => $courses ?? [],
        ])
    </div>

    {{-- Komponen Global untuk Pre/Post Test (bisa dipakai di view lain juga) --}}
    <x-test.modals :courses="$courses" />

    @push('scripts')
    {{-- Lucide icons global --}}
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
                if (window.lucide) {
                    window.lucide.createIcons();
                }
            });
    </script>
    @endpush
</x-app-layout>