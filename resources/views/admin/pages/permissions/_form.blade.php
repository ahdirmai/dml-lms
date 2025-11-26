@props(['action', 'method' => 'POST', 'permission' => null, 'idPrefix' => ''])

<form method="POST" action="{{ $action }}" id="{{ $idPrefix }}permission-form">
    @csrf
    @if($method !== 'POST')
    @method($method)
    @endif

    <div class="space-y-5">
        <x-ui.form-field label="Permission Name" for="{{ $idPrefix }}name" required :error="$errors->get('name')">
            <x-ui.input id="{{ $idPrefix }}name" name="name" value="{{ old('name', $permission->name ?? '') }}"
                placeholder="e.g. edit user, view reports" autofocus />
        </x-ui.form-field>

        <x-ui.form-field label="Guard Name" for="{{ $idPrefix }}guard_name" helper="Usually 'web' or 'api'."
            :error="$errors->get('guard_name')">
            <x-ui.input id="{{ $idPrefix }}guard_name" name="guard_name"
                value="{{ old('guard_name', $permission->guard_name ?? 'web') }}" />
        </x-ui.form-field>

        <div class="flex items-center justify-end gap-2 pt-4 border-t border-gray-100 mt-4">
            <x-ui.button type="button" variant="subtle" data-close="true">Cancel</x-ui.button>
            <x-ui.button type="submit" variant="primary">
                Save Permission
            </x-ui.button>
        </div>
    </div>
</form>