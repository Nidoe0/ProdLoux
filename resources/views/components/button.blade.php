@props(['type' => 'button', 'variant' => 'primary'])
@php
  $classes = $variant === 'primary'
    ? 'inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg shadow-sm hover:bg-primary-700 focus:outline-2 focus:outline-primary-700'
    : 'inline-flex items-center gap-2 px-4 py-2 bg-white text-neutral-900 border rounded-lg hover:bg-gray-50';
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
  {{ $slot }}
</button>
