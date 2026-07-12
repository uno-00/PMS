@props(['model', 'placeholder' => 'All'])

@php
    $field = 'mt-1 block w-full min-w-[5rem] rounded-lg border-slate-300 px-2 py-1.5 text-xs shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white';
@endphp

<select wire:model.live="{{ $model }}" {{ $attributes->merge(['class' => $field]) }}>
    <option value="">{{ $placeholder }}</option>
    {{ $slot }}
</select>
