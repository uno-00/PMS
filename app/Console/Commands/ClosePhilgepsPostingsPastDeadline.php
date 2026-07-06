<?php

namespace App\Console\Commands;

use App\Enums\PhilgepsPostingStatus;
use App\Models\Bac\PhilgepsPosting;
use App\Services\Bac\PhilgepsPostingService;
use Illuminate\Console\Command;

/**
 * Phase 11 rule "Late submission disabled": once a posting's closing date
 * has passed, automatically close it and move the procurement case into
 * Bidding so BAC Secretariat can proceed to opening without a manual step.
 */
class ClosePhilgepsPostingsPastDeadline extends Command
{
    protected $signature = 'pms:close-expired-philgeps-postings';

    protected $description = 'Auto-close PhilGEPS postings whose closing date has passed.';

    public function handle(PhilgepsPostingService $service): int
    {
        $postings = PhilgepsPosting::query()
            ->where('status', PhilgepsPostingStatus::Published)
            ->whereDate('closing_date', '<', now())
            ->get();

        foreach ($postings as $posting) {
            $service->close($posting);
        }

        $this->info("Closed {$postings->count()} expired PhilGEPS posting(s).");

        return self::SUCCESS;
    }
}
