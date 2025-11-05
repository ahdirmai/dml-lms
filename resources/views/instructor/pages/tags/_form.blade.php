<form method="POST" action="{{ $action }}">
    @csrf
    @if(($method ?? 'POST') !== 'POST') @method($method) @endif

    <div class="space-y-5">
        <x-ui.form-field label="Name" for="name" required :error="$errors->get('name')">
            <x-ui.input id="name" name="name" value="{{ old('name', $tag->name ?? '') }}" />
        </x-ui.form-field>

        <x-ui.form-field label="Slug" for="slug" helper="Opsional, otomatis dari Name jika dikosongkan."
            :error="$errors->get('slug')">
            <x-ui.input id="slug" name="slug" value="{{ old('slug', $tag->slug ?? '') }}" />
        </x-ui.form-field>

        <div class="flex items-center justify-end gap-2">
            <x-ui.button as="a" href="{{ route('instructor.tags.index') }}" variant="subtle">Cancel</x-ui.button>
            <x-ui.button type="submit" variant="primary">{{ ($method ?? 'POST')==='PUT' ? 'Update' : 'Create' }}
            </x-ui.button>
        </div>
    </div>
</form>
