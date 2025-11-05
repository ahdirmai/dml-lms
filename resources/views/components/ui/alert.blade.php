@props(['variant' => 'info', 'title' => null]) {{-- info|success|warning|danger -> map ke accent/brand/dark/danger --}}

@php
$map = [
'info' => 'bg-accent/10 text-accent border-accent/20',
'success' => 'bg-brand/10 text-brand border-brand/20',
'warning' => 'bg-dark/10 text-dark border-dark/20',
'danger' => 'bg-danger/10 text-danger border-danger/20',
];
@endphp

<div {{ $attributes->merge(['class' => "border rounded-lg p-4 {$map[$variant]}"]) }}>
    @if($title)
    <div class="font-semibold mb-1">{{ $title }}</div>
    @endif
    <div class="text-sm">{{ $slot }}</div>
</div>