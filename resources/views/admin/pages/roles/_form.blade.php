@props(['action', 'method' => 'POST', 'role' => null, 'permissions' => [], 'rolePermissions' => [], 'idPrefix' => ''])

<form method="POST" action="{{ $action }}" id="{{ $idPrefix }}role-form">
    @csrf
    @if($method !== 'POST')
    @method($method)
    @endif

    <div class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-ui.form-field label="Role Name" for="{{ $idPrefix }}name" required :error="$errors->get('name')">
                <x-ui.input id="{{ $idPrefix }}name" name="name" value="{{ old('name', $role->name ?? '') }}"
                    placeholder="e.g. admin, instructor, student" autofocus />
            </x-ui.form-field>

            <x-ui.form-field label="Guard Name" for="{{ $idPrefix }}guard_name" helper="Biasanya 'web'."
                :error="$errors->get('guard_name')">
                <x-ui.input id="{{ $idPrefix }}guard_name" name="guard_name"
                    value="{{ old('guard_name', $role->guard_name ?? 'web') }}" />
            </x-ui.form-field>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Permissions</label>
            <p class="text-xs text-gray-500 mb-2">Pilih satu atau lebih permission untuk role ini.</p>
            
            {{-- 1. Real Hidden Select (Stores Data) --}}
            <select name="permissions[]" id="{{ $idPrefix }}real-permissions" multiple class="hidden">
                @foreach($permissions as $p)
                <option value="{{ $p }}" @selected(in_array($p, old('permissions', $rolePermissions ?? [])))>
                    {{ $p }}
                </option>
                @endforeach
            </select>

            {{-- 2. UI Picker (User Interaction) --}}
            <div class="relative">
                <select id="{{ $idPrefix }}ui-permission-picker" 
                    class="w-full p-3 border border-gray-300 rounded-xl focus:ring-primary-accent focus:border-primary-accent permission-picker">
                    <option value="">-- Pilih Permission --</option>
                    @foreach($permissions as $p)
                        <option value="{{ $p }}">{{ $p }}</option>
                    @endforeach
                </select>
            </div>
            
            {{-- 3. Selected Badges Container --}}
            <div class="flex flex-wrap gap-2 mt-3 p-3 bg-gray-50 rounded-xl border border-gray-100 min-h-[60px]" id="{{ $idPrefix }}selected-permissions-container">
                {{-- Badges injected via JS --}}
                <span class="text-sm text-gray-400 italic self-center empty-text">Belum ada permission dipilih</span>
            </div>
            @error('permissions')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-end gap-2 pt-4 border-t border-gray-100 mt-4">
            <x-ui.button type="button" variant="subtle" data-close="true">Cancel</x-ui.button>
            <x-ui.button type="submit" variant="primary">
                Save Role
            </x-ui.button>
        </div>
    </div>
</form>