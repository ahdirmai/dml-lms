@props(['title' => config('app.name', 'LearnFlow')])

<!DOCTYPE html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{--
    <link rel="preload" as="style" href="{{ asset('build/assets/app-C_ju9-rv.css') }}">
    <link rel="modulepreload" as="script" href="{{ asset('build/assets/app-CE7cBmaw.js') }}">
    <link rel="stylesheet" href="{{ asset('build/assets/app-C_ju9-rv.css') }}"> --}}
</head>

<body class="bg-soft min-h-screen antialiased">
    @php
    /** @var \App\Models\User|null $me */
    $me = auth()->user();
    $roleNames = $me?->getRoleNames() ?? collect();
    $activeRole = $me?->active_role ?? $roleNames->first();

    $sidebarItems = [
    [
    'group' => null,
    'label' => 'Dashboard',
    'icon' => 'home',
    'href' => route('dashboard'),
    'active' => request()->routeIs('dashboard'),
    ]
    ];

    if ($activeRole === 'admin') {
    $sidebarItems = array_merge($sidebarItems, [[
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
    $sidebarItems = array_merge($sidebarItems, [[
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
</body>

</html>
