@props(['align' => 'left'])
<td {{ $attributes->merge(['class' => "px-3 py-2 text-{$align} text-sm text-gray-700 dark:text-gray-200"]) }}>
    {{ $slot }}
</td>