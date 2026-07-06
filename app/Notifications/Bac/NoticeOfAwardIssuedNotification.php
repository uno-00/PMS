<?php

namespace App\Notifications\Bac;

use App\Models\Procurement\NoticeOfAward;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Phase 15: "Email Supplier" once the Notice of Award is generated.
 */
class NoticeOfAwardIssuedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public NoticeOfAward $noticeOfAward) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $noa = $this->noticeOfAward;

        return (new MailMessage)
            ->subject("Notice of Award - {$noa->noa_no}")
            ->greeting('Congratulations!')
            ->line("Your bid for procurement case \"{$noa->procurement->title}\" (Case No. {$noa->procurement->case_no}) has been awarded.")
            ->line('Awarded Amount: ₱'.number_format((float) $noa->amount, 2))
            ->action('View / Respond in Bidder Portal', route('bidder.awards.index'))
            ->line('Please accept or decline this award within the period prescribed by the Bidding Documents.');
    }
}
