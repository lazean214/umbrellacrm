<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Support\Facades\Storage;

class DealEmailMailable extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $subjectLine,
        public string $bodyContent,
        public array $emailAttachments = [],
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'email.simple-template',
            with: [
                'bodyContent' =>
                    $this->bodyContent,
            ],
        );
    }

    public function attachments(): array
    {
        return collect(
            $this->emailAttachments
        )
        ->filter(
            fn ($file) =>
                Storage::disk('local')
                    ->exists(
                        $file['path']
                    )
        )
        ->map(
            function ($file) {

                return Attachment::fromStorageDisk(
                    'local',
                    $file['path']
                )->as(
                    $file['name']
                );
            }
        )
        ->toArray();
    }
}