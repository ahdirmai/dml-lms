@props(['items' => []])

<nav class="text-sm text-dark/70">
    <ol class="flex items-center space-x-1">
        @foreach($items as $index => $item)
        <li>
            @if(isset($item['href']))
            <a href="{{ $item['href'] }}" class="hover:text-brand">{{ $item['label'] }}</a>
            @else
            <span class="text-dark font-medium">{{ $item['label'] }}</span>
            @endif
            @if(!$loop->last)
            <span class="mx-1 text-dark/40">›</span>
            @endif
        </li>
        @endforeach
    </ol>
</nav>