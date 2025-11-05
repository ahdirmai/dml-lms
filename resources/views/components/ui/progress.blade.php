@props(['value' => 0, 'color' => 'brand']) {{-- brand|accent|danger|dark|gray --}}

@php
$bar = match($color) {
'accent' => 'bg-accent',
'danger' => 'bg-danger',
'dark' => 'bg-dark',
'gray' => 'bg-gray-400',
default => 'bg-brand',
};
@endphp

<div class="w-full bg-soft rounded-full h-3 overflow-hidden">
    <div class="h-3 rounded-full {{ $bar }}" style="width: {{ (int) $value }}%"></div>
</div>