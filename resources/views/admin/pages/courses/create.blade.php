<x-app-layout :title="'Create Course'">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-dark leading-tight">Create Course</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                @include('admin.pages.courses._form', [
                'action' => route('admin.courses.store'),
                'method' => 'POST',
                'course' => null,
                'submitLabel' => 'Save & Continue (Builder)',
                'categories'=>$categories
                ])
            </div>
        </div>
    </div>
</x-app-layout>