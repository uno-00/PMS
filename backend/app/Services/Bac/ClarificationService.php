<?php

namespace App\Services\Bac;

use App\Models\Bac\BidClarification;
use App\Models\Bac\Procurement;
use App\Models\Supplier\Bidder;
use App\Models\User;
use App\Notifications\Bac\ClarificationAnswered;
use App\Notifications\Bac\ClarificationAsked;
use Illuminate\Support\Facades\Notification;

/**
 * Phase 9 "Ask Clarification": bidders question a posted procurement and
 * the BAC Secretariat/TWG responds. Every question/answer pair is kept as
 * a permanent, publicly auditable record tied to the procurement case.
 */
class ClarificationService
{
    public function ask(Procurement $procurement, Bidder $bidder, User $user, string $question): BidClarification
    {
        abort_unless($procurement->philgepsPosting?->isOpenForSubmission(), 422, 'Clarifications can only be asked while the posting is open.');

        $clarification = BidClarification::query()->create([
            'procurement_id' => $procurement->id,
            'bidder_id' => $bidder->id,
            'question' => $question,
            'status' => 'pending',
            'asked_by' => $user->id,
        ]);

        Notification::route('mail', config('mail.from.address'))
            ->notify(new ClarificationAsked($clarification));

        return $clarification;
    }

    public function answer(BidClarification $clarification, User $user, string $answer): BidClarification
    {
        $clarification->update([
            'answer' => $answer,
            'status' => 'answered',
            'answered_by' => $user->id,
            'answered_at' => now(),
        ]);

        if ($clarification->bidder?->email) {
            Notification::route('mail', $clarification->bidder->email)
                ->notify(new ClarificationAnswered($clarification));
        }

        return $clarification->fresh();
    }
}
