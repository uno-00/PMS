<div>
    <x-page-header title="System Settings" subtitle="Appearance, agency profile, fiscal years, master data, security, integrations, users, and role permissions." />

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
        <nav class="space-y-1 lg:col-span-1">
            @foreach($tabs as $key => $def)
                <button type="button" wire:click="switchTab('{{ $key }}')"
                        class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm font-medium transition
                            {{ $tab === $key ? 'bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                    <x-icon :name="$def['icon']" class="h-4 w-4" />
                    {{ $def['label'] }}
                </button>
            @endforeach
        </nav>

        <div class="lg:col-span-3">
            <x-card>
                @if($tab === 'appearance')
                    @livewire('settings.appearance-tab')
                @elseif($tab === 'profile')
                    @livewire('settings.agency-profile-tab')
                @elseif($tab === 'fiscal-years')
                    @livewire('settings.fiscal-years-tab')
                @elseif($tab === 'reference-data')
                    @livewire('settings.reference-data-tab')
                @elseif($tab === 'security')
                    @livewire('settings.security-tab')
                @elseif($tab === 'integrations')
                    @livewire('settings.integrations-tab')
                @elseif($tab === 'modules')
                    @livewire('settings.modules-tab')
                @elseif($tab === 'users')
                    @livewire('settings.users-tab')
                @elseif($tab === 'rbac')
                    @livewire('settings.rbac-tab')
                @endif
            </x-card>
        </div>
    </div>
</div>
