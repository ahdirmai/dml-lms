@props(['prev' => null, 'next' => null])

<div class="flex items-center justify-between mt-4">
    <div>
        @if($prev)
        <x-ui.button as="a" href="{{ $prev }}" variant="outline" size="sm">Previous</x-ui.button>
        @endif
    </div>
    <div>
        @if($next)
        <x-ui.button as="a" href="{{ $next }}" variant="outline" size="sm">Next</x-ui.button>
        @endif
    </div>
</div>