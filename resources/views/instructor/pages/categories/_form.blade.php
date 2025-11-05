<form method="POST" action="{{ $action }}">
    @csrf
    @if(($method ?? 'POST') !== 'POST') @method($method) @endif

    <div class="space-y-5">
        <x-ui.form-field label="Name" for="name" required :error="$errors->get('name')">
            <x-ui.input id="name" name="name" value="{{ old('name', $category->name ?? '') }}" />
        </x-ui.form-field>

        <x-ui.form-field label="Slug" for="slug" helper="Opsional, otomatis dari Name jika dikosongkan."
            :error="$errors->get('slug')">
            <x-ui.input id="slug" name="slug" value="{{ old('slug', $category->slug ?? '') }}" />
        </x-ui.form-field>

        <x-ui.form-field label="Description" for="description" :error="$errors->get('description')">
            <textarea id="description" name="description"
                class="w-full rounded-xl border border-soft px-3 py-2 text-sm text-dark focus:ring-2 focus:ring-brand focus:border-brand">{{ old('description', $category->description ?? '') }}</textarea>
        </x-ui.form-field>

        <div class="flex items-center justify-end gap-2">
            <x-ui.button as="a" href="{{ route('admin.categories.index') }}" variant="subtle">Cancel</x-ui.button>
            <x-ui.button type="submit" variant="primary">{{ ($method ?? 'POST')==='PUT' ? 'Update' : 'Create' }}
            </x-ui.button>
        </div>
    </div>
</form>