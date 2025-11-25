<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-dark leading-tight">Edit Tag</h2>
            <x-ui.breadcrumbs :items="[
        ['label'=>'Dashboard','href'=>route('dashboard')],
        ['label'=>'Tags','href'=>route('instructor.tags.index')],
        ['label'=>'Edit'],
      ]" />
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg">
                <div class="p-6">
                    @include('instructor.pages.tags._form', [
                    'action' => route('admin.tags.update',$tag),
                    'method' => 'PUT',
                    'tag' => $tag,
                    ])
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
