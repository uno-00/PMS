<?php

namespace App\Livewire\Gaa;

use App\Exceptions\BudgetExceededException;
use App\Models\Budget\GeneralAppropriationsAct;
use App\Services\Budget\GaaService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

class Show extends Component
{
    use WithPagination;

    public GeneralAppropriationsAct $gaa;

    public bool $showCompare = false;

    public array $comparison = [];

    public function mount(GeneralAppropriationsAct $gaa): void
    {
        Gate::authorize('view', $gaa);
        $this->gaa = $gaa;
    }

    public function validateBudget(GaaService $service): void
    {
        Gate::authorize('validateBudget', $this->gaa);

        $result = $service->validateBudget($this->gaa);

        if ($result->passed) {
            session()->flash('status', "Budget validated successfully. {$result->lineItemCount} line items totaling ₱".number_format($result->totalAmount, 2).'.');
        } else {
            $this->addError('validation', implode(' ', $result->errors));
        }

        $this->gaa->refresh();
    }

    public function compareBudget(GaaService $service): void
    {
        Gate::authorize('view', $this->gaa);
        $this->comparison = $service->compareBudget($this->gaa);
        $this->showCompare = true;
    }

    public function approve(GaaService $service): void
    {
        Gate::authorize('approve', $this->gaa);
        $service->approve($this->gaa, Auth::user());
        session()->flash('status', 'GAA approved. The Annual Procurement Plan has been auto-created for this fiscal year.');
        $this->gaa->refresh();
    }

    public function distribute(GaaService $service): void
    {
        Gate::authorize('distribute', $this->gaa);

        try {
            $service->distribute($this->gaa, Auth::user());
            session()->flash('status', 'Budget allocation generated and distributed to departments/divisions.');
        } catch (BudgetExceededException $e) {
            $this->addError('distribute', $e->getMessage());
        }

        $this->gaa->refresh();
    }

    public function render()
    {
        $lineItems = $this->gaa->lineItems()
            ->with(['department', 'division', 'pap', 'uacsCode', 'fundSource'])
            ->orderBy('line_no')
            ->paginate(20);

        return view('livewire.gaa.show', compact('lineItems'))
            ->layout('components.layouts.app', ['title' => $this->gaa->title]);
    }
}
