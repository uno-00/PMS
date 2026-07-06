<?php

namespace App\Livewire\Settings;

use App\Support\ThemeSettings;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class AppearanceTab extends Component
{
    public string $primary = '';

    public string $gradient_from = '';

    public string $gradient_via = '';

    public string $gradient_to = '';

    public function mount(): void
    {
        Gate::authorize('settings.manage');

        $theme = ThemeSettings::all();
        $this->primary = $theme['primary'];
        $this->gradient_from = $theme['gradient_from'];
        $this->gradient_via = $theme['gradient_via'];
        $this->gradient_to = $theme['gradient_to'];
    }

    public function applyPreset(string $presetKey): void
    {
        Gate::authorize('settings.manage');

        $preset = ThemeSettings::presets()[$presetKey] ?? null;

        if (! $preset) {
            return;
        }

        $this->primary = $preset['primary'];
        $this->gradient_from = $preset['gradient_from'];
        $this->gradient_via = $preset['gradient_via'];
        $this->gradient_to = $preset['gradient_to'];
    }

    public function save(): void
    {
        Gate::authorize('settings.manage');

        $this->validate([
            'primary' => ['required', 'regex:/^#?[0-9a-fA-F]{3}([0-9a-fA-F]{3})?$/'],
            'gradient_from' => ['required', 'regex:/^#?[0-9a-fA-F]{3}([0-9a-fA-F]{3})?$/'],
            'gradient_via' => ['required', 'regex:/^#?[0-9a-fA-F]{3}([0-9a-fA-F]{3})?$/'],
            'gradient_to' => ['required', 'regex:/^#?[0-9a-fA-F]{3}([0-9a-fA-F]{3})?$/'],
        ]);

        ThemeSettings::save([
            'primary' => $this->primary,
            'gradient_from' => $this->gradient_from,
            'gradient_via' => $this->gradient_via,
            'gradient_to' => $this->gradient_to,
        ]);

        session()->flash('status', 'Theme colors updated. Refresh any open tabs to see the new gradient everywhere.');
    }

    public function resetDefaults(): void
    {
        Gate::authorize('settings.manage');

        $defaults = ThemeSettings::defaults();
        $this->fill($defaults);
        ThemeSettings::save($defaults);

        session()->flash('status', 'Theme restored to default colors.');
    }

    public function render()
    {
        return view('livewire.settings.appearance-tab', [
            'presets' => ThemeSettings::presets(),
        ]);
    }
}
