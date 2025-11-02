@props(['border' => null, 'hover' => true, 'pad' => 'p-6', 'rounded' => 'rounded-2xl'])

@php
$classes = "bg-white $pad $rounded shadow-md";
$classes .= $hover ? ' transition hover:shadow-custom-soft duration-300' : '';
if ($border) $classes .= " border-l-4 border-{$border}";
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</div>