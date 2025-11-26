@props([
'variant' => 'primary', // primary|secondary|outline|danger|subtle|link
'size' => 'md',
'as' => 'button',
'href' => null,
'type' => 'button',
])

@php
$base = 'inline-flex items-center justify-center font-semibold rounded-lg transition focus:outline-none focus:ring-2
focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';
$sizes = [
'sm' => 'px-3 py-1.5 text-sm',
'md' => 'px-4 py-2 text-sm',
'lg' => 'px-5 py-2.5 text-base',
];
$variants = [
'primary' => 'bg-brand text-white hover:brightness-95 focus:ring-brand',
'secondary' => 'bg-dark text-white hover:brightness-95 focus:ring-dark',
'outline' => 'border border-dark text-dark hover:bg-soft focus:ring-dark',
'danger' => 'bg-danger text-white hover:brightness-95 focus:ring-danger',
'subtle' => 'bg-soft text-dark hover:brightness-95 focus:ring-brand',
'link' => 'text-brand hover:underline focus:ring-brand',
'success' => 'bg-emerald-600 text-white hover:brightness-95 focus:ring-emerald-600',
'ghost' => 'bg-transparent text-gray-500 hover:text-dark hover:bg-gray-100 focus:ring-gray-200',
];
$classes = "$base {$sizes[$size]} {$variants[$variant]}";
@endphp

@if($as === 'a')
<a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
@else
<button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</button>
@endif