<?php

namespace App\Livewire\Settings;

use App\Models\Settings\SystemSetting;
use App\Support\ModuleRegistry;
use App\Support\ModuleState;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

/**
 * Settings > Module Management: lets a Super Admin activate or deactivate
 * feature modules. Each toggle persists a boolean into the SystemSetting
 * "modules" group, and a Gate::after callback in AppServiceProvider denies
 * any {module}.{action} permission for a deactivated module (for everyone
 * except Super Admin, who always retains full access via Gate::before).
 *
 * The Settings module itself is intentionally never toggleable — disabling
 * it would lock every administrator out of this screen.
 */
class ModulesTab extends Component
{
    /** @var array<string, bool> module key => activated */
    public array $modules = [];

    public function mount(): void
    {
        Gate::authorize('settings.manage');

        $saved = SystemSetting::group('modules');

        // Default every module to active when no stored value exists, so the
        // first visit does not silently turn features off.
        foreach (ModuleRegistry::toggleableKeys() as $key) {
            $this->modules[$key] = filter_var($saved[$key] ?? '1', FILTER_VALIDATE_BOOLEAN);
        }
    }

    public function toggle(string $key): void
    {
        Gate::authorize('settings.manage');

        if (! ModuleRegistry::isToggleable($key)) {
            return;
        }

        $this->modules[$key] = ! ($this->modules[$key] ?? true);

        ModuleState::set($key, $this->modules[$key]);

        $this->dispatch('module-toggled', module: $key, active: $this->modules[$key]);

        session()->flash(
            'status',
            $this->modules[$key]
                ? __(':module module activated.', ['module' => ucwords(str_replace('-', ' ', $key))])
                : __(':module module deactivated.', ['module' => ucwords(str_replace('-', ' ', $key))])
        );
    }

    public function activateAll(): void
    {
        Gate::authorize('settings.manage');

        foreach (ModuleRegistry::toggleableKeys() as $key) {
            $this->modules[$key] = true;
            ModuleState::set($key, true);
        }

        $this->dispatch('modules-synced', modules: $this->modules);

        session()->flash('status', 'All modules activated.');
    }

    public function render()
    {
        return view('livewire.settings.modules-tab', [
            'groups' => ModuleRegistry::grouped(),
            'alwaysOn' => ModuleRegistry::alwaysOn(),
        ]);
    }
}
