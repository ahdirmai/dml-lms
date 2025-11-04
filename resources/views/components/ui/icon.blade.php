@props(['name' => 'dot', 'class' => 'w-4 h-4'])

@switch($name)
{{-- 🏠 Dashboard --}}
@case('home')
<svg class="{{ $class }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l9-9 9 9M5 10v10h4m6 0h4V10" />
</svg>
@break

{{-- 👥 User Management --}}
@case('users')
<svg class="{{ $class }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M17 20v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8zM23 20v-2a4 4 0 00-3-3.87" />
</svg>
@break

{{-- 🛡️ Role Management --}}
@case('shield-check')
<svg class="{{ $class }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10zM9 12l2 2 4-4" />
</svg>
@break

{{-- 🔑 Permission Management --}}
@case('key-square')
<svg class="{{ $class }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M3 7a4 4 0 014-4h10a4 4 0 014 4v10a4 4 0 01-4 4H7a4 4 0-01-4-4V7z" />
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M14 10a2 2 0 11-4 0 2 2 0 014 0zM12 12l4 4" />
</svg>
@break

{{-- 📂 Categories --}}
@case('folder')
<svg class="{{ $class }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h5l2 2h11v9a2 2 0 01-2 2H3V7z" />
</svg>
@break

{{-- 🏷️ Tags --}}
@case('tag')
<svg class="{{ $class }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h10l4 5-4 5H7l-4-5 4-5z" />
</svg>
@break

{{-- 📘 Courses --}}
@case('book')
<svg class="{{ $class }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M4 19.5A2.5 2.5 0 006.5 22H20m0 0V2H6.5A2.5 2.5 0 004 4.5v15z" />
</svg>
@break

{{-- ⚙️ Default / Dot --}}
@default
<svg class="{{ $class }}" fill="currentColor" viewBox="0 0 8 8">
    <circle cx="4" cy="4" r="4" />
</svg>
@endswitch