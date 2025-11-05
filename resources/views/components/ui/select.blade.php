@props(['name' => null])

<select name="{{ $name }}" {{ $attributes->merge([
    'class' => 'w-full rounded-xl border border-soft px-3 py-2 text-sm text-dark bg-white
    focus:ring-2 focus:ring-brand focus:border-brand transition'
    ]) }}
    >
    {{ $slot }}
</select>