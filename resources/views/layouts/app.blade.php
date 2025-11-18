{{-- resources/views/layouts/app.blade.php --}}
@props(['title' => config('app.name', 'LearnFlow')])

<!DOCTYPE html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>

<body class="bg-soft min-h-screen antialiased font-sans">
    @php
    /** @var \App\Models\User|null $me */
    $me = auth()->user();
    $roleNames = $me?->getRoleNames() ?? collect();
    $activeRole = $me?->active_role ?? $roleNames->first();

    $sidebarItems = [];

    /**
    * Helper kecil untuk menambahkan banyak item sekaligus.
    * @param array $target
    * @param array $items
    */
    $pushMany = function (&$target, array $items) {
    foreach ($items as $it) {
    $target[] = $it;
    }
    };

    if ($activeRole === 'student') {
    $pushMany($sidebarItems, [
    [
    'group' => null,
    'label' => 'Dashboard',
    'icon' => 'home',
    'href' => route('user.dashboard'),
    'active' => request()->routeIs('user.dashboard'),
    ],[
    'group' => null,
    'label' => 'My Courses',
    'icon' => 'book',
    'href' => route('user.courses.index'),
    'active' => request()->routeIs('user.courses.*'),
    ],
    ]);
    }

    if ($activeRole === 'admin') {
    $pushMany($sidebarItems, [
    // Group: null
    [
    'group' => null,
    'label' => 'Dashboard',
    'icon' => 'home',
    'href' => route('admin.dashboard'),
    'active' => request()->routeIs('admin.dashboard'),
    ],

    // Group: Course Management
    [
    'group' => 'Course Management',
    'label' => 'Courses',
    'icon' => 'book',
    'href' => route('admin.courses.index'),
    'active' => request()->routeIs('admin.courses.*'),
    ],
    [
    'group' => 'Course Management',
    'label' => 'Categories',
    'icon' => 'folder',
    'href' => route('admin.categories.index'),
    'active' => request()->routeIs('admin.categories.*'),
    ],
    [
    'group' => 'Course Management',
    'label' => 'Tags',
    'icon' => 'tag',
    'href' => route('admin.tags.index'),
    'active' => request()->routeIs('admin.tags.*'),
    ],

    // Group: Master Data
    [
    'group' => 'Master Data',
    'label' => 'User Management',
    'icon' => 'users',
    'href' => route('admin.users.index'),
    'active' => request()->routeIs('admin.users.*'),
    ],
    [
    'group' => 'Master Data',
    'label' => 'Role Management',
    'icon' => 'shield-check',
    'href' => route('admin.roles.index'),
    'active' => request()->routeIs('admin.roles.*'),
    ],
    [
    'group' => 'Master Data',
    'label' => 'Permission Management',
    'icon' => 'key-square',
    'href' => route('admin.permissions.index'),
    'active' => request()->routeIs('admin.permissions.*'),
    ],
    ]);
    }

    if ($activeRole === 'instructor') {
    $pushMany($sidebarItems, [
    [
    'group' => null,
    'label' => 'Dashboard',
    'icon' => 'home',
    'href' => route('instructor.dashboard'),
    'active' => request()->routeIs('instructor.dashboard'),
    ],
    [
    'group' => 'Course Management',
    'label' => 'Courses',
    'icon' => 'book',
    'href' => route('instructor.courses.index'),
    'active' => request()->routeIs('instructor.courses.*'),
    ],
    [
    'group' => 'Course Management',
    'label' => 'Categories',
    'icon' => 'folder',
    'href' => route('instructor.categories.index'),
    'active' => request()->routeIs('instructor.categories.*'),
    ],
    [
    'group' => 'Course Management',
    'label' => 'Tags',
    'icon' => 'tag',
    'href' => route('instructor.tags.index'),
    'active' => request()->routeIs('instructor.tags.*'),
    ],
    ]);
    }
    @endphp

    <div class="relative min-h-screen lg:flex">
        {{-- Sidebar --}}
        <x-ui.sidebar :brand="config('app.name')" :items="$sidebarItems" />

        {{-- Backdrop untuk Mobile --}}
        <div id="sidebar-backdrop" class="hidden fixed inset-0 bg-black/50 z-30 lg:hidden"></div>

        {{-- Konten Utama --}}
        <div class="flex-1 lg:ml-64">
            <main class="p-6 lg:p-8">
                {{-- Topbar (Header akan disembunyikan di mobile) --}}
                <x-ui.topbar :avatar="auth()->user()->avatar_url ?? null" :header="$header" />

                {{-- Header Terpisah (Hanya tampil di mobile) --}}
                @isset($header)
                <h2 class="text-xl font-semibold text-dark mb-6 hidden">
                    {{ $header }}
                </h2>
                @endisset

                {{-- Slot Konten Halaman --}}
                <div class="space-y-8">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    @stack('scripts')

    {{-- JS untuk Sidebar Toggle --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('main-sidebar');
            const openBtn = document.getElementById('open-sidebar');
            const closeBtn = document.getElementById('close-sidebar');
            const backdrop = document.getElementById('sidebar-backdrop');

            const openSidebar = () => {
                if (sidebar && backdrop) {
                    sidebar.classList.remove('-translate-x-full');
                    backdrop.classList.remove('hidden');
                }
            };

            const closeSidebar = () => {
                if (sidebar && backdrop) {
                    sidebar.classList.add('-translate-x-full');
                    backdrop.classList.add('hidden');
                }
            };

            if (openBtn) {
                openBtn.addEventListener('click', openSidebar);
            }
            if (closeBtn) {
                closeBtn.addEventListener('click', closeSidebar);
            }
            if (backdrop) {
                backdrop.addEventListener('click', closeSidebar);
            }
        });
    </script>
</body>

</html>