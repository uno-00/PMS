<?php

namespace App\Livewire\Bac;

use App\Models\Bac\PhilgepsPosting;
use App\Models\Bac\Procurement;
use App\Services\Bac\PhilgepsPostingService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class PhilgepsForm extends Component
{
    public ?PhilgepsPosting $posting = null;

    public string $procurement_id = '';

    public string $reference_no = '';

    public string $posting_date = '';

    public string $closing_date = '';

    public string $remarks = '';

    public function mount(?PhilgepsPosting $posting = null): void
    {
        if ($posting && $posting->exists) {
            Gate::authorize('update', $posting);
            $this->posting = $posting;
            $this->fillFromModel($posting);
        } else {
            Gate::authorize('create', PhilgepsPosting::class);
            $this->posting_date = now()->format('Y-m-d');
            $this->closing_date = now()->addDays(7)->format('Y-m-d');
        }
    }

    protected function fillFromModel(PhilgepsPosting $posting): void
    {
        $this->procurement_id = $posting->procurement_id ?? '';
        $this->reference_no = $posting->reference_no ?? '';
        $this->posting_date = $posting->posting_date?->format('Y-m-d') ?? '';
        $this->closing_date = $posting->closing_date?->format('Y-m-d') ?? '';
        $this->remarks = $posting->remarks ?? '';
    }

    protected function rules(): array
    {
        return [
            'procurement_id' => ['required', 'exists:procurements,id'],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'posting_date' => ['required', 'date'],
            'closing_date' => ['required', 'date', 'after_or_equal:posting_date'],
            'remarks' => ['nullable', 'string'],
        ];
    }

    public function save(PhilgepsPostingService $service): void
    {
        $this->validate();

        $attributes = [
            'reference_no' => $this->reference_no ?: null,
            'posting_date' => $this->posting_date,
            'closing_date' => $this->closing_date,
            'remarks' => $this->remarks,
        ];

        if ($this->posting && $this->posting->exists) {
            $service->update($this->posting, $attributes);
            $record = $this->posting;
        } else {
            $procurement = Procurement::query()->findOrFail($this->procurement_id);

            if ($procurement->philgepsPosting()->exists()) {
                $this->addError('procurement_id', 'This procurement already has a PhilGEPS posting.');

                return;
            }

            $record = $service->createManual($procurement, $attributes, Auth::user());
        }

        session()->flash('status', 'PhilGEPS posting saved.');
        $this->redirect(route('philgeps.show', $record), navigate: false);
    }

    public function render()
    {
        // On create, only offer procurements that don't yet have a posting.
        $procurements = Procurement::query()
            ->with('modeOfProcurement')
            ->when(! $this->posting || ! $this->posting->exists, function ($q) {
                $q->whereDoesntHave('philgepsPosting');
            })
            ->orderByDesc('created_at')
            ->limit(200)
            ->get();

        return view('livewire.bac.philgeps-form', [
            'procurements' => $procurements,
        ])->layout('components.layouts.app', [
            'title' => $this->posting && $this->posting->exists ? 'Edit PhilGEPS Posting' : 'New Manual PhilGEPS Posting',
        ]);
    }
}
