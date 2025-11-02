<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Edit Role</h2>
    </x-slot>
    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
                @include('admin.pages.roles._form', [
                'action' => route('admin.roles.update', $role),
                'method' => 'PUT',
                'role' => $role,
                'permissions' => $permissions,
                'rolePermissions' => $rolePermissions,
                ])
            </div>
        </div>
    </div>
</x-app-layout>