@props([
    'action',
    'method' => 'POST',
    'category' => null,
    'idPrefix' => ''
])

<form method="POST" action="{{ $action }}">
    @csrf
    @if($method !== 'POST')
    @method($method)
    @endif

    <div class="space-y-5">
        <x-ui.form-field label="Name" for="{{ $idPrefix }}name" required :error="$errors->get('name')">
            <x-ui.input id="{{ $idPrefix }}name" name="name" value="{{ old('name', $category->name ?? '') }}" />
        </x-ui.form-field>

        <x-ui.form-field label="Slug" for="{{ $idPrefix }}slug" helper="Opsional, otomatis dari Name jika dikosongkan."
            :error="$errors->get('slug')">
            <x-ui.input id="{{ $idPrefix }}slug" name="slug" value="{{ old('slug', $category->slug ?? '') }}" />
        </x-ui.form-field>

        <x-ui.form-field label="Description" for="{{ $idPrefix }}description" :error="$errors->get('description')">
            <textarea id="{{ $idPrefix }}description" name="description"
                class="w-full rounded-xl border border-soft px-3 py-2 text-sm text-dark focus:ring-2 focus:ring-brand focus:border-brand">{{ old('description', $category->description ?? '') }}</textarea>
        </x-ui.form-field>

        <div class="flex items-center justify-end gap-2">
            <x-ui.button type="button" variant="subtle" data-close="true">Cancel</x-ui.button>
            <x-ui.button type="submit" variant="primary">{{ $method === 'PUT' ? 'Update' : 'Create' }}
            </x-ui.button>
        </div>
    </div>
</form>
