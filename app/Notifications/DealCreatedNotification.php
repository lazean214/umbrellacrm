<?php

namespace App\Notifications;

use App\Enums\DealStage;
use App\Models\Deal;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DealCreatedNotification extends Notification
{
    public function __construct(
        public readonly Deal $deal
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("New Deal Assigned: {$this->deal->name}")
            ->greeting("Hello {$notifiable->name},")
            ->line('A new deal has been created and assigned to you.')
            ->line("**Deal:** {$this->deal->name}")
            ->line('**Stage:** '.($this->deal->stage instanceof DealStage ? $this->deal->stage->value : $this->deal->stage))
            ->line("**Amount:** {$this->deal->amount}")
            ->action('View Deal', url("/deals/{$this->deal->id}"))
            ->line('Please follow up as soon as possible.');
    }

    /**
     * Database notification payload
     */
    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'deal_created',
            'title' => 'New Deal Created',
            'message' => "A new deal '{$this->deal->name}' has been created by {$this->deal->user->name}.",
            'deal_id' => $this->deal->id,
            'url' => route('deals.show', $this->deal),
        ];
    }

    /**
     * Array representation
     */
    public function toArray($notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
