@props(['section' => null, 'label' => 'Generate Context from AI'])

@php
    $target = $section ? "draftSectionWithAi('{$section}')" : 'draftAllNarrativeSections';
    $wireTarget = $section ? "draftSectionWithAi('{$section}')" : 'draftAllNarrativeSections';
    $labelClass = 'block text-sm font-medium text-slate-700 dark:text-slate-300';
@endphp

<button
    type="button"
    wire:click="{{ $target }}"
    wire:loading.attr="disabled"
    wire:target="{{ $wireTarget }}"
    {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 transition hover:bg-slate-50 disabled:opacity-60 dark:border-slate-700 dark:bg-slate-900 dark:hover:bg-slate-800']) }}
>
    <span wire:loading.remove wire:target="{{ $wireTarget }}" class="{{ $labelClass }}">Generate Context from AI</span>
    <span wire:loading wire:target="{{ $wireTarget }}" class="{{ $labelClass }}">Generating&hellip;</span>
</button>
