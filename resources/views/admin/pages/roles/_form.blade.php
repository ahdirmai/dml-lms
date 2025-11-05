{{-- resources/views/admin/pages/roles/_form.blade.php --}}
<form method="POST" action="{{ $action }}">
    @csrf
    @if(($method ?? 'POST') !== 'POST')
    @method($method)
    @endif

    <div class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-ui.form-field label="Role Name" for="name" required :error="$errors->get('name')">
                <x-ui.input id="name" name="name" value="{{ old('name', $role->name ?? '') }}"
                    placeholder="e.g. admin, instructor, student" autofocus />
            </x-ui.form-field>

            <x-ui.form-field label="Guard Name" for="guard_name" helper="Biasanya 'web'."
                :error="$errors->get('guard_name')">
                <x-ui.input id="guard_name" name="guard_name"
                    value="{{ old('guard_name', $role->guard_name ?? 'web') }}" />
            </x-ui.form-field>
        </div>

        <x-ui.form-field label="Permissions" for="permissions" helper="Pilih satu atau lebih permission untuk role ini."
            :error="$errors->get('permissions')">
            <x-ui.select id="permissions" name="permissions[]" multiple size="10">
                @foreach($permissions as $p)
                <option value="{{ $p }}" @selected(in_array($p, old('permissions', $rolePermissions ?? [])))>
                    {{ $p }}
                </option>
                @endforeach
            </x-ui.select>
        </x-ui.form-field>

        <div class="flex items-center justify-end gap-2 pt-2">
            <x-ui.button as="a" href="{{ route('admin.roles.index') }}" variant="subtle">Cancel</x-ui.button>
            <x-ui.button type="submit" variant="primary">
                {{ ($method ?? 'POST') === 'PUT' ? 'Update Role' : 'Create Role' }}
            </x-ui.button>
        </div>
    </div>
</form>