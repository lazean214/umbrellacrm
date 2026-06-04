<?php

namespace App\Notifications;

use App\Models\ActivityLog;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class DealCommentedNotification extends Notification
{
    use Queueable;

    // Inject the newly created comment/reply activity model
    public function __construct(
        public readonly ActivityLog $comment
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $typeLabel = $this->comment->activity_name ?? 'Comment';
        $typeLabelLower = strtolower($typeLabel); // 👈 Move the assignment out here

        return (new MailMessage)
            ->subject("New {$typeLabel} on Deal Activity")
            ->greeting("Hello {$notifiable->name},")
            ->line("{$this->comment->user_email} added a {$typeLabelLower}:") // 👈 Kept clean here
            ->line('"'.Str::limit($this->comment->message, 150).'"')
            ->action('View Deal', url("/deals/{$this->comment->deal_id}"))
            ->line('Thank you for using our application!');
    }

    /**
     * Store formatted payload for ⚡notifications-dropdown.blade.php
     */
    public function toDatabase(object $notifiable): array
    {
        $typeLabel = $this->comment->activity_name ?? 'Comment';

        return [
            'type' => 'deal_commented',
            'title' => "New {$typeLabel} Received",
            'message' => "{$this->comment->user_email}: ".Str::limit($this->comment->message, 60),
            'deal_id' => $this->comment->deal_id,
            'url' => url("/deals/{$this->comment->deal_id}"),
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
