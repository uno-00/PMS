@props(['model', 'placeholder' => 'All'])

<select wire:model.live="{{ $model }}" {{ $attributes->merge(['class' => 'form-control-sm']) }}>
    <option value="">{{ $placeholder }}</option>
    {{ $slot }}
</select>
