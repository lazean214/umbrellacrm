<?php

namespace App\Console\Commands;

use App\Enums\DealStage;
use App\Models\Deal;
use App\Notifications\DealStageStaleNotification;
use Illuminate\Console\Command;

class CheckStaleDealStages extends Command
{
    protected $signature = 'deals:check-stale-stages';

    protected $description = 'Notify deal owners when a Doc Sent deal has not progressed in over 24 hours.';

    public function handle(): int
    {
        $stale = Deal::query()
            ->with('user')
            ->where('stage', DealStage::DOC_SENT)
            ->whereNotNull('stage_updated_at')
            ->where('stage_updated_at', '<=', now()->subHours(24))
            ->get();

        if ($stale->isEmpty()) {
            $this->info('No stale deals found.');

            return self::SUCCESS;
        }

        $notified = 0;

        foreach ($stale as $deal) {
            if (! $deal->user) {
                continue;
            }

            // Avoid duplicate notifications: skip if owner already has an
            // unread stale notification for this deal sent in the last 24h.
            $alreadyNotified = $deal->user
                ->notifications()
                ->where('type', DealStageStaleNotification::class)
                ->where('data->deal_id', $deal->id)
                ->where('created_at', '>=', now()->subHours(24))
                ->whereNull('read_at')
                ->exists();

            if ($alreadyNotified) {
                continue;
            }

            $deal->user->notify(new DealStageStaleNotification($deal));
            $notified++;

            $this->line("Notified: {$deal->user->name} → {$deal->name}");
        }

        $this->info("Done. {$notified} notification(s) sent.");

        return self::SUCCESS;
    }
}
