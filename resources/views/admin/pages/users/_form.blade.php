{{-- resources/views/admin/pages/users/_form.blade.php --}}
<form method="POST" action="{{ $action }}">
    @csrf
    @if(($method ?? 'POST') !== 'POST')
    @method($method)
    @endif

    <div class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <x-ui.form-field label="Name" for="name" :error="$errors->get('name')" required>
                <x-ui.input id="name" name="name" value="{{ old('name', $user->name ?? '') }}" placeholder="John Doe" />
            </x-ui.form-field>

            <x-ui.form-field label="Email" for="email" :error="$errors->get('email')" required>
                <x-ui.input id="email" type="email" name="email" value="{{ old('email', $user->email ?? '') }}" placeholder="john@example.com" />
            </x-ui.form-field>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <x-ui.form-field label="Password" for="password" helper="Leave blank to keep current password."
                :error="$errors->get('password')">
                <x-ui.input id="password" type="password" name="password" autocomplete="new-password" placeholder="••••••••" />
            </x-ui.form-field>

            <x-ui.form-field label="Confirm Password" for="password_confirmation"
                :error="$errors->get('password_confirmation')">
                <x-ui.input id="password_confirmation" type="password" name="password_confirmation" placeholder="••••••••" />
            </x-ui.form-field>
        </div>

        <div class="border-t border-gray-100 pt-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                {{-- Multiple Roles (Badge Picker) --}}
                <x-ui.form-field label="Roles" for="roles_picker" helper="Select one or more roles." :error="$errors->get('roles')">
                    
                    {{-- Real Hidden Select --}}
                    <select name="roles[]" id="roles" multiple class="hidden">
                        @foreach($roles as $r)
                        <option value="{{ $r }}" @selected(in_array($r, old('roles', $userRoles ?? [])))>
                            {{ Str::headline($r) }}
                        </option>
                        @endforeach
                    </select>

                    {{-- UI Picker --}}
                    <x-ui.select id="roles_picker" class="w-full mb-2">
                        <option value="">-- Select Role --</option>
                        @foreach($roles as $r)
                        <option value="{{ $r }}">{{ Str::headline($r) }}</option>
                        @endforeach
                    </x-ui.select>

                    {{-- Badges Container --}}
                    <div id="roles_badges" class="flex flex-wrap gap-2 p-3 bg-gray-50 rounded-xl border border-gray-100 min-h-[50px]">
                        <span class="text-sm text-gray-400 italic self-center empty-text">No roles selected</span>
                    </div>
                </x-ui.form-field>
    
                {{-- Active Role --}}
                <x-ui.form-field label="Active Role" for="active_role"
                    helper="Determines the dashboard view." :error="$errors->get('active_role')">
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
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
            <button type="button" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand" data-close="true">
                Cancel
            </button>
            <x-ui.button type="submit" variant="primary">
                {{ ($method ?? 'POST') === 'PUT' ? 'Save Changes' : 'Create User' }}
            </x-ui.button>
        </div>
    </div>
</form>
