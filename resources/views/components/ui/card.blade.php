@props([
'border' => null,
'hover' => true,
'pad' => 'p-6',
'rounded' => 'rounded-2xl',
// Accent gradient for bottom bar
'accent' => 'from-sky-400 to-blue-600',
])

@php
$classes = "relative overflow-hidden bg-white $pad $rounded shadow-md";
$classes .= $hover ? ' transition hover:shadow-custom-soft duration-300' : '';
if ($border) $classes .= " border-l-4 border-{$border}";
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
    <span class="pointer-events-none absolute inset-x-0 bottom-0 h-1 bg-gradient-to-r {{ $accent }}"></span>

</div>