<?php

namespace App\Notifications\Bac;

use App\Models\Procurement\NoticeToProceed;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Phase 16: "Email Supplier" once the Notice to Proceed is generated. */
class NoticeToProceedIssuedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public NoticeToProceed $noticeToProceed) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $ntp = $this->noticeToProceed;

        return (new MailMessage)
            ->subject("Notice to Proceed - {$ntp->ntp_no}")
            ->greeting('Notice to Proceed Issued')
            ->line("The Notice to Proceed for procurement case \"{$ntp->procurement->title}\" has been issued.")
            ->line('Effectivity Date: '.$ntp->effectivity_date->format('F d, Y'))
            ->when($ntp->completion_date, fn ($mail) => $mail->line('Completion Date: '.$ntp->completion_date->format('F d, Y')))
            ->action('View in Bidder Portal', route('bidder.awards.index'));
    }
}
