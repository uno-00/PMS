@props(['title'])
<div>
    <p class="px-3 pb-1 text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400 dark:text-slate-500">{{ $title }}</p>
    <div class="mt-1 space-y-1">
        {{ $slot }}
    </div>
</div>
