<?php

namespace App\Livewire\Gaa;

use App\Models\Budget\GeneralAppropriationsAct;
use App\Models\Settings\FiscalYear;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    public function mount(): void
    {
        Gate::authorize('viewAny', GeneralAppropriationsAct::class);
    }

    public function render()
    {
        $gaas = GeneralAppropriationsAct::query()
            ->with('fiscalYear')
            ->orderByDesc('created_at')
            ->paginate(15);

        $fiscalYearsWithoutGaa = FiscalYear::query()
            ->whereDoesntHave('gaa')
            ->orderByDesc('year')
            ->get();

        return view('livewire.gaa.index', compact('gaas', 'fiscalYearsWithoutGaa'))
            ->layout('components.layouts.app', ['title' => 'General Appropriations Act']);
    }
}
