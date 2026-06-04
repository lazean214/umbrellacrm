<?php

namespace App\Services;

use App\Mail\DealEmailMailable;
use App\Models\Deal;
use App\Models\DealEmailLog;
use App\Models\EmailTemplate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Throwable;

class DealEmailService
{
    public static function send(
        Deal $deal,
        int $templateId,
        string $to,
        ?string $customSubject = null,
        ?string $customBody = null,

        array $selectedTemplateAttachments = [],
        array $manualAttachments = [],
    ): DealEmailLog {

        $contact =
            $deal->primaryContact();

        $company =
            $deal->primaryCompany();

        $user =
            Auth::user();

        $template =
            EmailTemplate::with(
                'attachments'
            )->findOrFail(
                $templateId
            );

        /*
        |--------------------------------------------------------------------------
        | Parse subject/body
        |--------------------------------------------------------------------------
        */

        $subject =
            $customSubject
            ?: EmailTemplateParser::parse(
                $template->subject,
                $deal,
                $contact,
                $company,
                $user,
            );

        $renderAsHtml =
            $template->editor_mode === 'builder'
            || (bool) ($template->is_html ?? true);

        $body =
            $customBody
            ?: EmailTemplateParser::parse(
                $template->editor_mode === 'builder'
                    ? ($template->sections ?? [])
                    : $template->body,
                $deal,
                $contact,
                $company,
                $user,
                $renderAsHtml,
            );

        /*
        |--------------------------------------------------------------------------
        | Template attachments
        |--------------------------------------------------------------------------
        */

        $templateAttachments =
            $template
                ->attachments
                ->whereIn(
                    'id',
                    $selectedTemplateAttachments
                )
                ->map(function ($file) {

                    return [
                        'path' => $file->file_path,

                        'name' => $file->file_name,
                    ];
                })
                ->toArray();

        /*
        |--------------------------------------------------------------------------
        | Manual uploads
        |--------------------------------------------------------------------------
        */

        $uploadedAttachments =
            collect(
                $manualAttachments
            )
                ->map(function (
                    $path
                ) {

                    return [
                        'path' => $path,

                        'name' => basename(
                            $path
                        ),
                    ];
                })
                ->toArray();

        /*
        |--------------------------------------------------------------------------
        | Merge all attachments
        |--------------------------------------------------------------------------
        */

        $attachments =
            array_merge(
                $templateAttachments,
                $uploadedAttachments
            );

        /*
        |--------------------------------------------------------------------------
        | Log email
        |--------------------------------------------------------------------------
        */

        $log =
            DealEmailLog::create([
                'deal_id' => $deal->id,

                'contact_id' => $contact?->id,

                'company_id' => $company?->id,

                'user_id' => $user?->id,

                'email_template_id' => $template->id,

                'to_email' => $to,

                'subject' => $subject,

                'body' => $body,

                'status' => 'pending',
            ]);

        try {

            Mail::to($to)->send(
                new DealEmailMailable(
                    subjectLine: $subject,

                    bodyContent: $body,

                    isHtml: $renderAsHtml,

                    emailAttachments: $attachments,
                )
            );

            $log->update([
                'status' => 'sent',

                'sent_at' => now(),

                'error_message' => null,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Cleanup temp uploads
            |--------------------------------------------------------------------------
            */

            foreach (
                $manualAttachments as $path
            ) {

                Storage::disk(
                    'local'
                )->delete(
                    $path
                );
            }

        } catch (Throwable $e) {

            $log->update([
                'status' => 'failed',

                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }

        return $log;
    }
}
