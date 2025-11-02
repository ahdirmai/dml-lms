<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-dark leading-tight">Edit Category</h2>
            <x-ui.breadcrumbs :items="[
        ['label'=>'Dashboard','href'=>route('dashboard')],
        ['label'=>'Categories','href'=>route('admin.categories.index')],
        ['label'=>'Edit'],
      ]" />
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg">
                <div class="p-6">
                    @include('admin.pages.categories._form', [
                    'action' => route('admin.categories.update',$category),
                    'method' => 'PUT',
                    'category' => $category,
                    ])
                </div>
            </div>
        </div>
    </div>
</x-app-layout>