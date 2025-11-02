@props(['href' => '#', 'active' => false, 'icon' => 'dot'])

@php
$base = 'flex items-center px-3 py-2 rounded-xl font-semibold transition';
$classes = $active
? $base.' bg-brand text-white shadow'
: $base.' text-dark hover:bg-accent/10 hover:text-dark';
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
    <x-ui.icon :name="$icon" class="mr-3 w-5 h-5 {{ $active ? 'text-white' : 'text-dark/60' }}" />
    <span>{{ $slot }}</span>
</a>