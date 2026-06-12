<?php

namespace App\Jobs;

use Throwable;
use App\Models\DealEmailLog;
use App\Mail\DealEmailMailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendDealEmailJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public int $logId
    ) {}

    public function handle(): void
    {
        $log = DealEmailLog::find($this->logId);

        if (! $log) {
            return;
        }

        try {

            Mail::to($log->to_email)
                ->send(
                    new DealEmailMailable(
                        subjectLine: $log->subject,
                        bodyContent: $log->body,
                    )
                );

            $log->update([
                'status' => 'sent',
                'sent_at' => now(),
                'error_message' => null,
            ]);

        } catch (Throwable $e) {

            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}