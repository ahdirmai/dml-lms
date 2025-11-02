{{-- resources/views/admin/pages/roles/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-dark leading-tight">Create Role</h2>
            <x-ui.breadcrumbs :items="[
                ['label' => 'Dashboard', 'href' => route('dashboard')],
                ['label' => 'Roles', 'href' => route('admin.roles.index')],
                ['label' => 'Create'],
            ]" />
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
            <x-ui.alert variant="success" class="mb-4">{{ session('success') }}</x-ui.alert>
            @endif
            @if (session('error'))
            <x-ui.alert variant="danger" class="mb-4">{{ session('error') }}</x-ui.alert>
            @endif

            <div class="bg-white shadow sm:rounded-lg">
                <div class="p-6">
                    @include('admin.pages.roles._form', [
                    'action' => route('admin.roles.store'),
                    'method' => 'POST',
                    'role' => null,
                    'permissions' => $permissions, // array nama permission
                    'rolePermissions' => [], // kosong saat create
                    ])
                </div>
            </div>
        </div>
    </div>
</x-app-layout>