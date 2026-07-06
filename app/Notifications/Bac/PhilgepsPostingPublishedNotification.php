<?php

namespace App\Notifications\Bac;

use App\Models\Bac\PhilgepsPosting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Phase 8: alert verified bidders that a new opportunity has been posted to PhilGEPS. */
class PhilgepsPostingPublishedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public PhilgepsPosting $posting) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $posting = $this->posting;
        $procurement = $posting->procurement;

        return (new MailMessage)
            ->subject('New Procurement Opportunity: '.$procurement->title)
            ->greeting('New Opportunity Posted')
            ->line("\"{$procurement->title}\" (ABC: ₱".number_format((float) $procurement->abc, 2).') has been posted to PhilGEPS.')
            ->line('Closing Date: '.$posting->closing_date->format('F d, Y'))
            ->action('View Opportunity', route('bidder.opportunities.show', $procurement));
    }
}
