@php

$slotContent = trim(strip_tags($slot->toHtml()));
$duskId = $attributes->get('id') ?: $attributes->get('name') ?: $slotContent;
$duskSelector = 'secondary-button-' . Str::slug(str_replace([' ', '_'], '-', $duskId), '-');
@endphp

<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150']) }} dusk="{{ $duskSelector }}">
    {{ $slot }}
</button>