<?php

namespace App\Livewire\Budget;

use App\Exceptions\BudgetExceededException;
use App\Models\Budget\BudgetAllocation;
use App\Models\Settings\CostCenter;
use App\Models\Settings\Department;
use App\Models\Settings\Division;
use App\Models\Settings\FiscalYear;
use App\Models\Settings\FundSource;
use App\Models\Settings\Office;
use App\Models\Settings\Pap;
use App\Models\Settings\UacsCode;
use App\Services\Budget\BudgetAllocationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class AllocationForm extends Component
{
    public ?BudgetAllocation $allocation = null;

    public string $fiscal_year_id = '';

    public string $level = 'department';

    public string $department_id = '';

    public string $division_id = '';

    public string $office_id = '';

    public string $cost_center_id = '';

    public string $pap_id = '';

    public string $fund_source_id = '';

    public string $uacs_code_id = '';

    public string $allocated_amount = '';

    public string $remarks = '';

    public function mount(?BudgetAllocation $allocation = null): void
    {
        if ($allocation && $allocation->exists) {
            Gate::authorize('update', $allocation);
            $this->allocation = $allocation;
            $this->fillFromModel($allocation);
        } else {
            Gate::authorize('create', BudgetAllocation::class);
            $this->fiscal_year_id = FiscalYear::query()->where('is_current', true)->value('id')
                ?? FiscalYear::query()->orderByDesc('year')->value('id') ?? '';
        }
    }

    protected function fillFromModel(BudgetAllocation $allocation): void
    {
        $this->fiscal_year_id = $allocation->fiscal_year_id ?? '';
        $this->level = $allocation->level;
        $this->department_id = $allocation->department_id ?? '';
        $this->division_id = $allocation->division_id ?? '';
        $this->office_id = $allocation->office_id ?? '';
        $this->cost_center_id = $allocation->cost_center_id ?? '';
        $this->pap_id = $allocation->pap_id ?? '';
        $this->fund_source_id = $allocation->fund_source_id ?? '';
        $this->uacs_code_id = $allocation->uacs_code_id ?? '';
        $this->allocated_amount = (string) $allocation->allocated_amount;
        $this->remarks = $allocation->remarks ?? '';
    }

    protected function rules(): array
    {
        return [
            'fiscal_year_id' => ['required', 'exists:fiscal_years,id'],
            'level' => ['required', 'in:department,division,office,cost_center'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'division_id' => ['nullable', 'exists:divisions,id'],
            'office_id' => ['nullable', 'exists:offices,id'],
            'cost_center_id' => ['nullable', 'exists:cost_centers,id'],
            'pap_id' => ['nullable', 'exists:paps,id'],
            'fund_source_id' => ['nullable', 'exists:fund_sources,id'],
            'uacs_code_id' => ['nullable', 'exists:uacs_codes,id'],
            'allocated_amount' => ['required', 'numeric', 'min:0.01'],
            'remarks' => ['nullable', 'string'],
        ];
    }

    public function save(BudgetAllocationService $service): void
    {
        $this->validate();

        $payload = [
            'level' => $this->level,
            'department_id' => $this->department_id ?: null,
            'division_id' => $this->division_id ?: null,
            'office_id' => $this->office_id ?: null,
            'cost_center_id' => $this->cost_center_id ?: null,
            'pap_id' => $this->pap_id ?: null,
            'fund_source_id' => $this->fund_source_id ?: null,
            'uacs_code_id' => $this->uacs_code_id ?: null,
            'allocated_amount' => $this->allocated_amount,
            'remarks' => $this->remarks ?: null,
        ];

        if ($this->allocation && $this->allocation->exists) {
            try {
                $service->update($this->allocation, $payload, Auth::user());
            } catch (BudgetExceededException $e) {
                $this->addError('allocated_amount', $e->getMessage());

                return;
            }
            $record = $this->allocation;
        } else {
            $record = BudgetAllocation::query()->create(array_merge($payload, [
                'fiscal_year_id' => $this->fiscal_year_id,
                'created_by' => Auth::id(),
            ]));
        }

        session()->flash('status', 'Budget allocation saved.');
        $this->redirect(route('budget-allocations.index'), navigate: false);
    }

    public function render()
    {
        return view('livewire.budget.allocation-form', [
            'fiscalYears' => FiscalYear::query()->orderByDesc('year')->get(),
            'departments' => Department::query()->orderBy('name')->get(),
            'divisions' => Division::query()->orderBy('name')->get(),
            'offices' => Office::query()->orderBy('name')->get(),
            'costCenters' => CostCenter::query()->orderBy('name')->get(),
            'paps' => Pap::query()->orderBy('name')->get(),
            'fundSources' => FundSource::query()->orderBy('name')->get(),
            'uacsCodes' => UacsCode::query()->orderBy('code')->get(),
        ])->layout('components.layouts.app', [
            'title' => $this->allocation && $this->allocation->exists ? 'Edit Budget Allocation' : 'New Budget Allocation',
        ]);
    }
}
