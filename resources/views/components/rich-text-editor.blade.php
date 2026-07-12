@props(['model', 'placeholder' => ''])

<div
    wire:ignore
    data-rich-text-model="{{ $model }}"
    x-data="richTextEditor(@entangle($model).live, @js($model))"
    {{ $attributes->merge(['class' => 'rich-text-editor rounded-lg border border-slate-300 bg-white dark:border-slate-700 dark:bg-slate-900']) }}
>
    <div x-ref="editor" data-placeholder="{{ $placeholder }}" class="min-h-[10rem] text-base sm:text-sm"></div>
</div>
