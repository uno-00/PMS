<?php

namespace App\Console\Commands;

use App\Models\Bac\BacCalendarEvent;
use App\Models\User;
use App\Notifications\Bac\BacCalendarReminder;
use App\Support\Roles;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

/**
 * Phase 7 "Notifications / Email Alerts": reminds the BAC (Chairperson,
 * Secretariat, Members) one day ahead of every scheduled activity
 * (pre-procurement conference, pre-bid conference, bid opening,
 * post-qualification, NOA, NTP, contract signing, PO issuance).
 */
class SendBacCalendarReminders extends Command
{
    protected $signature = 'pms:send-bac-calendar-reminders';

    protected $description = 'Email the BAC about calendar activities scheduled for tomorrow.';

    public function handle(): int
    {
        $events = BacCalendarEvent::query()
            ->with('procurement')
            ->where('status', 'scheduled')
            ->whereBetween('scheduled_at', [now()->addDay()->startOfDay(), now()->addDay()->endOfDay()])
            ->get();

        if ($events->isEmpty()) {
            $this->info('No BAC activities scheduled for tomorrow.');

            return self::SUCCESS;
        }

        $recipients = User::query()
            ->role(Roles::bac())
            ->where('is_active', true)
            ->get();

        foreach ($events as $event) {
            Notification::send($recipients, new BacCalendarReminder($event));
        }

        $this->info("Sent reminders for {$events->count()} activity(ies) to {$recipients->count()} BAC member(s).");

        return self::SUCCESS;
    }
}
