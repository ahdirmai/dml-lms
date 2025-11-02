{{-- resources/views/admin/pages/permissions/_form.blade.php --}}
<form method="POST" action="{{ $action }}">
    @csrf
    @if(($method ?? 'POST') !== 'POST')
    @method($method)
    @endif

    <div class="space-y-5">
        <x-ui.form-field label="Permission Name" for="name" required :error="$errors->get('name')">
            <x-ui.input id="name" name="name" value="{{ old('name', $permission->name ?? '') }}"
                placeholder="e.g. edit user, view reports" autofocus />
        </x-ui.form-field>

        <x-ui.form-field label="Guard Name" for="guard_name" helper="Usually 'web' or 'api'."
            :error="$errors->get('guard_name')">
            <x-ui.input id="guard_name" name="guard_name"
                value="{{ old('guard_name', $permission->guard_name ?? 'web') }}" />
        </x-ui.form-field>

        <div class="flex items-center justify-end gap-2 pt-2">
            <x-ui.button as="a" href="{{ route('admin.permissions.index') }}" variant="subtle">
                Cancel
            </x-ui.button>
            <x-ui.button type="submit" variant="primary">
                {{ ($method ?? 'POST') === 'PUT' ? 'Update Permission' : 'Create Permission' }}
            </x-ui.button>
        </div>
    </div>
</form>