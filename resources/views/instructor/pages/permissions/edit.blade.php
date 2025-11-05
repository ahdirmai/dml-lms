<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-dark leading-tight">
                Edit Permission
            </h2>

            <x-ui.breadcrumbs :items="[
                ['label' => 'Dashboard', 'href' => route('dashboard')],
                ['label' => 'Permissions', 'href' => route('admin.permissions.index')],
                ['label' => 'Edit'],
            ]" />
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
            <x-ui.alert variant="success" class="mb-4">{{ session('success') }}</x-ui.alert>
            @endif
            @if (session('error'))
            <x-ui.alert variant="danger" class="mb-4">{{ session('error') }}</x-ui.alert>
            @endif

            <div class="bg-white shadow sm:rounded-lg">
                <div class="p-6">
                    @include('admin.pages.permissions._form', [
                    'action' => route('admin.permissions.update', $permission),
                    'method' => 'PUT',
                    'permission' => $permission,
                    ])
                </div>
            </div>
        </div>
    </div>
</x-app-layout>