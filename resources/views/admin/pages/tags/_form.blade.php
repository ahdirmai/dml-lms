@props(['action', 'method' => 'POST', 'tag' => null, 'idPrefix' => ''])

<form method="POST" action="{{ $action }}" id="{{ $idPrefix }}tag-form">
    @csrf
    @if($method !== 'POST') @method($method) @endif

    <div class="space-y-5">
        <x-ui.form-field label="Name" for="{{ $idPrefix }}name" required :error="$errors->get('name')">
            <x-ui.input id="{{ $idPrefix }}name" name="name" value="{{ old('name', $tag->name ?? '') }}" />
        </x-ui.form-field>

        <x-ui.form-field label="Slug" for="{{ $idPrefix }}slug" helper="Opsional, otomatis dari Name jika dikosongkan."
            :error="$errors->get('slug')">
            <x-ui.input id="{{ $idPrefix }}slug" name="slug" value="{{ old('slug', $tag->slug ?? '') }}" />
        </x-ui.form-field>

        <div class="flex items-center justify-end gap-2 pt-4 border-t border-gray-100 mt-4">
            <x-ui.button type="button" variant="subtle" data-close="true">Cancel</x-ui.button>
            <x-ui.button type="submit" variant="primary">
                Save Tag
            </x-ui.button>
        </div>
    </div>
</form>