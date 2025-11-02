@props(['name' => 'dot', 'class' => 'w-4 h-4'])

@switch($name)
@case('home')
<svg class="{{ $class }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l9-9 9 9M5 10v10h4m6 0h4V10" />
</svg>
@break
@case('users')
<svg class="{{ $class }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M17 20v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8zM23 20v-2a4 4 0 00-3-3.87" />
</svg>
@break
@case('shield-check')
<svg class="{{ $class }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10zM9 12l2 2 4-4" />
</svg>
@break
@case('key-square')
<svg class="{{ $class }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M3 7a4 4 0 014-4h10a4 4 0 014 4v10a4 4 0 01-4 4H7a4 4 0 01-4-4V7z" />
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M14 10a2 2 0 11-4 0 2 2 0 014 0zM12 12l4 4" />
</svg>
@break
@default
<svg class="{{ $class }}" viewBox="0 0 8 8">
    <circle cx="4" cy="4" r="4" />
</svg>
@endswitch