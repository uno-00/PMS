@props(['title' => null])
<div {{ $attributes->merge(['class' => 'rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900']) }}>
    @if($title)
        <h3 class="mb-4 text-sm font-semibold text-slate-700 dark:text-slate-200">{{ $title }}</h3>
    @endif
    {{ $slot }}
</div>
