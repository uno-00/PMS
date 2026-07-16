<?php

namespace App\Enums;

enum PpmpConsolidationStep: int
{
    case SelectPpmps = 1;
    case GenerateConsolidated = 2;
    case GenerateBp2020 = 3;
    case GenerateWfp = 4;
    case ReviewValidation = 5;
    case ApprovalWorkflow = 6;
    case FinalConsolidation = 7;

    public function label(): string
    {
        return match ($this) {
            self::SelectPpmps => 'Select PPMPs',
            self::GenerateConsolidated => 'Consolidated PPMP',
            self::GenerateBp2020 => 'BP Form 2020',
            self::GenerateWfp => 'Work & Financial Plan',
            self::ReviewValidation => 'Review & Validation',
            self::ApprovalWorkflow => 'Approval Workflow',
            self::FinalConsolidation => 'Final Consolidation',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::SelectPpmps => 'Choose approved Indicative or Final PPMPs from requesting units.',
            self::GenerateConsolidated => 'Merge procurement items into an agency-wide PPMP.',
            self::GenerateBp2020 => 'Generate DBM Budget Preparation Form 2020.',
            self::GenerateWfp => 'Generate the Work and Financial Plan.',
            self::ReviewValidation => 'Validate budgets, schedules, and procurement data.',
            self::ApprovalWorkflow => 'Route through Planning, Budget, Accounting, BAC, and HoPE.',
            self::FinalConsolidation => 'Release final locked documents.',
        };
    }

    /** @return array<int, array{step: self, title: string, description: string}> */
    public static function wizardSteps(): array
    {
        return collect(self::cases())
            ->map(fn (self $step) => [
                'step' => $step,
                'title' => $step->label(),
                'description' => $step->description(),
            ])
            ->all();
    }
}
