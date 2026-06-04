<?php

namespace App\Notifications;

use App\Models\Deal;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DealReadyForPaymentNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Deal $deal
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Payment Ready: {$this->deal->name}")
            ->greeting('Hello,')
            ->line('Your deal is now ready for payment.')
            ->line("**Deal:** {$this->deal->name}")
            ->line("**Amount:** {$this->deal->amount}")
            ->action('View Deal', url("/deals/{$this->deal->id}"))
            ->line('Please proceed with the payment at your earliest convenience.');
    }
}
