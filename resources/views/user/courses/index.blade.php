<x-app-layout :title="'Kursus Saya'">
    <x-slot name="header">
        {{-- <h2 class="text-xl font-semibold leading-tight text-gray-800"> --}}
            {{ __('Kursus Saya') }}
            {{-- </h2> --}}
    </x-slot>

    {{-- Header bar: search + avatar (sederhana) --}}
    <div class="bg-white p-4 rounded-xl shadow mb-6 flex items-center justify-between">
        <div class="relative w-full max-w-md">
            <input type="text" placeholder="Cari di kursus saya..."
                class="pl-10 pr-4 py-2 border border-gray-200 rounded-xl w-full focus:outline-none focus:ring-2 focus:ring-brand/60 transition" />
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>

    </div>

    {{-- Tabs --}}
    @php
    $tabs = $tabs ?? [];
    $counts = $counts ?? [];
    $activeTab = $activeTab ?? 'in_progress';
    $tabUrl = fn($key) => route('user.courses.index', ['tab' => $key]);
    @endphp

    <div class="flex flex-wrap gap-x-6 gap-y-2 mb-6 border-b border-gray-200 pb-2">
        @foreach($tabs as $key => $label)
        @php $isActive = $activeTab === $key; @endphp
        <a href="{{ $tabUrl($key) }}"
            class="pb-2 font-semibold {{ $isActive ? 'text-brand border-b-2 border-brand' : 'text-gray-500 hover:text-brand' }}">
            {{ $label }} ({{ $counts[$key] ?? 0 }})
        </a>
        @endforeach
    </div>

    {{-- Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @forelse($courses as $c)
        @php
        $barColor = [
        'primary' => 'bg-brand',
        'success' => 'bg-emerald-500',
        'muted' => 'bg-gray-500',
        ][$c['cta_kind']] ?? 'bg-brand';

        $borderTop = [
        'in_progress' => 'border-brand',
        'completed' => 'border-emerald-500',
        'private' => 'border-gray-400',
        ][$c['status']] ?? 'border-brand';
        @endphp

        <div class="bg-white rounded-2xl shadow hover:shadow-lg transition overflow-hidden border-t-8 {{ $borderTop }}">
            <img src="{{ $c['thumbnail'] }}" alt="Thumbnail"
                class="w-full h-40 object-cover {{ $c['status']==='private' ? 'opacity-75' : '' }}">
            <div class="p-6">
                <span
                    class="text-[10px] tracking-wide font-bold px-2 py-1 rounded-full
                                 {{ $c['status']==='private' ? 'text-gray-600 bg-gray-200' :
                                    ($c['status']==='completed' ? 'text-emerald-600 bg-emerald-50' : 'text-brand bg-brand/10') }}">
                    {{ $c['category'] }}
                </span>

                <h3 class="mt-2 font-bold text-xl text-gray-800">{{ $c['title'] }}</h3>
                <p class="text-sm text-gray-500 mb-4">Oleh: {{ $c['instructor'] }}</p>

                <div class="flex justify-between items-center mb-2">
                    <span
                        class="text-sm font-semibold {{ $c['status']==='completed' ? 'text-emerald-600' : 'text-brand' }}">
                        {{ $c['progress'] }}% Selesai
                    </span>
                    <span class="text-xs text-gray-500">{{ $c['done'] }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3 mb-5">
                    <div class="{{ $barColor }} h-3 rounded-full" style="width: {{ $c['progress'] }}%"></div>
                </div>

                <div class="flex gap-2">
                    <a href="{{ route('user.courses.show', $c['id']) }}"
                        class="w-full inline-flex items-center justify-center px-4 py-2 rounded-xl font-bold text-white shadow
                                   {{ $c['cta_kind']==='success' ? 'bg-emerald-600 hover:bg-emerald-700'
                                                                : ($c['cta_kind']==='muted' ? 'bg-gray-600 hover:bg-gray-700'
                                                                                             : 'bg-brand hover:brightness-95') }}">
                        {{ $c['cta'] }}
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full">
            <div class="bg-white rounded-xl p-8 text-center border border-dashed">
                <p class="text-gray-600">Belum ada kursus pada kategori ini.</p>
            </div>
        </div>
        @endforelse
    </div>
</x-app-layout>
