@props([
'name' => 'modal',
'maxWidth' => 'lg', // sm|md|lg|xl|2xl
'show' => false,
])

@php
$maxMap = [
'sm' => 'sm:max-w-sm',
'md' => 'sm:max-w-md',
'lg' => 'sm:max-w-lg',
'xl' => 'sm:max-w-xl',
'2xl'=> 'sm:max-w-2xl',
];
@endphp

<div x-data="{ open: @js($show) }" x-on:open-{{ $name }}.window="open = true" x-on:close-{{ $name
    }}.window="open = false">
    <div x-show="open" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
        <div x-show="open" x-transition.opacity class="fixed inset-0 bg-black/40"></div>

        <div x-show="open" x-transition
            class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full {{ $maxMap[$maxWidth] }} mx-auto overflow-hidden">
            {{ $slot }}
        </div>
    </div>
</div>