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

{{-- 🔍 Search --}}
@case('search')
<svg class="{{ $class }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
</svg>
@break

{{-- 🌪️ Filter --}}
@case('filter')
<svg class="{{ $class }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
</svg>
@break

{{-- 🔄 Refresh --}}
@case('refresh')
<svg class="{{ $class }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
</svg>
@break

{{-- ➕ Plus --}}
@case('plus')
<svg class="{{ $class }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
</svg>
@break

{{-- ✏️ Pencil / Edit --}}
@case('pencil')
<svg class="{{ $class }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
</svg>
@break

{{-- 🗑️ Trash / Delete --}}
@case('trash')
<svg class="{{ $class }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
</svg>
@break

{{-- ❌ X / Close --}}
@case('x')
<svg class="{{ $class }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
</svg>
@break

{{-- 🕒 Clock --}}
@case('clock')
<svg class="{{ $class }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
</svg>
@break

{{-- ⚙️ Default / Dot --}}
@default
<svg class="{{ $class }}" fill="currentColor" viewBox="0 0 8 8">
    <circle cx="4" cy="4" r="4" />
</svg>
@endswitch