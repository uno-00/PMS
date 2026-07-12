<div>
    <div class="mb-5 flex items-start justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Module Management</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Activate or deactivate feature modules. Changes apply immediately in the left menu without refreshing the page. Deactivated modules are hidden from users and their pages become inaccessible. Super Admins always retain access to every module.</p>
        </div>
        <x-button wire:click="activateAll" wire:confirm="Activate every module?" variant="secondary" size="sm">Activate All</x-button>
    </div>

    @foreach($groups as $groupName => $groupModules)
        <div class="mb-6 last:mb-0">
            <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">{{ $groupName }}</h3>
            <div class="divide-y divide-slate-100 overflow-hidden rounded-xl border border-slate-200 dark:divide-slate-800 dark:border-slate-700">
                @foreach($groupModules as $module)
                    @php $active = $modules[$module['key']] ?? true; @endphp
                    <div class="flex items-center justify-between gap-4 px-4 py-3.5">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-slate-800 dark:text-slate-200">{{ $module['label'] }}</p>
                            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $module['description'] }}</p>
                        </div>
                        <button type="button"
                                wire:click="toggle('{{ $module['key'] }}')"
                                role="switch"
                                aria-checked="{{ $active ? 'true' : 'false' }}"
                                aria-label="{{ $active ? 'Deactivate' : 'Activate' }} {{ $module['label'] }}"
                                class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 {{ $active ? 'bg-primary-600' : 'bg-slate-300 dark:bg-slate-600' }}">
                            <span aria-hidden="true" class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $active ? 'translate-x-5' : 'translate-x-0' }}"></span>
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
