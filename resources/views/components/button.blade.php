@props(['href' => null, 'variant' => 'primary', 'type' => 'button', 'size' => 'md', 'disabled' => false])
@php
    $base = 'inline-flex items-center justify-center gap-1.5 rounded-xl font-semibold transition-all duration-200 ease-out focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:translate-y-0 disabled:hover:shadow-none active:scale-[0.98]';

    $variants = [
        'primary' => 'bg-gradient-to-b from-primary-600 to-primary-700 text-white shadow-sm shadow-primary-900/20 hover:-translate-y-0.5 hover:from-primary-700 hover:to-primary-800 hover:shadow-md hover:shadow-primary-900/30 focus:ring-primary-500 dark:shadow-none dark:hover:shadow-lg dark:hover:shadow-primary-950/50',
        'secondary' => 'border border-slate-300 bg-white text-slate-700 shadow-sm hover:-translate-y-0.5 hover:border-primary-300 hover:bg-primary-50/60 hover:text-primary-800 hover:shadow-md focus:ring-primary-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:border-primary-700 dark:hover:bg-primary-950/40 dark:hover:text-primary-200',
        'danger' => 'bg-gradient-to-b from-red-600 to-red-700 text-white shadow-sm shadow-red-900/20 hover:-translate-y-0.5 hover:from-red-700 hover:to-red-800 hover:shadow-md hover:shadow-red-900/25 focus:ring-red-500',
        'success' => 'bg-gradient-to-b from-emerald-600 to-emerald-700 text-white shadow-sm shadow-emerald-900/20 hover:-translate-y-0.5 hover:from-emerald-700 hover:to-emerald-800 hover:shadow-md hover:shadow-emerald-900/25 focus:ring-emerald-500',
        'ghost' => 'text-slate-600 hover:-translate-y-0.5 hover:bg-slate-100 hover:text-slate-900 hover:shadow-sm focus:ring-slate-400 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white',
    ];
    $sizes = [
        'sm' => 'px-3 py-1.5 text-xs',
        'md' => 'px-4 py-2.5 text-sm',
    ];
    $classes = $base.' '.($variants[$variant] ?? $variants['primary']).' '.($sizes[$size] ?? $sizes['md']);
@endphp
@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }} @disabled($disabled)>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }} @disabled($disabled)>{{ $slot }}</button>
@endif
