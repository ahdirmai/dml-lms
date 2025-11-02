{{-- resources/views/admin/pages/users/_form.blade.php --}}
@props(['action', 'method' => 'POST', 'user' => null, 'roles' => collect(), 'userRoles' => []])

<form action="{{ $action }}" method="POST" class="space-y-4">
    @csrf
    @if(in_array($method, ['PUT','PATCH','DELETE']))
    @method($method)
    @endif

    <div>
        <x-input-label for="name" :value="__('Name')" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
            :value="old('name', $user->name ?? '')" required autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="email" :value="__('Email')" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
            :value="old('email', $user->email ?? '')" required />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="password" :value="__('Password')" />
        <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" @if(!isset($user))
            required @endif />
        <p class="mt-1 text-xs text-gray-500">{{ isset($user) ? 'Kosongkan jika tidak ingin mengubah password.' :
            'Minimal 8 karakter.' }}</p>
        <x-input-error :messages="$errors->get('password')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="roles" :value="__('Roles')" />
        <select id="roles" name="roles[]" multiple
            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200">
            @foreach($roles as $r)
            <option value="{{ $r }}" @selected( in_array($r, old('roles', $userRoles ?? [])) )>
                {{ Str::headline($r) }}
            </option>
            @endforeach
        </select>
        <p class="mt-1 text-xs text-gray-500">
            Bila tidak memilih, sistem tetap menambahkan <b>student</b> secara default.
        </p>
        <x-input-error :messages="$errors->get('roles')" class="mt-2" />
    </div>

    <div class="pt-2 flex items-center gap-2">
        <x-primary-button type="submit">{{ __('Save') }}</x-primary-button>
        <a href="{{ route('admin.users.index') }}" class="px-4 py-2 rounded border">{{ __('Cancel') }}</a>
    </div>
</form>