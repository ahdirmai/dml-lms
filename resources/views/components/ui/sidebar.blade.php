@props(['brand' => 'LearnFlow', 'items' => []])

<aside class="w-64 bg-white p-6 shadow-xl flex flex-col fixed h-full overflow-y-auto">
    <div class="flex items-center mb-8 mt-1">
        <svg class="w-8 h-8 mr-2 text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 14l9-5-9-5-9 5 9 5zM12 14v6M7.5 7.5L12 10.5l4.5-3" />
        </svg>
        <h1 class="text-lg font-bold text-dark">{{ $brand }}</h1>
    </div>

    <nav class="space-y-4 mb-6">
        @php $currentGroup = null; @endphp

        @foreach($items as $item)
        @if($item['group'] !== $currentGroup)
        @if($currentGroup !== null)
        <div class="border-t border-gray-100 my-2"></div>
        @endif
        @if($item['group'])
        <p class="text-xs uppercase font-semibold text-gray-400 mt-4 mb-2">
            {{ $item['group'] }}
        </p>
        @endif
        @php $currentGroup = $item['group']; @endphp
        @endif

        <x-ui.nav-item :href="$item['href']" :active="$item['active']" :icon="$item['icon']">
            {{ $item['label'] }}
        </x-ui.nav-item>
        @endforeach
    </nav>

    <div class="mt-auto pt-4 border-t border-soft">
        <p class="text-xs text-gray-400">v1.0 · {{ now()->format('M Y') }}</p>
    </div>
</aside>