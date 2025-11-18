@props([
'type' => 'text',
'name' => null,
'value' => null,
'placeholder' => null,
])

<input type="{{ $type }}" name="{{ $name }}" value="{{ $value }}" placeholder="{{ $placeholder }}" {{
    $attributes->merge([
'class' => '
w-full
rounded-xl
border border-soft
bg-white
px-3 py-2.5
text-sm text-dark placeholder-dark/40
shadow-[0_1px_2px_rgba(0,0,0,0.03)]
transition-all

focus:ring-2 focus:ring-brand/60
focus:border-brand
focus:bg-white
focus:shadow-[0_0_0_2px_rgba(0,0,0,0.02)]

disabled:bg-soft disabled:text-dark/50 disabled:cursor-not-allowed
'
])
}}
>