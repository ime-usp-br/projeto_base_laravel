@props(['disabled' => false, 'name' => '', 'id' => null, 'type' => 'text'])

@php

$computedId = $id ?? $name ?: Str::random(8);

$computedName = $name ?: $computedId;

$duskName = Str::slug($computedName);
@endphp

<input
    id="{{ $computedId }}"
    name="{{ $computedName }}"
    type="{{ $type }}"
    {{ $disabled ? 'disabled' : '' }}
    {!! $attributes->merge(['class' => 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm']) !!}
    dusk="text-input-{{ $duskName }}"
>