@props(['title'])
<div>
    <p class="px-3 text-xs font-semibold uppercase tracking-wider text-slate-400">{{ $title }}</p>
    <div class="mt-2 space-y-0.5">
        {{ $slot }}
    </div>
</div>
