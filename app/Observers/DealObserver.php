<?php

namespace App\Observers;

use App\Enums\DealStage;
use App\Models\Deal;
use App\Models\User;
use App\Notifications\DealCreatedNotification;
use App\Notifications\DealStageMovedNotification;
use Illuminate\Support\Facades\Notification;

class DealObserver
{
    /**
     * Handle the Deal "created" event.
     * Notifies the deal owner when a new deal is created.
     */
    public function created(Deal $deal): void
    {
        // Stamp initial stage timestamp without triggering observer loop
        $deal->timestamps = false;
        $deal->updateQuietly(['stage_updated_at' => now()]);
        $deal->timestamps = true;

        $deal->load('user');

        if ($deal->user) {
            $deal->user->notify(new DealCreatedNotification($deal));
        }
    }

    /**
     * Handle the Deal "updated" event.
     * Stamps stage_updated_at and notifies on stage changes.
     */
    public function updated(Deal $deal): void
    {
        if ($deal->wasChanged('stage')) {

            // Stamp the stage change time without triggering observer loop
            $deal->timestamps = false;
            $deal->updateQuietly(['stage_updated_at' => now()]);
            $deal->timestamps = true;

            $oldStage = $deal->getOriginal('stage');
            $newStage = $deal->stage;

            // Convert enum to string safely
            $oldStage = $oldStage instanceof DealStage
                ? $oldStage->value
                : $oldStage;

            $newStage = $newStage instanceof DealStage
                ? $newStage->value
                : $newStage;

            $users = User::whereHas('teams', function ($query) {
                $query->where('name', 'Compliance');
            })->get();

            if ($deal->user) {
                $users->push($deal->user);
            }

            Notification::send(
                $users->unique('id'),
                new DealStageMovedNotification(
                    $deal,
                    $oldStage,
                    $newStage
                )
            );
        }
    }

    private function recipients(Deal $deal)
    {
        $complianceUsers = User::whereHas('teams', function ($query) {
            $query->where('name', 'Compliance');
        })->get();

        return $complianceUsers
            ->push($deal->owner)
            ->unique('id');
    }
}
