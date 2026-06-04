<?php

namespace App\Notifications;

use App\Models\Deal;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DealStageMovedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Deal $deal,
        public string $oldStage,
        public string $newStage
    ) {}

    /**
     * Delivery channels
     */
    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * Database notification payload
     */
    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'stage_moved',
            'title' => $this->deal->name.' Deal Stage Updated',
            'message' => "{$this->deal->company_name} moved from {$this->oldStage} to {$this->newStage}",
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
