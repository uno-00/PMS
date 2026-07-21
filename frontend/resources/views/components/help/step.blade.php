@props(['number', 'text', 'screenshot' => null, 'caption' => null])

<div {{ $attributes->merge(['class' => 'rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900/40']) }}>
    <div class="flex gap-4 p-4">
        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary-100 text-sm font-bold text-primary-700 dark:bg-primary-900/40 dark:text-primary-300">
            {{ $number }}
        </div>
        <div class="min-w-0 flex-1">
            <p class="text-sm leading-relaxed text-slate-700 dark:text-slate-200">{{ $text }}</p>
            @if($caption)
                <p class="mt-1 text-xs text-slate-400">{{ $caption }}</p>
            @endif
        </div>
    </div>
    @if($screenshot)
        <div class="border-t border-slate-100 px-4 pb-4 pt-3 dark:border-slate-800">
            <a href="{{ asset($screenshot) }}" target="_blank" rel="noopener" class="group block overflow-hidden rounded-lg border border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-950">
                <img src="{{ asset($screenshot) }}" alt="Step {{ $number }} screenshot" class="max-h-80 w-full object-cover object-top transition group-hover:opacity-95" loading="lazy">
                <p class="px-3 py-2 text-center text-xs text-slate-400 group-hover:text-primary-600 dark:group-hover:text-primary-400">Click to view full size</p>
            </a>
        </div>
    @endif
</div>
