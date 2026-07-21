<?php

namespace App\Livewire\Planning;

use App\Enums\MarketScopingStatus;
use App\Enums\ProjectProposalPipelineStep;
use App\Models\Planning\MarketScoping;
use App\Models\Planning\ProjectProposal;
use App\Models\Settings\AgencyProfile;
use App\Models\Settings\Division;
use App\Models\Settings\FiscalYear;
use App\Services\Planning\ProjectProposalService;
use App\Support\MarketScopingPrintFormatter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class ProjectProposalWizardMarketScoping extends Component
{
    public ProjectProposal $projectProposal;

    public ?MarketScoping $marketScoping = null;

    public string $fiscal_year_id = '';

    public string $division_id = '';

    public string $procuring_entity = '';

    public string $end_user_unit = '';

    public string $representative_name = '';

    public string $representative_designation = '';

    public string $project_name = '';

    public string $estimated_budget = '';

    public string $period_from = '';

    public string $period_to = '';

    public string $expected_delivery = '';

    public array $activities = [];

    public array $parameters = [];

    public string $remarks = '';

    public function mount(ProjectProposal $projectProposal): void
    {
        Gate::authorize('update', $projectProposal);
        abort_unless(
            in_array($projectProposal->pipeline_step, [
                ProjectProposalPipelineStep::MarketScoping,
                ProjectProposalPipelineStep::IndicativePpmp,
                ProjectProposalPipelineStep::Completed,
            ], true),
            403,
            'Complete Step 1: Project Proposal first.'
        );

        $this->projectProposal = $projectProposal->load('marketScoping');
        $this->initializeStructures();

        if ($this->projectProposal->marketScoping) {
            Gate::authorize('update', $this->projectProposal->marketScoping);
            $this->marketScoping = $this->projectProposal->marketScoping;
            $this->fillFromModel($this->marketScoping);
        } else {
            $this->prefillFromProjectProposal($this->projectProposal);
        }
    }

    protected function initializeStructures(): void
    {
        foreach (MarketScopingPrintFormatter::activityDefinitions() as $activity) {
            $this->activities[$activity['key']] = [
                'checked' => false,
                'documentation' => '',
                'description' => '',
            ];
        }

        foreach (MarketScopingPrintFormatter::parameterDefinitions() as $parameter) {
            $this->parameters[$parameter['key']] = [
                'considered' => '',
                'recommendations' => '',
            ];
        }
    }

    protected function prefillFromProjectProposal(ProjectProposal $proposal): void
    {
        $agency = AgencyProfile::current();
        $user = Auth::user();

        $this->fiscal_year_id = $proposal->fiscal_year_id ?? '';
        $this->division_id = $proposal->division_id;
        $this->procuring_entity = $agency->displayName();
        $this->end_user_unit = $proposal->venue_area ?: $user->division?->name ?? '';
        $this->representative_name = $proposal->proponent ?: $user->name ?? '';
        $this->representative_designation = $user->position ?? '';
        $this->project_name = $proposal->title;
        $this->estimated_budget = (string) $proposal->total_cost;
    }

    protected function fillFromModel(MarketScoping $record): void
    {
        $this->fiscal_year_id = $record->fiscal_year_id ?? '';
        $this->division_id = $record->division_id;
        $this->procuring_entity = $record->procuring_entity ?? '';
        $this->end_user_unit = $record->end_user_unit ?? '';
        $this->representative_name = $record->representative_name ?? '';
        $this->representative_designation = $record->representative_designation ?? '';
        $this->project_name = $record->project_name;
        $this->estimated_budget = (string) $record->estimated_budget;
        $this->period_from = optional($record->period_from)->format('Y-m-d') ?? '';
        $this->period_to = optional($record->period_to)->format('Y-m-d') ?? '';
        $this->expected_delivery = optional($record->expected_delivery)->format('Y-m-d') ?? '';
        $this->remarks = $record->remarks ?? '';
        $this->activities = array_replace_recursive($this->activities, $record->activities ?? []);
        $this->parameters = array_replace_recursive($this->parameters, $record->parameters ?? []);
    }

    protected function rules(): array
    {
        return [
            'project_name' => ['required', 'string', 'max:255'],
            'fiscal_year_id' => ['nullable', 'exists:fiscal_years,id'],
            'division_id' => ['required', 'exists:divisions,id'],
            'procuring_entity' => ['nullable', 'string', 'max:255'],
            'end_user_unit' => ['nullable', 'string', 'max:255'],
            'representative_name' => ['nullable', 'string', 'max:255'],
            'representative_designation' => ['nullable', 'string', 'max:255'],
            'estimated_budget' => ['required', 'numeric', 'min:0'],
            'period_from' => ['nullable', 'date'],
            'period_to' => ['nullable', 'date', 'after_or_equal:period_from'],
            'expected_delivery' => ['nullable', 'date'],
            'activities.*.checked' => ['boolean'],
            'activities.*.documentation' => ['nullable', 'string'],
            'activities.other.description' => ['nullable', 'string', 'max:255'],
            'parameters.*.considered' => ['nullable', 'in:yes,no,na'],
            'parameters.*.recommendations' => ['nullable', 'string'],
            'remarks' => ['nullable', 'string'],
        ];
    }

    protected function persistMarketScoping(): MarketScoping
    {
        $payload = [
            'project_proposal_id' => $this->projectProposal->id,
            'fiscal_year_id' => $this->fiscal_year_id ?: null,
            'division_id' => $this->division_id,
            'procuring_entity' => $this->procuring_entity,
            'end_user_unit' => $this->end_user_unit,
            'representative_name' => $this->representative_name,
            'representative_designation' => $this->representative_designation,
            'project_name' => $this->project_name,
            'estimated_budget' => $this->estimated_budget,
            'period_from' => $this->period_from ?: null,
            'period_to' => $this->period_to ?: null,
            'expected_delivery' => $this->expected_delivery ?: null,
            'activities' => $this->activities,
            'parameters' => $this->parameters,
            'remarks' => $this->remarks,
        ];

        if ($this->marketScoping) {
            $this->marketScoping->update($payload);

            return $this->marketScoping->fresh();
        }

        return MarketScoping::query()->create(array_merge($payload, [
            'status' => MarketScopingStatus::Draft,
            'prepared_by' => Auth::id(),
        ]));
    }

    public function save(): void
    {
        $this->validate();
        $this->marketScoping = $this->persistMarketScoping();
        session()->flash('status', 'Market Scoping Checklist saved.');
    }

    public function saveAndContinue(ProjectProposalService $service): void
    {
        $this->validate();
        $marketScoping = $this->persistMarketScoping();
        $service->linkMarketScoping($this->projectProposal, $marketScoping);

        $this->projectProposal->update([
            'with_enclosures' => $this->projectProposal->with_enclosures ?: 'Market Scoping Checklist '.$marketScoping->control_no,
        ]);

        session()->flash('status', 'Step 2 complete. Proceed to Indicative PPMP generation.');
        $this->redirect(route('project-proposals.wizard.indicative-ppmp', $this->projectProposal), navigate: false);
    }

    public function render()
    {
        return view('livewire.planning.project-proposal-wizard-market-scoping', [
            'divisions' => Division::query()->orderBy('name')->get(),
            'fiscalYears' => FiscalYear::query()->orderByDesc('year')->get(),
            'activityDefinitions' => MarketScopingPrintFormatter::activityDefinitions(),
            'parameterDefinitions' => MarketScopingPrintFormatter::parameterDefinitions(),
            'consideredOptions' => \App\Enums\MarketScopingConsidered::options(),
        ])->layout('components.layouts.app', [
            'title' => 'Step 2: Market Scoping',
        ]);
    }
}
