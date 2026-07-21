<?php

namespace App\Livewire\Planning;

use App\Enums\ProjectProposalPipelineStep;
use App\Enums\ProjectProposalStatus;
use App\Models\Planning\ProjectProposal;
use App\Models\Settings\AgencyProfile;
use App\Models\Settings\Division;
use App\Models\Settings\FiscalYear;
use App\Services\Planning\ProjectProposalAiDraftService;
use App\Services\Planning\ProjectProposalService;
use App\Support\MonthYearSchedule;
use App\Support\RichTextSanitizer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class ProjectProposalForm extends Component
{
    public ?ProjectProposal $projectProposal = null;

    public string $fiscal_year_id = '';

    public string $division_id = '';

    public string $document_ref = 'NMP-PP-01';

    public string $with_enclosures = '';

    public string $project_type = '';

    public string $title = '';

    public string $schedule = '';

    public string $schedule_from_month = '';

    public string $schedule_from_year = '';

    public string $schedule_to_month = '';

    public string $schedule_to_year = '';

    public string $venue_area = '';

    public string $total_cost = '';

    public string $fund_source_text = '';

    public string $proponent = '';

    public string $rationale = '';

    public string $objectives = '';

    public string $target_schedule = '';

    public string $budgetary_requirement = '';

    public string $fund_source_narrative = '';

    public string $remarks = '';

    public int $focusedSection = 0;

    /** @var array<int, array{key: string, title: string, short: string}> */
    public const SECTIONS = [
        ['key' => 'basic', 'title' => 'I. Basic Information', 'short' => 'Basic'],
        ['key' => 'rationale', 'title' => 'II. Rationale', 'short' => 'Rationale'],
        ['key' => 'objectives', 'title' => 'III. Objectives', 'short' => 'Objectives'],
        ['key' => 'target_schedule', 'title' => 'IV. Target Schedule', 'short' => 'Schedule'],
        ['key' => 'budgetary', 'title' => 'V. Budgetary Requirement', 'short' => 'Budget'],
        ['key' => 'fund_source', 'title' => 'VI. Fund Source', 'short' => 'Fund Source'],
    ];

    public function focusSection(int $index): void
    {
        if ($index >= 0 && $index < count(self::SECTIONS)) {
            $this->focusedSection = $index;
        }
    }

    public function nextSection(): void
    {
        $this->focusSection(min($this->focusedSection + 1, count(self::SECTIONS) - 1));
    }

    public function previousSection(): void
    {
        $this->focusSection(max($this->focusedSection - 1, 0));
    }

    public function getFormattedTotalCostProperty(): string
    {
        $amount = (float) ($this->total_cost ?: 0);

        return '₱'.number_format($amount, 2);
    }

    public function mount(?ProjectProposal $projectProposal = null): void
    {
        if ($projectProposal && $projectProposal->exists) {
            Gate::authorize('update', $projectProposal);
            $this->projectProposal = $projectProposal;
            $this->fillFromModel($projectProposal);
        } else {
            Gate::authorize('create', ProjectProposal::class);
            $user = Auth::user();
            $this->fiscal_year_id = FiscalYear::query()->where('is_current', true)->value('id') ?? '';
            $this->division_id = $user->division_id ?? '';
        }

        $this->syncProponentFromAuth();
    }

    protected function syncProponentFromAuth(): void
    {
        $this->proponent = Auth::user()?->name ?? '';
    }

    protected function fillFromModel(ProjectProposal $record): void
    {
        $this->fiscal_year_id = $record->fiscal_year_id ?? '';
        $this->division_id = $record->division_id;
        $this->document_ref = $record->document_ref ?? 'NMP-PP-01';
        $this->with_enclosures = $record->with_enclosures ?? '';
        $this->project_type = $record->project_type ?? '';
        $this->title = $record->title;
        $this->schedule = $record->schedule ?? '';
        $this->fillSchedulePickersFromSchedule();
        $this->venue_area = $record->venue_area ?? '';
        $this->total_cost = (string) $record->total_cost;
        $this->fund_source_text = $record->fund_source_text ?? '';
        $this->proponent = $record->proponent ?? '';
        $this->rationale = $record->rationale ?? '';
        $this->objectives = $record->objectives ?? '';
        $this->target_schedule = $record->target_schedule ?? '';
        $this->budgetary_requirement = $record->budgetary_requirement ?? '';
        $this->fund_source_narrative = $record->fund_source_narrative ?? '';
        $this->remarks = $record->remarks ?? '';
    }

    public function updated($property): void
    {
        if (in_array($property, [
            'schedule_from_month',
            'schedule_from_year',
            'schedule_to_month',
            'schedule_to_year',
        ], true)) {
            $this->syncScheduleFromPickers();
        }
    }

    protected function syncScheduleFromPickers(): void
    {
        $this->schedule = MonthYearSchedule::format(
            $this->schedule_from_month,
            $this->schedule_from_year,
            $this->schedule_to_month,
            $this->schedule_to_year,
        ) ?? '';
    }

    protected function fillSchedulePickersFromSchedule(): void
    {
        $parsed = MonthYearSchedule::parse($this->schedule);

        $this->schedule_from_month = $parsed['from_month'] ? (string) $parsed['from_month'] : '';
        $this->schedule_from_year = $parsed['from_year'] ? (string) $parsed['from_year'] : '';
        $this->schedule_to_month = $parsed['to_month'] ? (string) $parsed['to_month'] : '';
        $this->schedule_to_year = $parsed['to_year'] ? (string) $parsed['to_year'] : '';
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            MonthYearSchedule::validateRange(
                $this->schedule_from_month,
                $this->schedule_from_year,
                $this->schedule_to_month,
                $this->schedule_to_year,
                fn (string $message) => $validator->errors()->add('schedule_from_month', $message),
            );
        });
    }

    protected function rules(): array
    {
        return [
            'fiscal_year_id' => ['nullable', 'exists:fiscal_years,id'],
            'division_id' => ['required', 'exists:divisions,id'],
            'document_ref' => ['nullable', 'string', 'max:50'],
            'with_enclosures' => ['nullable', 'string', 'max:255'],
            'project_type' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'schedule_from_month' => ['nullable', 'integer', 'between:1,12'],
            'schedule_from_year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'schedule_to_month' => ['nullable', 'integer', 'between:1,12'],
            'schedule_to_year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'venue_area' => ['nullable', 'string', 'max:255'],
            'total_cost' => ['required', 'numeric', 'min:0'],
            'fund_source_text' => ['nullable', 'string', 'max:255'],
            'proponent' => ['nullable', 'string', 'max:255'],
            'rationale' => ['nullable', 'string'],
            'objectives' => ['nullable', 'string'],
            'target_schedule' => ['nullable', 'string'],
            'budgetary_requirement' => ['nullable', 'string'],
            'fund_source_narrative' => ['nullable', 'string'],
            'remarks' => ['nullable', 'string'],
        ];
    }

    protected function payload(): array
    {
        return [
            'fiscal_year_id' => $this->fiscal_year_id ?: null,
            'division_id' => $this->division_id,
            'document_ref' => $this->document_ref ?: 'NMP-PP-01',
            'with_enclosures' => $this->with_enclosures ?: null,
            'project_type' => $this->project_type,
            'title' => $this->title,
            'schedule' => $this->schedule ?: null,
            'venue_area' => $this->venue_area ?: null,
            'total_cost' => $this->total_cost,
            'fund_source_text' => $this->fund_source_text ?: null,
            'proponent' => $this->proponent ?: null,
            'rationale' => RichTextSanitizer::clean($this->rationale),
            'objectives' => RichTextSanitizer::clean($this->objectives),
            'target_schedule' => RichTextSanitizer::clean($this->target_schedule),
            'budgetary_requirement' => RichTextSanitizer::clean($this->budgetary_requirement),
            'fund_source_narrative' => RichTextSanitizer::clean($this->fund_source_narrative),
            'remarks' => $this->remarks ?: null,
        ];
    }

    public function save(): void
    {
        $this->syncScheduleFromPickers();
        $this->validate();
        $record = $this->persistProposal();
        session()->flash('status', 'Project Proposal saved.');
        $this->redirect(route('project-proposals.show', $record), navigate: false);
    }

    public function saveAndContinue(ProjectProposalService $service): void
    {
        $this->syncScheduleFromPickers();
        $this->validate();
        $record = $this->persistProposal();
        $service->advanceToMarketScoping($record);
        session()->flash('status', 'Step 1 complete. Proceed to Market Scoping.');
        $this->redirect(route('project-proposals.wizard.market-scoping', $record), navigate: false);
    }

    public function draftAllNarrativeSections(ProjectProposalAiDraftService $ai): void
    {
        $this->prepareAiDraft($ai);

        try {
            $drafts = $ai->draftAll($this->aiContext());
            $this->applyAiDrafts($drafts);
            $this->focusedSection = 1;
            session()->flash('status', $this->aiFlashMessage($ai, 'Sections II–VI were drafted and filled in below.'));
        } catch (\Throwable $e) {
            $this->addError('ai', $e->getMessage());
        }
    }

    public function draftSectionWithAi(string $section, ProjectProposalAiDraftService $ai): void
    {
        $this->prepareAiDraft($ai);

        try {
            $html = $ai->draftSection($section, $this->aiContext());
            $this->{$section} = $html;
            $this->dispatch('rich-text-sync', drafts: [$section => $html]);
            session()->flash('status', $this->aiFlashMessage($ai, str_replace('_', ' ', $section).' drafted.'));
        } catch (\Throwable $e) {
            $this->addError('ai', $e->getMessage());
        }
    }

    protected function prepareAiDraft(ProjectProposalAiDraftService $ai): void
    {
        $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'project_type' => ['required', 'string', 'max:255'],
            'division_id' => ['required', 'exists:divisions,id'],
            'total_cost' => ['required', 'numeric', 'min:0'],
        ]);
    }

    /** @return array<string, mixed> */
    protected function aiContext(): array
    {
        $division = Division::query()->find($this->division_id);
        $fiscalYear = $this->fiscal_year_id
            ? FiscalYear::query()->find($this->fiscal_year_id)
            : null;

        return [
            'agency_name' => AgencyProfile::current()->displayName(),
            'division_name' => $division?->name,
            'fiscal_year' => $fiscalYear?->year,
            'title' => $this->title,
            'project_type' => $this->project_type,
            'schedule' => $this->schedule,
            'venue_area' => $this->venue_area,
            'total_cost' => $this->total_cost,
            'fund_source_text' => $this->fund_source_text,
            'proponent' => $this->proponent,
        ];
    }

    /** @param  array<string, string>  $drafts */
    protected function applyAiDrafts(array $drafts): void
    {
        foreach ($drafts as $field => $html) {
            $this->{$field} = $html;
        }

        $this->dispatch('rich-text-sync', drafts: $drafts);
    }

    protected function aiFlashMessage(ProjectProposalAiDraftService $ai, string $suffix): string
    {
        if ($ai->usedTemplateFallback()) {
            return "Smart template (OpenAI unavailable): {$suffix} Drafts were generated locally. Check internet, billing, or restart the dev server.";
        }

        $mode = $ai->isAiConfigured() ? 'AI' : 'Smart template';

        return "{$mode}: {$suffix} Review and edit before saving.";
    }

    protected function persistProposal(): ProjectProposal
    {
        $payload = $this->payload();

        if ($this->projectProposal) {
            $this->projectProposal->update($payload);

            return $this->projectProposal->fresh();
        }

        return ProjectProposal::query()->create(array_merge($payload, [
            'status' => ProjectProposalStatus::Draft,
            'pipeline_step' => ProjectProposalPipelineStep::ProjectProposal,
            'prepared_by' => Auth::id(),
        ]));
    }

    public function render()
    {
        $fiscalYears = FiscalYear::query()->orderByDesc('year')->get();
        $selectedFiscalYear = $this->fiscal_year_id
            ? $fiscalYears->firstWhere('id', $this->fiscal_year_id)
            : null;

        return view('livewire.planning.project-proposal-form', [
            'divisions' => Division::query()->orderBy('name')->get(),
            'fiscalYears' => $fiscalYears,
            'scheduleYearOptions' => MonthYearSchedule::yearOptions(
                $selectedFiscalYear ? (int) $selectedFiscalYear->year : null,
            ),
            'pipelineStep' => $this->projectProposal?->pipeline_step ?? ProjectProposalPipelineStep::ProjectProposal,
            'aiConfigured' => app(ProjectProposalAiDraftService::class)->isAiConfigured(),
        ])->layout('components.layouts.app', [
            'title' => $this->projectProposal ? 'Step 1: Project Proposal' : 'Step 1: New Project Proposal',
        ]);
    }
}
