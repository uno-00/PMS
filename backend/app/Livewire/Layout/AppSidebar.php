<?php

namespace App\Livewire\Layout;

use App\Support\ModuleRegistry;
use App\Support\ModuleState;
use App\Support\NavAccess;
use Livewire\Attributes\On;
use Livewire\Component;

class AppSidebar extends Component
{
    /** @var array<string, bool> */
    public array $moduleStates = [];

    public function mount(): void
    {
        $this->syncModuleStatesFromStorage();
    }

    #[On('module-toggled')]
    public function onModuleToggled(string $module, bool $active): void
    {
        if (! ModuleRegistry::isToggleable($module)) {
            return;
        }

        $this->moduleStates[$module] = $active;
    }

    /** @param  array<string, bool>  $modules */
    #[On('modules-synced')]
    public function onModulesSynced(array $modules): void
    {
        foreach (ModuleRegistry::toggleableKeys() as $key) {
            $this->moduleStates[$key] = filter_var($modules[$key] ?? true, FILTER_VALIDATE_BOOLEAN);
        }
    }

    public function navCan(string $permission): bool
    {
        return NavAccess::can($permission, moduleStates: $this->moduleStates);
    }

    /** @param  array<int, string>  $permissions */
    public function navCanAny(array $permissions): bool
    {
        return NavAccess::canAny($permissions, moduleStates: $this->moduleStates);
    }

    protected function syncModuleStatesFromStorage(): void
    {
        foreach (ModuleRegistry::toggleableKeys() as $key) {
            $this->moduleStates[$key] = ModuleState::isActive($key);
        }
    }

    public function render()
    {
        return view('livewire.layout.app-sidebar');
    }
}
