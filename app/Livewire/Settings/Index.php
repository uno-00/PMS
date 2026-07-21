<?php

namespace App\Livewire\Settings;

use Illuminate\Support\Facades\Gate;
use Livewire\Component;

/**
 * System Settings shell. Everything a Super Admin/System Admin needs to
 * configure without a code deployment: Appearance, Agency Profile, Fiscal
 * Years, Reference/Master Data, Security policy, Integrations, Users, and
 * RBAC.
 */
class Index extends Component
{
    public string $tab = 'profile';

    /** @var array<string, array{label: string, icon: string, permission: string}> */
    protected array $tabDefinitions = [
        'appearance' => ['label' => 'Appearance', 'icon' => 'swatch', 'permission' => 'settings.manage'],
        'profile' => ['label' => 'Agency Profile', 'icon' => 'building-office', 'permission' => 'settings.manage'],
        'fiscal-years' => ['label' => 'Fiscal Years', 'icon' => 'calendar', 'permission' => 'settings.manage'],
        'reference-data' => ['label' => 'Reference Data & Thresholds', 'icon' => 'clipboard', 'permission' => 'settings.manage'],
        'security' => ['label' => 'Security', 'icon' => 'shield-check', 'permission' => 'settings.manage'],
        'integrations' => ['label' => 'Integrations', 'icon' => 'globe', 'permission' => 'settings.manage'],
        'modules' => ['label' => 'Module Management', 'icon' => 'view-grid', 'permission' => 'settings.manage'],
        'users' => ['label' => 'Users', 'icon' => 'users', 'permission' => 'users.view'],
        'rbac' => ['label' => 'Roles & Permissions', 'icon' => 'key', 'permission' => 'users.manage-roles'],
    ];

    public function mount(?string $tab = null): void
    {
        Gate::authorize('access-settings');

        $tabs = $this->availableTabs();
        $defaultTab = array_key_first($tabs) ?: 'profile';
        $this->tab = ($tab !== null && array_key_exists($tab, $tabs)) ? $tab : $defaultTab;
    }

    public function switchTab(string $tab): void
    {
        $tabs = $this->availableTabs();
        $this->tab = array_key_exists($tab, $tabs) ? $tab : (array_key_first($tabs) ?: 'profile');
        $this->redirect(route('settings.index', ['tab' => $this->tab]), navigate: false);
    }

    /** @return array<string, array{label: string, icon: string, permission: string}> */
    protected function availableTabs(): array
    {
        return collect($this->tabDefinitions)
            ->filter(fn (array $def) => auth()->user()->can($def['permission']))
            ->all();
    }

    public function render()
    {
        return view('livewire.settings.index', ['tabs' => $this->availableTabs()])
            ->layout('components.layouts.app', ['title' => 'System Settings']);
    }
}
