@props(['href', 'active' => false, 'icon' => null])
<a href="{{ $href }}"
   @class([
        'group flex items-center gap-2.5 rounded-xl border-l-[3px] px-3 py-2.5 text-sm font-medium transition-all duration-200 ease-out',
        'border-primary-600 bg-primary-50 text-primary-800 shadow-sm shadow-primary-900/5 dark:border-primary-400 dark:bg-primary-900/30 dark:text-primary-200 dark:shadow-none' => $active,
        'border-transparent text-slate-600 hover:-translate-y-px hover:border-slate-200 hover:bg-slate-100 hover:text-slate-900 hover:shadow-sm dark:text-slate-300 dark:hover:border-slate-700 dark:hover:bg-slate-800/80 dark:hover:text-white' => ! $active,
   ])
>
    @if($icon)
        <span @class([
            'flex h-8 w-8 shrink-0 items-center justify-center rounded-lg transition-all duration-200',
            'bg-primary-100 text-primary-700 dark:bg-primary-800/50 dark:text-primary-200' => $active,
            'bg-slate-100 text-slate-500 group-hover:scale-105 group-hover:bg-white group-hover:text-primary-700 group-hover:shadow-sm dark:bg-slate-800 dark:text-slate-400 dark:group-hover:bg-slate-700 dark:group-hover:text-primary-300' => ! $active,
        ])>
            <x-icon :name="$icon" class="h-4 w-4" />
        </span>
    @endif
    <span class="truncate">{{ $slot }}</span>
</a>
