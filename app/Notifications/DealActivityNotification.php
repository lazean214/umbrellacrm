<?php

namespace App\Notifications;

use App\Models\ActivityLog;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class DealActivityNotification extends Notification
{
    use Queueable;

    // ✅ Inject the newly created ActivityLog model
    public function __construct(
        public readonly ActivityLog $activity
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database']; // ✅ Ensure database channel is set
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $typeLabel = ucfirst($this->activity->type ?? 'Activity');

        return (new MailMessage)
            ->subject("New {$typeLabel}: {$this->activity->activity_name}")
            ->greeting("Hello {$notifiable->name},")
            ->line('A new activity has been recorded on your deal:')
            ->line("**Type:** {$typeLabel}")
            ->line("**Title:** {$this->activity->activity_name}")
            ->line('"'.Str::limit($this->activity->message, 150).'"')
            ->action('View Deal', url("/deals/{$this->activity->deal_id}"))
            ->line('Thank you for using our application!');
    }

    /**
     * ✅ Store formatted payload for ⚡notifications-dropdown.blade.php
     */
    public function toDatabase(object $notifiable): array
    {
        $typeLabel = ucfirst($this->activity->type ?? 'Activity');

        return [
            'type' => 'deal_activity_created',
            'title' => "New {$typeLabel} Created",
            'message' => "{$this->activity->activity_name}: ".Str::limit($this->activity->message, 60),
            'deal_id' => $this->activity->deal_id,
            'url' => url("/deals/{$this->activity->deal_id}"),
        ];
    }

    /**
     * Fallback array mapping
     */
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
