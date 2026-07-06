<?php

namespace App\Notifications\Bac;

use App\Models\Bac\BidClarification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClarificationAnswered extends Notification implements ShouldQueue
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
            ->subject('Your Clarification Has Been Answered - '.$c->procurement->case_no)
            ->line('Your question on "'.$c->procurement->title.'" has been answered:')
            ->line('Q: '.$c->question)
            ->line('A: '.$c->answer)
            ->action('View in Bidder Portal', route('bidder.opportunities.show', $c->procurement));
    }
}
