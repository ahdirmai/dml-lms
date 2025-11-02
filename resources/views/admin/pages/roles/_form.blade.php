@props(['action', 'method' => 'POST', 'role' => null, 'permissions' => collect(), 'rolePermissions' => []])

<form action="{{ $action }}" method="POST" class="space-y-4">
    @csrf
    @if(in_array($method, ['PUT','PATCH','DELETE'])) @method($method) @endif

    <div>
        <x-input-label for="name" :value="__('Role Name')" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
            :value="old('name', $role->name ?? '')" required />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label :value="__('Permissions')" />
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2 mt-2">
            @foreach($permissions as $perm)
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" name="permissions[]" value="{{ $perm }}" @checked( in_array($perm,
                    old('permissions', $rolePermissions ?? [])) ) class="rounded border-gray-300 dark:border-gray-600">
                <span>{{ $perm }}</span>
            </label>
            @endforeach
        </div>
        <x-input-error :messages="$errors->get('permissions')" class="mt-2" />
    </div>

    <div class="pt-2 flex items-center gap-2">
        <x-primary-button type="submit">{{ __('Save') }}</x-primary-button>
        <a href="{{ route('admin.roles.index') }}" class="px-4 py-2 rounded border">{{ __('Cancel') }}</a>
    </div>
</form>