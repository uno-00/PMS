<?php

namespace App\Services\Bac;

use App\Models\Bac\BidEvaluation;
use App\Models\Bac\BidOpening;
use App\Models\Bac\BidSubmission;
use App\Models\Bac\PostQualification;
use App\Models\Bac\Procurement;
use App\Models\Supplier\Bidder;
use App\Models\User;
use App\Services\Support\DocumentStorageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Phases 11-14: electronic bid submission (encrypted-at-rest on the
 * private document disk, deadline-locked), opening, evaluation, and
 * post-qualification.
 */
class BiddingService
{
    public function __construct(protected DocumentStorageService $documents) {}

    /**
     * "Late submission disabled": once the PhilGEPS posting's closing_date
     * has passed, no further bid version can be submitted for this
     * procurement, full stop.
     */
    public function submitBid(
        Procurement $procurement,
        Bidder $bidder,
        User $user,
        ?UploadedFile $technical = null,
        ?UploadedFile $financial = null,
        ?UploadedFile $eligibility = null,
    ): BidSubmission {
        $posting = $procurement->philgepsPosting;
        abort_if(! $posting, 422, 'This procurement has not been posted to PhilGEPS yet.');
        abort_if(now()->gt($posting->closing_date->endOfDay()), 423, 'The bid submission deadline has passed. Late submissions are disabled.');

        return DB::transaction(function () use ($procurement, $bidder, $user, $technical, $financial, $eligibility) {
            $nextVersion = 1 + (int) BidSubmission::query()
                ->where('procurement_id', $procurement->id)
                ->where('bidder_id', $bidder->id)
                ->max('version');

            $bid = BidSubmission::query()->create([
                'procurement_id' => $procurement->id,
                'bidder_id' => $bidder->id,
                'version' => $nextVersion,
                'submitted_at' => now(),
                'is_late' => false,
                'status' => 'submitted',
            ]);

            foreach (['technical' => $technical, 'financial' => $financial, 'eligibility' => $eligibility] as $category => $file) {
                if ($file) {
                    $this->documents->store($file, $bid, 'bids', "{$category}-document", $user);
                }
            }

            return $bid;
        });
    }

    public function openBids(Procurement $procurement, User $user, array $checklist = [], array $attendance = []): BidOpening
    {
        $opening = BidOpening::query()->updateOrCreate(
            ['procurement_id' => $procurement->id],
            ['opened_at' => now(), 'opened_by' => $user->id, 'checklist' => $checklist, 'attendance' => $attendance]
        );

        $procurement->bidSubmissions()->update(['status' => 'opened']);

        return $opening;
    }

    public function evaluate(BidSubmission $bid, User $evaluator, array $compliance, float $score, ?int $rank, string $recommendation, ?string $remarks = null): BidEvaluation
    {
        return BidEvaluation::query()->updateOrCreate(
            ['bid_submission_id' => $bid->id, 'evaluator_id' => $evaluator->id],
            compact('compliance', 'score', 'rank', 'recommendation', 'remarks')
        );
    }

    public function processPostQualification(
        Procurement $procurement,
        Bidder $bidder,
        User $user,
        bool $siteVisitConducted,
        string $notes,
        string $result,
    ): PostQualification {
        return PostQualification::query()->create([
            'procurement_id' => $procurement->id,
            'bidder_id' => $bidder->id,
            'site_visit_conducted' => $siteVisitConducted,
            'document_validation_notes' => $notes,
            'result' => $result,
            'processed_by' => $user->id,
            'processed_at' => now(),
        ]);
    }
}
