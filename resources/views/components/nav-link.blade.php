@props(['href', 'active' => false, 'icon' => null])
<a href="{{ $href }}"
   @class([
        'flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium transition',
        'bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300' => $active,
        'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white' => ! $active,
   ])
>
    @if($icon)
        <x-icon :name="$icon" class="h-4.5 w-4.5 shrink-0" />
    @endif
    <span class="truncate">{{ $slot }}</span>
</a>
