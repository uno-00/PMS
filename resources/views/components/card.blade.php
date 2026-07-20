@props(['title' => null, 'hover' => true])
@php
    $cardClass = ($hover ?? true) ? 'surface-card' : 'surface-card-static';
@endphp
<div {{ $attributes->merge(['class' => $cardClass.' px-5 pt-5 pb-7 sm:px-6 sm:pt-6 sm:pb-8']) }}>
    @if($title)
        <div class="mb-4 flex items-center justify-between gap-3 border-b border-slate-100 pb-4 dark:border-slate-800">
            <h3 class="text-sm font-semibold tracking-tight text-slate-800 dark:text-slate-100">{{ $title }}</h3>
        </div>
    @endif
    {{ $slot }}
</div>
