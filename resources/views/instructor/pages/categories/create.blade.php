<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-dark leading-tight">Create Category</h2>
            <x-ui.breadcrumbs :items="[
        ['label'=>'Dashboard','href'=>route('dashboard')],
        ['label'=>'Categories','href'=>route('instructor.categories.index')],
        ['label'=>'Create'],
      ]" />
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg">
                <div class="p-6">
                    @include('instructor.pages.categories._form', [
                    'action' => route('instructor.categories.store'),
                    'method' => 'POST',
                    'category' => null,
                    ])
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
