<?php

namespace App\Livewire\Budget;

use App\Exceptions\BudgetExceededException;
use App\Livewire\Concerns\InteractsWithTableFilters;
use App\Models\Budget\BudgetAllocation;
use App\Models\Settings\CostCenter;
use App\Models\Settings\Division;
use App\Models\Settings\FiscalYear;
use App\Models\Settings\Office;
use App\Services\Budget\BudgetAllocationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.app')]
class AllocationIndex extends Component
{
    use InteractsWithTableFilters;

    #[Url]
    public string $fiscalYearId = '';

    #[Url]
    public string $filterLevel = '';

    #[Url]
    public string $filterOrgUnit = '';

    #[Url]
    public string $filterPap = '';

    #[Url]
    public string $filterAllocated = '';

    #[Url]
    public string $filterUtilized = '';

    #[Url]
    public string $filterRemaining = '';

    public bool $showAllocateModal = false;

    public ?string $parentId = null;

    public string $level = 'division';

    public string $target_id = '';

    public string $allocated_amount = '';

    public string $remarks = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', BudgetAllocation::class);
        $this->fiscalYearId = $this->fiscalYearId !== '' ? $this->fiscalYearId : (
            FiscalYear::query()->where('is_current', true)->value('id')
            ?? FiscalYear::query()->orderByDesc('year')->value('id') ?? ''
        );
    }

    public function resetFilters(): void
    {
        $this->resetTableFilters([
            'filterLevel',
            'filterOrgUnit',
            'filterPap',
            'filterAllocated',
            'filterUtilized',
            'filterRemaining',
        ]);
    }

    public function openAllocateModal(string $parentId): void
    {
        Gate::authorize('budget-allocation.allocate');
        $this->parentId = $parentId;
        $this->level = 'division';
        $this->target_id = '';
        $this->allocated_amount = '';
        $this->remarks = '';
        $this->resetErrorBag();
        $this->showAllocateModal = true;
    }

    public function allocate(BudgetAllocationService $service): void
    {
        $this->validate([
            'level' => ['required', 'in:division,office,cost_center'],
            'target_id' => ['required', 'string'],
            'allocated_amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        $parent = BudgetAllocation::query()->findOrFail($this->parentId);

        $attributes = [
            'level' => $this->level,
            'allocated_amount' => $this->allocated_amount,
            'remarks' => $this->remarks,
        ];

        $attributes[match ($this->level) {
            'division' => 'division_id',
            'office' => 'office_id',
            'cost_center' => 'cost_center_id',
        }] = $this->target_id;

        try {
            $service->allocate($parent, $attributes, Auth::user());
            $this->showAllocateModal = false;
            session()->flash('status', 'Budget sub-allocated successfully.');
        } catch (BudgetExceededException $e) {
            $this->addError('allocated_amount', $e->getMessage());
        }
    }

    public function delete(string $id, BudgetAllocationService $service): void
    {
        $allocation = BudgetAllocation::query()->findOrFail($id);
        Gate::authorize('delete', $allocation);

        try {
            $service->delete($allocation);
            session()->flash('status', 'Budget allocation removed.');
        } catch (\DomainException $e) {
            session()->flash('status', $e->getMessage());
        }
    }

    public function render()
    {
        $query = BudgetAllocation::query()
            ->when($this->fiscalYearId, fn ($q) => $q->where('fiscal_year_id', $this->fiscalYearId))
            ->with(['department', 'division', 'office', 'costCenter', 'pap', 'fundSource', 'parent', 'fiscalYear']);

        $this->applyExactFilter($query, 'level', $this->filterLevel);

        if ($this->filterOrgUnit !== '') {
            $query->where(function ($q) {
                $q->whereHas('division', fn ($inner) => $inner->where('name', 'like', '%'.$this->filterOrgUnit.'%'))
                    ->orWhereHas('office', fn ($inner) => $inner->where('name', 'like', '%'.$this->filterOrgUnit.'%'))
                    ->orWhereHas('costCenter', fn ($inner) => $inner->where('name', 'like', '%'.$this->filterOrgUnit.'%'))
                    ->orWhereHas('department', fn ($inner) => $inner->where('name', 'like', '%'.$this->filterOrgUnit.'%'));
            });
        }

        if ($this->filterPap !== '') {
            $query->whereHas('pap', fn ($q) => $q->where('code', 'like', '%'.$this->filterPap.'%'));
        }

        $this->applyAmountFilter($query, 'allocated_amount', $this->filterAllocated);
        $this->applyAmountFilter($query, 'utilized_amount', $this->filterUtilized);

        if ($this->filterRemaining !== '') {
            $amount = (float) str_replace(',', '', $this->filterRemaining);
            if ($amount > 0) {
                $query->whereRaw('(allocated_amount - utilized_amount) = ?', [$amount]);
            }
        }

        $this->applyCreatedAtFilter($query);

        $allocations = $query->orderBy('level')->get();

        $fiscalYears = FiscalYear::query()->orderByDesc('year')->get();
        $divisions = Division::query()->orderBy('name')->get();
        $offices = Office::query()->orderBy('name')->get();
        $costCenters = CostCenter::query()->orderBy('name')->get();

        return view('livewire.budget.allocation-index', compact('allocations', 'fiscalYears', 'divisions', 'offices', 'costCenters'))
            ->layout('components.layouts.app', ['title' => 'Budget Allocation']);
    }
}
