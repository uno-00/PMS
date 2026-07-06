<?php

namespace App\Livewire\Planning;

use App\Enums\PpmpStatus;
use App\Enums\PreProcurementConference;
use App\Models\Budget\BudgetAllocation;
use App\Models\Planning\Ppmp;
use App\Models\Settings\Division;
use App\Models\Settings\FiscalYear;
use App\Models\Settings\ModeOfProcurement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class PpmpForm extends Component
{
    public ?Ppmp $ppmp = null;

    public string $title = '';

    public string $fiscal_year_id = '';

    public string $division_id = '';

    public array $items = [];

    public int $focusedItemIndex = 0;

    public function mount(?Ppmp $ppmp = null): void
    {
        if ($ppmp && $ppmp->exists) {
            Gate::authorize('update', $ppmp);
            $this->ppmp = $ppmp;
            $this->title = $ppmp->title;
            $this->fiscal_year_id = $ppmp->fiscal_year_id;
            $this->division_id = $ppmp->division_id;
            $this->items = $ppmp->items()->orderBy('item_no')->get()->map(fn ($i) => [
                'id' => $i->id,
                'item_name' => $i->item_name,
                'description' => $i->description,
                'specification' => $i->specification,
                'unit' => $i->unit,
                'quantity' => (string) $i->quantity,
                'estimated_unit_cost' => (string) $i->estimated_unit_cost,
                'schedule_start' => optional($i->schedule_start)->format('Y-m-d'),
                'schedule_end' => optional($i->schedule_end)->format('Y-m-d'),
                'mode_of_procurement_id' => $i->mode_of_procurement_id,
                'pre_procurement_conference' => $i->pre_procurement_conference?->value ?? '',
                'budget_allocation_id' => $i->budget_allocation_id,
                'remarks' => $i->remarks,
            ])->all();
        } else {
            Gate::authorize('create', Ppmp::class);
            $user = Auth::user();
            $this->fiscal_year_id = FiscalYear::query()->where('is_current', true)->value('id') ?? '';
            $this->division_id = $user->division_id ?? '';
        }

        if (empty($this->items)) {
            $this->addItem();
        }
    }

    public function addItem(): void
    {
        $this->items[] = [
            'id' => null,
            'item_name' => '',
            'description' => '',
            'specification' => '',
            'unit' => '',
            'quantity' => '',
            'estimated_unit_cost' => '',
            'schedule_start' => '',
            'schedule_end' => '',
            'mode_of_procurement_id' => '',
            'pre_procurement_conference' => '',
            'budget_allocation_id' => '',
            'remarks' => '',
        ];

        $this->focusedItemIndex = count($this->items) - 1;
    }

    public function focusItem(int $index): void
    {
        if ($index >= 0 && $index < count($this->items)) {
            $this->focusedItemIndex = $index;
        }
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);

        if ($this->items === []) {
            $this->addItem();

            return;
        }

        $this->focusedItemIndex = min($this->focusedItemIndex, count($this->items) - 1);
        if ($this->focusedItemIndex < 0) {
            $this->focusedItemIndex = 0;
        }
    }

    public function getTotalAbcProperty(): float
    {
        return (float) collect($this->items)->sum(
            fn (array $row) => (float) ($row['quantity'] ?: 0) * (float) ($row['estimated_unit_cost'] ?: 0)
        );
    }

    /** @param  array<string, mixed>  $row */
    public static function lineAbc(array $row): float
    {
        return (float) ($row['quantity'] ?: 0) * (float) ($row['estimated_unit_cost'] ?: 0);
    }

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'fiscal_year_id' => ['required', 'exists:fiscal_years,id'],
            'division_id' => ['required', 'exists:divisions,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.unit' => ['required', 'string', 'max:50'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.estimated_unit_cost' => ['required', 'numeric', 'min:0'],
            'items.*.budget_allocation_id' => ['required', 'exists:budget_allocations,id'],
            'items.*.mode_of_procurement_id' => ['nullable', 'exists:modes_of_procurement,id'],
            'items.*.pre_procurement_conference' => ['nullable', 'in:yes,no,na'],
            'items.*.schedule_start' => ['nullable', 'date'],
            'items.*.schedule_end' => ['nullable', 'date', 'after_or_equal:items.*.schedule_start'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        $allocations = BudgetAllocation::query()->whereIn('id', collect($this->items)->pluck('budget_allocation_id'))->get()->keyBy('id');

        if ($this->ppmp) {
            $this->ppmp->update([
                'title' => $this->title,
                'fiscal_year_id' => $this->fiscal_year_id,
                'division_id' => $this->division_id,
            ]);
            $ppmp = $this->ppmp;
        } else {
            $ppmp = Ppmp::query()->create([
                'title' => $this->title,
                'fiscal_year_id' => $this->fiscal_year_id,
                'division_id' => $this->division_id,
                'ppmp_type' => 'regular',
                'status' => PpmpStatus::Draft,
                'prepared_by' => Auth::id(),
            ]);
        }

        $keptIds = [];

        foreach ($this->items as $i => $row) {
            $allocation = $allocations->get($row['budget_allocation_id']);

            $payload = [
                'item_no' => $i + 1,
                'item_name' => $row['item_name'],
                'description' => $row['description'],
                'specification' => $row['specification'],
                'unit' => $row['unit'],
                'quantity' => $row['quantity'],
                'estimated_unit_cost' => $row['estimated_unit_cost'],
                'schedule_start' => $row['schedule_start'] ?: null,
                'schedule_end' => $row['schedule_end'] ?: null,
                'mode_of_procurement_id' => $row['mode_of_procurement_id'] ?: null,
                'pre_procurement_conference' => $row['pre_procurement_conference'] ?: null,
                'fund_source_id' => $allocation?->fund_source_id,
                'pap_id' => $allocation?->pap_id,
                'uacs_code_id' => $allocation?->uacs_code_id,
                'budget_allocation_id' => $row['budget_allocation_id'],
                'remarks' => $row['remarks'],
            ];

            if (! empty($row['id'])) {
                $ppmp->items()->where('id', $row['id'])->update($payload);
                $keptIds[] = $row['id'];
            } else {
                $item = $ppmp->items()->create($payload);
                $keptIds[] = $item->id;
            }
        }

        $ppmp->items()->whereNotIn('id', $keptIds)->delete();
        $ppmp->recalculateTotal();

        session()->flash('status', 'PPMP saved as draft.');
        $this->redirect(route('ppmps.show', $ppmp), navigate: false);
    }

    public function render()
    {
        $divisions = Division::query()->orderBy('name')->get();
        $fiscalYears = FiscalYear::query()->orderByDesc('year')->get();
        $modes = ModeOfProcurement::query()->where('is_active', true)->orderBy('sort_order')->get();
        $allocations = BudgetAllocation::query()
            ->when($this->division_id, fn ($q) => $q->where('division_id', $this->division_id))
            ->when($this->fiscal_year_id, fn ($q) => $q->where('fiscal_year_id', $this->fiscal_year_id))
            ->with(['pap', 'fundSource'])
            ->get();

        return view('livewire.planning.ppmp-form', [
            'divisions' => $divisions,
            'fiscalYears' => $fiscalYears,
            'modes' => $modes,
            'allocations' => $allocations,
            'preProcurementOptions' => PreProcurementConference::options(),
        ])
            ->layout('components.layouts.app', ['title' => $this->ppmp ? 'Edit PPMP' : 'New PPMP']);
    }
}
