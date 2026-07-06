<?php

namespace App\Livewire\Settings;

use App\Enums\FiscalYearStatus;
use App\Models\Settings\FiscalYear;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

/**
 * Phase 1: "Super Admin creates Fiscal Year". Activating a fiscal year
 * flips every other year back to draft/closed so exactly one fiscal
 * year is ever "current" — the anchor every downstream module
 * (APP, PPMP, PR) filters against.
 */
class FiscalYearsTab extends Component
{
    public bool $showCreateModal = false;

    public string $year = '';

    public string $start_date = '';

    public string $end_date = '';

    public function mount(): void
    {
        Gate::authorize('fiscal-year.view');
        $this->year = (string) (now()->year + 1);
    }

    public function openCreateModal(): void
    {
        Gate::authorize('fiscal-year.create');
        $this->reset('start_date', 'end_date');
        $this->year = (string) (((int) FiscalYear::query()->max('year')) + 1 ?: now()->year);
        $this->start_date = $this->year.'-01-01';
        $this->end_date = $this->year.'-12-31';
        $this->showCreateModal = true;
    }

    public function create(): void
    {
        Gate::authorize('fiscal-year.create');

        $this->validate([
            'year' => 'required|integer|min:2000|max:2100|unique:fiscal_years,year',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        FiscalYear::query()->create([
            'year' => $this->year,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status' => FiscalYearStatus::Draft,
            'is_current' => false,
            'created_by' => Auth::id(),
        ]);

        $this->showCreateModal = false;
        session()->flash('status', "Fiscal Year {$this->year} created.");
    }

    public function activate(string $id): void
    {
        Gate::authorize('fiscal-year.activate');

        DB::transaction(function () use ($id) {
            FiscalYear::query()->where('id', '!=', $id)->update(['is_current' => false]);

            $fy = FiscalYear::findOrFail($id);
            $fy->update(['is_current' => true, 'status' => FiscalYearStatus::Active]);
        });

        session()->flash('status', 'Fiscal year activated as the current fiscal year.');
    }

    public function close(string $id): void
    {
        Gate::authorize('fiscal-year.edit');

        FiscalYear::findOrFail($id)->update(['status' => FiscalYearStatus::Closed, 'is_current' => false]);
        session()->flash('status', 'Fiscal year closed.');
    }

    public function render()
    {
        return view('livewire.settings.fiscal-years-tab', [
            'fiscalYears' => FiscalYear::query()->with(['gaa', 'annualProcurementPlan'])->orderByDesc('year')->get(),
        ]);
    }
}
