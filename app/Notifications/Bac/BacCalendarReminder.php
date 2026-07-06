<?php

namespace App\Notifications\Bac;

use App\Models\Bac\BacCalendarEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Phase 7: "Notifications / Email Alerts / Dashboard Timeline" reminder for an upcoming BAC activity, dispatched by the scheduler. */
class BacCalendarReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public BacCalendarEvent $event) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $event = $this->event;

        return (new MailMessage)
            ->subject('Reminder: '.$event->typeLabel().' Tomorrow')
            ->greeting('Upcoming BAC Activity')
            ->line($event->typeLabel().' - '.$event->title)
            ->line('Scheduled: '.$event->scheduled_at->format('F d, Y g:ia'))
            ->when($event->venue, fn ($mail) => $mail->line('Venue: '.$event->venue))
            ->when($event->procurement, fn ($mail) => $mail->action('View Procurement Case', route('procurements.show', $event->procurement)));
    }
}
