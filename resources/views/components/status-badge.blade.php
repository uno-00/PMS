@props(['status'])
@php
    $colorMap = [
        'slate' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
        'amber' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
        'emerald' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
        'blue' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
        'indigo' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300',
        'purple' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300',
        'cyan' => 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-300',
        'teal' => 'bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-300',
        'sky' => 'bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-300',
    ];
    $color = method_exists($status, 'color') ? $status->color() : 'slate';
    $label = method_exists($status, 'label') ? $status->label() : (string) $status;
@endphp
<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium '.($colorMap[$color] ?? $colorMap['slate'])]) }}>
    {{ $label }}
</span>
