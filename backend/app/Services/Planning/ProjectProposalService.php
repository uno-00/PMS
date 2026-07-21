<?php

namespace App\Services\Planning;

use App\Enums\MarketScopingStatus;
use App\Enums\ProjectProposalPipelineStep;
use App\Enums\ProjectProposalStatus;
use App\Models\Planning\MarketScoping;
use App\Models\Planning\ProjectProposal;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ProjectProposalService
{
    public function __construct(protected PpmpService $ppmpService) {}

    public function advanceToMarketScoping(ProjectProposal $proposal): ProjectProposal
    {
        $proposal->update(['pipeline_step' => ProjectProposalPipelineStep::MarketScoping]);

        return $proposal->fresh();
    }

    public function linkMarketScoping(ProjectProposal $proposal, MarketScoping $marketScoping): ProjectProposal
    {
        return DB::transaction(function () use ($proposal, $marketScoping) {
            $marketScoping->update(['project_proposal_id' => $proposal->id]);
            $proposal->update([
                'market_scoping_id' => $marketScoping->id,
                'pipeline_step' => ProjectProposalPipelineStep::IndicativePpmp,
            ]);

            return $proposal->fresh(['marketScoping']);
        });
    }

    public function generateIndicativePpmp(ProjectProposal $proposal, User $user): ProjectProposal
    {
        abort_if($proposal->market_scoping_id === null, 422, 'Complete Market Scoping before generating the Indicative PPMP.');
        abort_if($proposal->pipeline_step !== ProjectProposalPipelineStep::IndicativePpmp, 422, 'Pipeline is not ready for Indicative PPMP generation.');

        return DB::transaction(function () use ($proposal, $user) {
            if (! $proposal->hasGeneratedPpmp()) {
                $ppmp = $this->ppmpService->generateFromProjectProposal($proposal->fresh(), $user);
                $proposal->update([
                    'ppmp_id' => $ppmp->id,
                    'pipeline_step' => ProjectProposalPipelineStep::Completed,
                ]);
            } else {
                $proposal->update(['pipeline_step' => ProjectProposalPipelineStep::Completed]);
            }

            return $proposal->fresh(['ppmp', 'marketScoping']);
        });
    }

    public function submitForRecommendation(ProjectProposal $proposal, User $user): ProjectProposal
    {
        abort_if(! $proposal->isEditable(), 422, 'Project Proposal is not editable.');
        abort_unless(
            ($proposal->pipeline_step ?? ProjectProposalPipelineStep::ProjectProposal) === ProjectProposalPipelineStep::Completed,
            422,
            'Complete the three-step pipeline before submitting for recommendation.'
        );

        $proposal->update([
            'prepared_by' => $proposal->prepared_by ?? $user->id,
            'submitted_at' => now(),
            'status' => ProjectProposalStatus::ForRecommendation,
        ]);

        return $proposal->fresh();
    }

    public function recommend(ProjectProposal $proposal, User $user, ?string $remarks = null): ProjectProposal
    {
        $proposal->update([
            'recommended_by' => $user->id,
            'recommended_at' => now(),
            'remarks' => $remarks ?? $proposal->remarks,
        ]);

        return $proposal->fresh();
    }

    public function approve(ProjectProposal $proposal, User $user, ?string $remarks = null): ProjectProposal
    {
        $proposal->update([
            'status' => ProjectProposalStatus::Approved,
            'approved_by' => $user->id,
            'approved_at' => now(),
            'remarks' => $remarks ?? $proposal->remarks,
        ]);

        return $proposal->fresh(['ppmp']);
    }

    public function returnForRevision(ProjectProposal $proposal, User $user, string $remarks): ProjectProposal
    {
        $proposal->update([
            'status' => ProjectProposalStatus::ReturnedForRevision,
            'pipeline_step' => ProjectProposalPipelineStep::ProjectProposal,
            'remarks' => $remarks,
        ]);

        return $proposal->fresh();
    }

    /** @return array<string, mixed> */
    public function indicativePpmpPreview(ProjectProposal $proposal): array
    {
        $proposal->loadMissing('marketScoping', 'division', 'fiscalYear');

        return [
            'title' => $proposal->title,
            'document_type' => 'Indicative PPMP',
            'fiscal_year' => $proposal->fiscalYear?->year,
            'division' => $proposal->division?->name,
            'total_abc' => $proposal->total_cost,
            'item_name' => $proposal->title,
            'description' => $proposal->rationale,
            'specification' => $proposal->objectives,
            'unit' => 'Lot',
            'quantity' => 1,
            'estimated_unit_cost' => $proposal->total_cost,
            'market_scoping_control_no' => $proposal->marketScoping?->control_no,
            'project_proposal_control_no' => $proposal->control_no,
            'remarks' => $proposal->budgetary_requirement,
        ];
    }
}
