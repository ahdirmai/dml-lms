@props(['align' => 'left'])
<th {{ $attributes->merge(['class' => "px-3 py-2 text-{$align} text-xs font-semibold text-gray-600 dark:text-gray-300
    uppercase"]) }}>
    {{ $slot }}
</th>