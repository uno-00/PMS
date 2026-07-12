<?php

namespace App\Enums;

enum ProjectProposalPipelineStep: string
{
    case ProjectProposal = 'project_proposal';
    case MarketScoping = 'market_scoping';
    case IndicativePpmp = 'indicative_ppmp';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::ProjectProposal => 'Project Proposal',
            self::MarketScoping => 'Market Scoping',
            self::IndicativePpmp => 'Indicative PPMP',
            self::Completed => 'Completed',
        };
    }

    public function number(): int
    {
        return match ($this) {
            self::ProjectProposal => 1,
            self::MarketScoping => 2,
            self::IndicativePpmp => 3,
            self::Completed => 4,
        };
    }

    /** @return array<int, array{step: self, title: string, description: string}> */
    public static function wizardSteps(): array
    {
        return [
            [
                'step' => self::ProjectProposal,
                'title' => 'Project Proposal',
                'description' => 'Complete NMP-PP-01 project proposal form',
            ],
            [
                'step' => self::MarketScoping,
                'title' => 'Market Scoping',
                'description' => 'Fill up the market scoping checklist',
            ],
            [
                'step' => self::IndicativePpmp,
                'title' => 'Indicative PPMP',
                'description' => 'Generate indicative PPMP from proposal data',
            ],
        ];
    }
}
