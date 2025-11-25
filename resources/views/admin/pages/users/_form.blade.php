{{-- resources/views/admin/pages/users/_form.blade.php --}}
<form method="POST" action="{{ $action }}">
    @csrf
    @if(($method ?? 'POST') !== 'POST')
    @method($method)
    @endif

    <div class="space-y-5">

        <x-ui.form-field label="Name" for="name" :error="$errors->get('name')" required>
            <x-ui.input id="name" name="name" value="{{ old('name', $user->name ?? '') }}" />
        </x-ui.form-field>

        <x-ui.form-field label="Email" for="email" :error="$errors->get('email')" required>
            <x-ui.input id="email" type="email" name="email" value="{{ old('email', $user->email ?? '') }}" />
        </x-ui.form-field>

        <x-ui.form-field label="Password" for="password" helper="Biarkan kosong jika tidak ingin mengubah."
            :error="$errors->get('password')">
            <x-ui.input id="password" type="password" name="password" autocomplete="new-password" />
        </x-ui.form-field>

        <x-ui.form-field label="Confirm Password" for="password_confirmation"
            :error="$errors->get('password_confirmation')">
            <x-ui.input id="password_confirmation" type="password" name="password_confirmation" />
        </x-ui.form-field>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Multiple Roles --}}
            <x-ui.form-field label="Roles" for="roles" helper="Pilih satu atau lebih peran untuk user."
                :error="$errors->get('roles')">
                <x-ui.select id="roles" name="roles[]" multiple size="6">
                    @foreach($roles as $r)
                    <option value="{{ $r }}" @selected(in_array($r, old('roles', $userRoles ?? [])))>
                        {{ Str::headline($r) }}
                    </option>
                    @endforeach
                </x-ui.select>
            </x-ui.form-field>

            {{-- Active Role (opsional) --}}
            <x-ui.form-field label="Active Role" for="active_role"
                helper="Peran aktif mempengaruhi menu & akses saat login." :error="$errors->get('active_role')">
                @php
                $availableActive = old('roles', $userRoles ?? []);
                $currentActive = old('active_role', $user->active_role ?? null);
                @endphp
                <x-ui.select id="active_role" name="active_role">
                    <option value="">— None —</option>
                    @foreach($roles as $r)
                    @if(in_array($r, $availableActive))
                    <option value="{{ $r }}" @selected($currentActive===$r)>{{ Str::headline($r) }}</option>
                    @endif
                    @endforeach
                </x-ui.select>
            </x-ui.form-field>
        </div>

        {{-- Optional: status aktif, dsb. --}}
        {{-- <x-ui.form-field>
            <label class="inline-flex items-center gap-2">
                <x-ui.checkbox name="is_active" :checked="old('is_active', $user->is_active ?? true)" />
                <span class="text-sm text-dark">Active</span>
            </label>
        </x-ui.form-field> --}}

        <div class="flex items-center justify-end gap-2 pt-2">
            <x-ui.button as="a" href="{{ route('admin.users.index') }}" variant="subtle">Cancel</x-ui.button>
            <x-ui.button type="submit" variant="primary">
                {{ ($method ?? 'POST') === 'PUT' ? 'Update User' : 'Create User' }}
            </x-ui.button>
        </div>
    </div>
</form>
