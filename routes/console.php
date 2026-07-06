<?php

use App\Console\Commands\ClosePhilgepsPostingsPastDeadline;
use App\Console\Commands\NotifyExpiringBidderDocuments;
use App\Console\Commands\PruneAuditLogs;
use App\Console\Commands\SendBacCalendarReminders;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Laravel Scheduler
|--------------------------------------------------------------------------
| Requires a single cron entry on the server:
|   * * * * * cd /path-to-app && php artisan schedule:run >> /dev/null 2>&1
| See docs/PRODUCTION_DEPLOYMENT.md for the full crontab, queue worker
| (supervisor), and horizon/queue:work configuration.
*/

// Phase 7: daily BAC activity reminders (pre-procurement conference,
// pre-bid conference, bid opening, post-qualification, NOA, NTP,
// contract signing, PO issuance) one day ahead of the scheduled date.
Schedule::command(SendBacCalendarReminders::class)
    ->dailyAt('06:00')
    ->withoutOverlapping()
    ->onOneServer();

// Phase 11: enforce "late submission disabled" by auto-closing PhilGEPS
// postings whose closing date has passed, every 15 minutes.
Schedule::command(ClosePhilgepsPostingsPastDeadline::class)
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->onOneServer();

// Phase 9: warn bidders 30 days before an eligibility document expires.
Schedule::command(NotifyExpiringBidderDocuments::class)
    ->dailyAt('06:30')
    ->withoutOverlapping()
    ->onOneServer();

// Settings > Security: enforce the configurable audit log retention window.
Schedule::command(PruneAuditLogs::class)->dailyAt('02:00')->withoutOverlapping()->onOneServer();

// Housekeeping.
Schedule::command('queue:prune-batches')->daily();
Schedule::command('queue:prune-failed', ['--hours' => 720])->weekly();
Schedule::command('model:prune')->daily();
Schedule::command('backup:run')->dailyAt('01:00')->when(fn () => config('backup.enabled', false));
