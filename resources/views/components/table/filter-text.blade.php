@props(['model', 'placeholder' => 'Search…', 'type' => 'text', 'align' => 'left'])

@php
    $field = 'mt-1 block w-full min-w-[5rem] rounded-lg border-slate-300 px-2 py-1.5 text-xs shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white';
    $field .= $align === 'right' ? ' text-right' : '';
@endphp

<input wire:model.live.debounce.400ms="{{ $model }}" type="{{ $type }}" placeholder="{{ $placeholder }}" {{ $attributes->merge(['class' => $field]) }}>
