@props(['color' => 'dark']) {{-- dark|brand|accent|danger|gray|green --}}

@php
$map = [
'gray' => 'bg-soft text-dark border-soft',
'dark' => 'bg-dark/10 text-dark border-dark/20',
'brand' => 'bg-brand/10 text-brand border-brand/20',
'accent' => 'bg-accent/10 text-accent border-accent/20',
'danger' => 'bg-danger/10 text-danger border-danger/20',
'green' => 'bg-green-100 text-green-800 border-green-200',
];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border
    {$map[$color]}"]) }}>
    {{ $slot }}
</span>