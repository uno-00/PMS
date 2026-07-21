<?php

namespace App\Livewire\PurchaseRequest;

use App\Enums\PpmpStatus;
use App\Enums\PurchaseRequestStatus;
use App\Exceptions\BudgetExceededException;
use App\Models\Planning\Ppmp;
use App\Models\Planning\PpmpItem;
use App\Models\Procurement\PurchaseRequest;
use App\Models\Settings\Division;
use App\Models\Settings\FiscalYear;
use App\Services\Procurement\PurchaseRequestService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class PurchaseRequestForm extends Component
{
    public ?PurchaseRequest $purchaseRequest = null;

    public string $fiscal_year_id = '';

    public string $division_id = '';

    public string $ppmp_id = '';

    public string $purpose = '';

    public array $lines = [];

    public function mount(?PurchaseRequest $purchase_request = null): void
    {
        if ($purchase_request && $purchase_request->exists) {
            Gate::authorize('update', $purchase_request);
            $this->purchaseRequest = $purchase_request;
            $this->fiscal_year_id = $purchase_request->fiscal_year_id;
            $this->division_id = $purchase_request->division_id;
            $this->ppmp_id = $purchase_request->ppmp_id;
            $this->purpose = $purchase_request->purpose;
        } else {
            Gate::authorize('create', PurchaseRequest::class);
            $user = Auth::user();
            $this->fiscal_year_id = FiscalYear::query()->where('is_current', true)->value('id') ?? '';
            $this->division_id = $user->division_id ?? '';
        }
    }

    public function updatedPpmpId(): void
    {
        $this->lines = [];
    }

    public function addLine(string $ppmpItemId): void
    {
        if (collect($this->lines)->pluck('ppmp_item_id')->contains($ppmpItemId)) {
            return;
        }

        $item = PpmpItem::query()->find($ppmpItemId);

        if (! $item) {
            return;
        }

        $service = app(PurchaseRequestService::class);
        $available = $service->availableForPpmpItem($item, $this->purchaseRequest?->id);

        $this->lines[] = [
            'ppmp_item_id' => $item->id,
            'item_name' => $item->item_name,
            'unit' => $item->unit,
            'unit_cost' => (string) $item->estimated_unit_cost,
            'available' => $available,
            'quantity' => $available > 0 ? min((float) $item->quantity, floor($available / max((float) $item->estimated_unit_cost, 0.01))) : 0,
        ];
    }

    public function removeLine(int $index): void
    {
        unset($this->lines[$index]);
        $this->lines = array_values($this->lines);
    }

    public function save(bool $submit = false): void
    {
        $this->validate([
            'fiscal_year_id' => ['required', 'exists:fiscal_years,id'],
            'division_id' => ['required', 'exists:divisions,id'],
            'ppmp_id' => ['required', 'exists:ppmps,id'],
            'purpose' => ['required', 'string', 'max:1000'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'lines.*.unit_cost' => ['required', 'numeric', 'min:0.01'],
        ]);

        $service = app(PurchaseRequestService::class);

        if (! $this->purchaseRequest) {
            $this->purchaseRequest = PurchaseRequest::query()->create([
                'fiscal_year_id' => $this->fiscal_year_id,
                'division_id' => $this->division_id,
                'ppmp_id' => $this->ppmp_id,
                'purpose' => $this->purpose,
                'status' => PurchaseRequestStatus::Draft,
                'requested_by' => Auth::id(),
            ]);
        } else {
            $this->purchaseRequest->update(['purpose' => $this->purpose]);
            $this->purchaseRequest->items()->delete();
        }

        try {
            foreach ($this->lines as $line) {
                $item = PpmpItem::query()->findOrFail($line['ppmp_item_id']);
                $service->addItem($this->purchaseRequest, $item, (float) $line['quantity'], (float) $line['unit_cost']);
            }
        } catch (BudgetExceededException $e) {
            $this->addError('lines', $e->getMessage());

            return;
        }

        if ($submit) {
            $service->submit($this->purchaseRequest, Auth::user());
            session()->flash('status', 'Purchase Request submitted for Division Chief review.');
        } else {
            session()->flash('status', 'Purchase Request saved as draft.');
        }

        $this->redirect(route('purchase-requests.show', $this->purchaseRequest), navigate: false);
    }

    public function render()
    {
        $fiscalYears = FiscalYear::query()->orderByDesc('year')->get();
        $divisions = Division::query()->orderBy('name')->get();

        $ppmps = Ppmp::query()
            ->where('fiscal_year_id', $this->fiscal_year_id)
            ->where('division_id', $this->division_id)
            ->whereIn('status', [PpmpStatus::Approved, PpmpStatus::Locked])
            ->get();

        $availableItems = collect();

        if ($this->ppmp_id) {
            $service = app(PurchaseRequestService::class);
            $ppmpItems = PpmpItem::query()->where('ppmp_id', $this->ppmp_id)->get();
            $availableItems = $ppmpItems->map(function ($item) use ($service) {
                $item->setAttribute('available_balance', $service->availableForPpmpItem($item, $this->purchaseRequest?->id));

                return $item;
            })->filter(fn ($item) => $item->available_balance > 0)->values();
        }

        return view('livewire.purchase-request.purchase-request-form', compact('fiscalYears', 'divisions', 'ppmps', 'availableItems'))
            ->layout('components.layouts.app', ['title' => $this->purchaseRequest ? 'Edit Purchase Request' : 'New Purchase Request']);
    }
}
