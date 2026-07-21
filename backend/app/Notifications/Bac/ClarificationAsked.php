<?php

namespace App\Notifications\Bac;

use App\Models\Bac\BidClarification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClarificationAsked extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public BidClarification $clarification) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $c = $this->clarification;

        return (new MailMessage)
            ->subject('New Bidder Clarification - '.$c->procurement->case_no)
            ->line("{$c->bidder->company_name} asked a clarification on \"{$c->procurement->title}\".")
            ->line('Question: '.$c->question)
            ->action('Answer in BAC Module', route('procurements.show', $c->procurement));
    }
}
