@props(['label' => null, 'for' => null, 'helper' => null, 'error' => []])

<div>
    @if($label)
    <label for="{{ $for }}" class="block text-sm font-medium text-dark mb-1">
        {{ $label }}
        @if($attributes->get('required')) <span class="text-danger">*</span> @endif
    </label>
    @endif

    {{ $slot }}

    @if($helper)
    <p class="text-xs text-dark/60 mt-1">{{ $helper }}</p>
    @endif

    @if($error)
    <p class="text-xs text-danger mt-1">{{ $error[0] }}</p>
    @endif
</div>