<?php

namespace App\Notifications\Supplier;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class EligibilityDocumentExpiringNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /** @param array<string, Carbon> $expiring */
    public function __construct(public array $expiring) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Eligibility Documents Expiring Soon')
            ->greeting('Action Required')
            ->line('The following eligibility document(s) on file are expiring soon or have expired:');

        foreach ($this->expiring as $label => $date) {
            $mail->line("- {$label}: ".$date->format('F d, Y'));
        }

        return $mail->action('Update Documents', route('bidder.profile'))
            ->line('Please upload updated documents to remain eligible to participate in ongoing and future biddings.');
    }
}
