@props(['title' => 'No data', 'subtitle' => null])

<div class="text-center py-8 text-dark/70">
    <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-10 w-10 text-dark/40" fill="none" viewBox="0 0 24 24"
        stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M9 13h6m-3-3v6m9 4H3a2 2 0 01-2-2V5a2 2 0 012-2h18a2 2 0 012 2v14a2 2 0 01-2 2z" />
    </svg>
    <h3 class="mt-2 font-semibold text-dark">{{ $title }}</h3>
    @if($subtitle)
    <p class="text-sm text-dark/60">{{ $subtitle }}</p>
    @endif
    <div class="mt-4">
        {{ $slot }}
    </div>
</div>