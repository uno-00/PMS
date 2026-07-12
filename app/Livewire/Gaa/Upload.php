<?php

namespace App\Livewire\Gaa;

use App\Livewire\Concerns\HandlesUploadErrors;
use App\Models\Budget\GeneralAppropriationsAct;
use App\Models\Settings\FiscalYear;
use App\Services\Budget\GaaService;
use App\Support\UploadLimits;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;

#[Layout('components.layouts.app')]
class Upload extends Component
{
    use HandlesUploadErrors, WithFileUploads {
        HandlesUploadErrors::_uploadErrored insteadof WithFileUploads;
    }

    public ?string $fiscal_year_id = null;

    public ?string $reference_no = null;

    public $file;

    public function mount(): void
    {
        Gate::authorize('upload', GeneralAppropriationsAct::class);
    }

    protected function rules(): array
    {
        $maxKb = UploadLimits::gaaFileMaxKilobytes();

        return [
            'fiscal_year_id' => ['required', 'exists:fiscal_years,id'],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:'.$maxKb],
        ];
    }

    public function save(GaaService $service): void
    {
        $this->validate();

        $fiscalYear = FiscalYear::query()->findOrFail($this->fiscal_year_id);

        try {
            $gaa = $service->upload($fiscalYear, $this->file, Auth::user(), $this->reference_no);
        } catch (\Throwable $e) {
            report($e);
            $this->addError('file', 'The file could not be parsed. Use the downloaded GAA Excel template and ensure the first sheet contains the required column headers.');

            return;
        }

        session()->flash('status', 'GAA Excel template uploaded and parsed successfully. Review the line items before validating.');

        $this->redirect(route('gaa.show', $gaa), navigate: false);
    }

    public function render()
    {
        $fiscalYears = FiscalYear::query()->orderByDesc('year')->get();

        return view('livewire.gaa.upload', [
            'fiscalYears' => $fiscalYears,
            'uploadLimitLabel' => UploadLimits::maxMegabytesLabel(),
            'gaaMaxKb' => UploadLimits::gaaFileMaxKilobytes(),
        ])->layout('components.layouts.app', ['title' => 'Upload GAA']);
    }
}
