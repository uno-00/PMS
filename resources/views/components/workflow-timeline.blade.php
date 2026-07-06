@props(['history'])
<div class="flow-root">
    <ul class="-mb-8">
        @forelse($history as $i => $entry)
            <li>
                <div class="relative pb-8">
                    @if(!$loop->last)
                        <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-slate-200 dark:bg-slate-800"></span>
                    @endif
                    <div class="relative flex items-start gap-3">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300">
                            <x-icon name="check" class="h-4 w-4" />
                        </span>
                        <div class="min-w-0 flex-1 pt-1">
                            <p class="text-sm font-medium text-slate-800 dark:text-slate-100">{{ ucfirst(str_replace('-', ' ', $entry->action)) }}</p>
                            <p class="text-xs text-slate-400">
                                {{ $entry->performedBy?->name ?? 'System' }}
                                @if($entry->performed_role) &middot; {{ $entry->performed_role }} @endif
                                &middot; {{ $entry->performed_at->format('M d, Y g:ia') }}
                            </p>
                            @if($entry->remarks)
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $entry->remarks }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </li>
        @empty
            <li class="text-sm text-slate-400">No workflow history yet.</li>
        @endforelse
    </ul>
</div>
