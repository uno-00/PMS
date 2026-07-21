@props(['model', 'placeholder' => 'Search…', 'type' => 'text', 'align' => 'left'])

@php
    $field = 'form-control-sm';
    $field .= $align === 'right' ? ' text-right' : '';
@endphp

<input wire:model.live.debounce.400ms="{{ $model }}" type="{{ $type }}" placeholder="{{ $placeholder }}" {{ $attributes->merge(['class' => $field]) }}>
