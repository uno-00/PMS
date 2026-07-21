<?php

namespace App\Services\Bac;

use App\Enums\NoticeOfAwardStatus;
use App\Enums\ProcurementCaseStatus;
use App\Events\Bac\NoticeOfAwardIssued;
use App\Events\Bac\NoticeToProceedIssued;
use App\Models\Bac\Procurement;
use App\Models\Procurement\NoticeOfAward;
use App\Models\Procurement\NoticeToProceed;
use App\Models\Supplier\Bidder;
use App\Models\User;

/**
 * Phases 15-16: Notice of Award and Notice to Proceed. Every document is
 * auto-numbered and, once approved_by/issued_by is stamped, the
 * responsible officer's stored digital signature image is rendered onto
 * the generated PDF (see App\Services\Reports\PdfReportService).
 */
class AwardService
{
    public function issueNoticeOfAward(Procurement $procurement, Bidder $bidder, User $approver, float $amount, ?string $remarks = null): NoticeOfAward
    {
        $noa = NoticeOfAward::query()->create([
            'procurement_id' => $procurement->id,
            'bidder_id' => $bidder->id,
            'amount' => $amount,
            'issued_at' => now(),
            'status' => NoticeOfAwardStatus::Awarded,
            'approved_by' => $approver->id,
            'remarks' => $remarks,
        ]);

        $procurement->transitionTo(ProcurementCaseStatus::Awarded, 'Notice of Award issued to '.$bidder->company_name.'.');

        NoticeOfAwardIssued::dispatch($noa);

        return $noa;
    }

    public function respond(NoticeOfAward $noa, bool $accepted, ?string $remarks = null): NoticeOfAward
    {
        $noa->transitionTo(
            $accepted ? NoticeOfAwardStatus::Accepted : NoticeOfAwardStatus::Declined,
            $remarks,
        );
        $noa->update(['responded_at' => now()]);

        return $noa->fresh();
    }

    public function issueNoticeToProceed(Procurement $procurement, User $user, string $effectivityDate, ?int $contractDurationDays = null): NoticeToProceed
    {
        $ntp = NoticeToProceed::query()->create([
            'procurement_id' => $procurement->id,
            'effectivity_date' => $effectivityDate,
            'contract_duration_days' => $contractDurationDays,
            'issued_at' => now(),
            'status' => 'issued',
            'issued_by' => $user->id,
        ]);

        $procurement->transitionTo(ProcurementCaseStatus::NtpIssued, 'Notice to Proceed issued.');

        NoticeToProceedIssued::dispatch($ntp);

        return $ntp;
    }
}
