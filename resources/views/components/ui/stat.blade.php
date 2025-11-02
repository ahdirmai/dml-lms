@props(['label' => 'Label', 'value' => '0', 'suffix' => null, 'border' => 'brand']) {{-- brand|accent|danger|dark|gray
--}}

@php
$borderMap = [
'brand' => 'border-brand',
'accent' => 'border-accent',
'danger' => 'border-danger',
'dark' => 'border-dark',
'gray' => 'border-gray-400',
];
$textMap = [
'brand' => 'text-brand',
'accent' => 'text-accent',
'danger' => 'text-danger',
'dark' => 'text-dark',
'gray' => 'text-gray-500',
];
@endphp

<x-ui.card :hover="false" pad="p-6" :class="'border-l-4 '.$borderMap[$border]">
    <p class="text-sm text-dark/70">{{ $label }}</p>
    <p class="text-4xl font-extrabold mt-1 {{ $textMap[$border] }}">
        {{ $value }}
        @if($suffix)
        <span class="text-lg font-semibold ml-1 text-dark/60">{{ $suffix }}</span>
        @endif
    </p>
</x-ui.card>