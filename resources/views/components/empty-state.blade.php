@props(['icon' => 'clipboard', 'title' => 'Nothing here yet', 'description' => null])
<div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-300/80 bg-slate-50/50 px-6 py-16 text-center dark:border-slate-700 dark:bg-slate-900/50">
    <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-slate-400 shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-800 dark:ring-slate-700">
        <x-icon :name="$icon" class="h-7 w-7" />
    </span>
    <p class="mt-5 text-base font-semibold text-slate-800 dark:text-slate-100">{{ $title }}</p>
    @if($description)
        <p class="mt-2 max-w-sm text-sm leading-relaxed text-slate-500 dark:text-slate-400">{{ $description }}</p>
    @endif
    @isset($actions)
        <div class="mt-5">{{ $actions }}</div>
    @endisset
</div>
