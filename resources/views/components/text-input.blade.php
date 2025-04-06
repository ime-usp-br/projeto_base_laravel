@props(['disabled' => false, 'name' => '', 'id' => null])

@php
$computedId = $id ?? $name ?? Str::random(8);
$duskName = $name ?: $computedId;
@endphp

<input id="{{ $computedId }}" name="{{ $name ?: $computedId }}" {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm']) !!} dusk="text-input-{{ $duskName }}">