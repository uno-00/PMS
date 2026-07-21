<?php

namespace App\Enums;

use App\Support\Workflow\Transitionable;

/**
 * Tracks a single BAC-handled procurement case end-to-end, from the
 * moment an approved Purchase Request is picked up by the BAC Secretariat
 * through PhilGEPS posting, bidding, award, NTP, and PO issuance
 * (Phases 7-17). Small Value Procurement / Shopping / Direct Contracting
 * cases skip the bidding-specific states and move straight to Awarded/PO.
 */
enum ProcurementCaseStatus: string implements Transitionable
{
    case Planning = 'planning';
    case Posted = 'posted';
    case Bidding = 'bidding';
    case Evaluation = 'evaluation';
    case PostQualification = 'post_qualification';
    case Awarded = 'awarded';
    case NtpIssued = 'ntp_issued';
    case PoIssued = 'po_issued';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Planning => [self::Posted, self::Awarded, self::Cancelled],
            self::Posted => [self::Bidding, self::Cancelled],
            self::Bidding => [self::Evaluation, self::Cancelled],
            self::Evaluation => [self::PostQualification, self::Cancelled],
            self::PostQualification => [self::Awarded, self::Cancelled],
            self::Awarded => [self::NtpIssued, self::Cancelled],
            self::NtpIssued => [self::PoIssued],
            self::PoIssued => [self::Completed],
            self::Completed, self::Cancelled => [],
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Planning => 'Planning',
            self::Posted => 'Posted to PhilGEPS',
            self::Bidding => 'Bidding',
            self::Evaluation => 'Bid Evaluation',
            self::PostQualification => 'Post-Qualification',
            self::Awarded => 'Awarded',
            self::NtpIssued => 'NTP Issued',
            self::PoIssued => 'PO Issued',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Planning => 'slate',
            self::Posted, self::Bidding, self::Evaluation, self::PostQualification => 'amber',
            self::Awarded, self::NtpIssued, self::PoIssued => 'indigo',
            self::Completed => 'emerald',
            self::Cancelled => 'red',
        };
    }
}
