@props(['value', 'for'])

@php

$id = $attributes->get('id', $for ?? Str::random(8));

$forAttr = $id;

$duskSelector = 'input-label-' . Str::slug($for ?? $id);
@endphp

<label for="{{ $forAttr }}" {{ $attributes->merge(['class' => 'block font-medium text-sm text-gray-700']) }} dusk="{{ $duskSelector }}">
    {{ $value ?? $slot }}
</label>