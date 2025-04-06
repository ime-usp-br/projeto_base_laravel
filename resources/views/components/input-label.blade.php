@props(['value', 'for'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-gray-700']) }} dusk="input-label-{{ $for }}">
    {{ $value ?? $slot }}
</label>