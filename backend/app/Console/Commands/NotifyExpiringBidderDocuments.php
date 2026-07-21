<?php

namespace App\Console\Commands;

use App\Models\Supplier\Bidder;
use App\Notifications\Supplier\EligibilityDocumentExpiringNotification;
use Illuminate\Console\Command;

/**
 * Phase 9: warns bidders 30 days before any eligibility document
 * (PhilGEPS registration, Mayor's Permit, Tax Clearance, PCAB license)
 * expires, so they stay eligible to participate in ongoing biddings.
 */
class NotifyExpiringBidderDocuments extends Command
{
    protected $signature = 'pms:notify-expiring-bidder-documents';

    protected $description = 'Notify bidders whose eligibility documents expire within 30 days.';

    public function handle(): int
    {
        $horizon = now()->addDays(30)->toDateString();
        $notified = 0;

        Bidder::query()
            ->where('status', 'verified')
            ->where(function ($q) use ($horizon) {
                $q->whereDate('philgeps_registration_expiry', '<=', $horizon)
                    ->orWhereDate('mayor_permit_expiry', '<=', $horizon)
                    ->orWhereDate('tax_clearance_expiry', '<=', $horizon)
                    ->orWhereDate('pcab_license_expiry', '<=', $horizon);
            })
            ->whereNotNull('email')
            ->chunkById(50, function ($bidders) use (&$notified) {
                foreach ($bidders as $bidder) {
                    $expiring = collect([
                        'PhilGEPS Registration' => $bidder->philgeps_registration_expiry,
                        "Mayor's Permit" => $bidder->mayor_permit_expiry,
                        'Tax Clearance' => $bidder->tax_clearance_expiry,
                        'PCAB License' => $bidder->pcab_license_expiry,
                    ])->filter(fn ($date) => $date && $date->lte(now()->addDays(30)));

                    if ($expiring->isNotEmpty()) {
                        $bidder->notify(new EligibilityDocumentExpiringNotification($expiring->toArray()));
                        $notified++;
                    }
                }
            });

        $this->info("Notified {$notified} bidder(s) of expiring eligibility documents.");

        return self::SUCCESS;
    }
}
