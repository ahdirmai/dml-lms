@props(['title' => config('app.name', 'LearnFlow')])

<!DOCTYPE html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
    {{--
    <link rel="preload" as="style" href="{{ asset('build/assets/app-DaDlh1KL.css') }}">
    <link rel="modulepreload" as="script" href="{{ asset('build/assets/app-DaDlh1KL.css') }}">
    <link rel="stylesheet" href="{{ asset('build/assets/app-DaDlh1KL.css') }}"> --}}
</head>

<body class="bg-soft min-h-screen antialiased">
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
    'active' => request()->routeIs('user.courses.index'),
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
    <div class="flex min-h-screen">
        <x-ui.sidebar :brand="config('app.name')" :items="$sidebarItems" />

        <main class="ml-64 flex-1 p-6 lg:p-8">
            <x-ui.topbar :avatar="auth()->user()->avatar_url ?? null" :header="$header" />



            <div class=" space-y-8">
                {{ $slot }}
            </div>
        </main>
    </div>
    @stack('scripts')
</body>

</html>
