<?php

namespace App\Enums;

enum ProjectProposalStatus: string
{
    case Draft = 'draft';
    case ForRecommendation = 'for_recommendation';
    case Approved = 'approved';
    case ReturnedForRevision = 'returned_for_revision';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::ForRecommendation => 'For Recommendation',
            self::Approved => 'Approved',
            self::ReturnedForRevision => 'Returned for Revision',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'slate',
            self::ForRecommendation => 'amber',
            self::Approved => 'emerald',
            self::ReturnedForRevision => 'red',
        };
    }
}
