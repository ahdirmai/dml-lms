@props(['title' => null, 'name' => 'modal'])

<div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">{{ $title }}</h3>
    <button x-on:click="$dispatch('close-{{ $name }}')" class="p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700">
        ✕
    </button>
</div>