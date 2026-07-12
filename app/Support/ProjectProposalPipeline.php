<?php

namespace App\Support;

use App\Enums\ProjectProposalPipelineStep;
use App\Models\Planning\ProjectProposal;

final class ProjectProposalPipeline
{
    public static function continueRoute(ProjectProposal $proposal): string
    {
        $step = $proposal->pipeline_step ?? ProjectProposalPipelineStep::ProjectProposal;

        return match ($step) {
            ProjectProposalPipelineStep::ProjectProposal => route('project-proposals.edit', $proposal),
            ProjectProposalPipelineStep::MarketScoping => route('project-proposals.wizard.market-scoping', $proposal),
            ProjectProposalPipelineStep::IndicativePpmp => route('project-proposals.wizard.indicative-ppmp', $proposal),
            ProjectProposalPipelineStep::Completed => route('project-proposals.show', $proposal),
        };
    }

    public static function continueLabel(ProjectProposal $proposal): string
    {
        $step = $proposal->pipeline_step ?? ProjectProposalPipelineStep::ProjectProposal;

        if ($step === ProjectProposalPipelineStep::Completed) {
            return 'View';
        }

        return 'Continue Step '.$step->number();
    }

    public static function isComplete(ProjectProposal $proposal): bool
    {
        return ($proposal->pipeline_step ?? ProjectProposalPipelineStep::ProjectProposal) === ProjectProposalPipelineStep::Completed;
    }
}
