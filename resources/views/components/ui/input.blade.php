@props(['type' => 'text', 'name' => null, 'value' => null, 'placeholder' => null])

<input type="{{ $type }}" name="{{ $name }}" value="{{ $value }}" placeholder="{{ $placeholder }}" {{
    $attributes->merge([
'class' => 'w-full rounded-xl border border-soft px-3 py-2 text-sm text-dark placeholder-dark/40
focus:ring-2 focus:ring-brand focus:border-brand transition bg-white'
]) }}
>