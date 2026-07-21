<?php

namespace App\Livewire\Help;

use App\Support\HelpContent;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class Index extends Component
{
    public string $module = 'gaa';

    public function mount(?string $module = null): void
    {
        Gate::authorize('help.view');

        $modules = HelpContent::modules();
        $this->module = ($module && array_key_exists($module, $modules)) ? $module : 'getting-started';
    }

    public function selectModule(string $module): void
    {
        $this->module = $module;
        $this->redirect(route('help.index', ['module' => $module]), navigate: false);
    }

    public function render()
    {
        $modules = HelpContent::modules();
        $roleManuals = HelpContent::roleManuals();

        return view('livewire.help.index', [
            'modules' => $modules,
            'roleManuals' => $roleManuals,
            'active' => $modules[$this->module] ?? reset($modules),
        ])->layout('components.layouts.app', ['title' => 'Help & User Manuals']);
    }
}
