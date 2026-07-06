@props(['icon' => 'clipboard', 'title' => 'Nothing here yet', 'description' => null])
<div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center dark:border-slate-700 dark:bg-slate-900">
    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400 dark:bg-slate-800">
        <x-icon :name="$icon" class="h-6 w-6" />
    </span>
    <p class="mt-4 text-sm font-semibold text-slate-700 dark:text-slate-200">{{ $title }}</p>
    @if($description)
        <p class="mt-1 max-w-sm text-sm text-slate-400">{{ $description }}</p>
    @endif
    @isset($actions)
        <div class="mt-4">{{ $actions }}</div>
    @endisset
</div>
