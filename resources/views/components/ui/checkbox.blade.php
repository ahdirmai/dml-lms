@props(['checked' => false, 'value' => '1', 'name' => null, 'id' => null])

<input type="checkbox" {{ $checked ? 'checked' : '' }} value="{{ $value }}" name="{{ $name }}" id="{{ $id }}" {{
    $attributes->merge(['class' => 'rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500'])
}}>