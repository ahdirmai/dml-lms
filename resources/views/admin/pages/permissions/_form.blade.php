@props(['action', 'method' => 'POST', 'permission' => null])

<form action="{{ $action }}" method="POST" class="space-y-4">
    @csrf
    @if(in_array($method, ['PUT','PATCH','DELETE'])) @method($method) @endif

    <div>
        <x-input-label for="name" :value="__('Permission Name')" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
            :value="old('name', $permission->name ?? '')" required />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div class="pt-2 flex items-center gap-2">
        <x-primary-button type="submit">{{ __('Save') }}</x-primary-button>
        <a href="{{ route('admin.permissions.index') }}" class="px-4 py-2 rounded border">{{ __('Cancel') }}</a>
    </div>
</form>