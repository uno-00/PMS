<?php

namespace App\Livewire\Gaa;

use App\Models\Budget\GeneralAppropriationsAct;
use App\Models\Settings\FiscalYear;
use App\Services\Budget\GaaService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class Upload extends Component
{
    use WithFileUploads;

    public ?string $fiscal_year_id = null;

    public ?string $reference_no = null;

    public $file;

    public function mount(): void
    {
        Gate::authorize('upload', GeneralAppropriationsAct::class);
    }

    protected function rules(): array
    {
        return [
            'fiscal_year_id' => ['required', 'exists:fiscal_years,id'],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ];
    }

    public function save(GaaService $service): void
    {
        $this->validate();

        $fiscalYear = FiscalYear::query()->findOrFail($this->fiscal_year_id);

        $gaa = $service->upload($fiscalYear, $this->file, Auth::user(), $this->reference_no);

        session()->flash('status', 'GAA Excel template uploaded and parsed successfully. Review the line items before validating.');

        $this->redirect(route('gaa.show', $gaa), navigate: false);
    }

    public function render()
    {
        $fiscalYears = FiscalYear::query()->orderByDesc('year')->get();

        return view('livewire.gaa.upload', compact('fiscalYears'))
            ->layout('components.layouts.app', ['title' => 'Upload GAA']);
    }
}
