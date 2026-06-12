<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\User;

class EmailTemplateParser
{
    public static function parse(
        string|array|null $content,
        ?Deal $deal = null,
        ?Contact $contact = null,
        ?Company $company = null,
        ?User $user = null,
    ): string {
        $tokens = [
            // Deal
            '[deal.name]' => $deal?->name ?? '',
            '[deal.amount]' => $deal?->amount ?? '',
            '[deal.stage]' => $deal?->stage?->value ?? '',
            '[deal.consultant_name]' => $deal?->consultant_name ?? '',
            '[deal.agency_deal_value]' => $deal?->agency_deal_value ?? '',
            '[deal.margin_agreed]' => $deal?->margin_agreed ?? '',
            '[deal.date_signed]' => $deal?->date_signed ?? '',

            // Contact
            '[contact.first_name]' => $contact?->first_name ?? '',
            '[contact.last_name]' => $contact?->last_name ?? '',
            '[contact.email]' => $contact?->email ?? '',
            '[contact.phone]' => $contact?->phone ?? '',
            '[contact.full_name]' => trim(
                ($contact?->first_name ?? '').
                ' '.
                ($contact?->last_name ?? '')
            ),

            // Company
            '[company.name]' => $company?->name ?? '',
            '[company.email]' => $company?->email ?? '',
            '[company.phone]' => $company?->phone ?? '',
            '[company.domain]' => $company?->domain ?? '',

            // User
            '[user.name]' => $user?->name ?? '',
            '[user.email]' => $user?->email ?? '',
        ];

        if (is_array($content)) {
            return self::renderSections($content, $tokens);
        }

        if ($content === null) {
            return '';
        }

        return self::replaceTokens($content, $tokens);
    }

    protected static function replaceTokens(
        string $content,
        array $tokens,
    ): string {
        return str_replace(
            array_keys($tokens),
            array_values($tokens),
            $content
        );
    }

    protected static function renderSections(
        array $sections,
        array $tokens,
    ): string {
        $renderedSections = collect($sections)
            ->map(function (array $section) use ($tokens): string {
                $type = $section['type'] ?? 'text';

                if ($type === 'image') {
                    $imageUrl = $section['image_url'] ?? '';

                    if (! filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                        return '';
                    }

                    $alt = e((string) ($section['alt'] ?? 'Template image'));

                    return '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:16px;"><tr><td align="center"><img src="'.e($imageUrl).'" alt="'.$alt.'" style="max-width:100%;height:auto;border:0;display:block;" /></td></tr></table>';
                }

                if ($type === 'button') {
                    $label = trim(self::replaceTokens((string) ($section['label'] ?? 'Open link'), $tokens));
                    $url = trim(self::replaceTokens((string) ($section['url'] ?? '#'), $tokens));

                    if (! filter_var($url, FILTER_VALIDATE_URL)) {
                        return '';
                    }

                    return '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:16px;"><tr><td align="center"><a href="'.e($url).'" style="display:inline-block;padding:12px 18px;background:#059669;border-radius:8px;color:#ffffff;text-decoration:none;font-weight:600;">'.e($label).'</a></td></tr></table>';
                }

                $content = self::replaceTokens((string) ($section['content'] ?? ''), $tokens);

                $content = self::renderTextMarkup($content);

                return '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:16px;"><tr><td style="font-size:16px;line-height:1.5;color:#0f172a;">'.$content.'</td></tr></table>';
            })
            ->filter()
            ->implode('');

        return $renderedSections;
    }

    protected static function renderTextMarkup(string $content): string
    {
        $safe = e($content);

        $safe = preg_replace_callback(
            '/\[([^\]]+)\]\((https?:\/\/[^\s\)]+)\)/',
            function (array $matches): string {
                $label = $matches[1];
                $url = $matches[2];

                if (! filter_var($url, FILTER_VALIDATE_URL)) {
                    return $matches[0];
                }

                return '<a href="'.e($url).'" style="color:#0867ec;text-decoration:underline;">'.$label.'</a>';
            },
            $safe
        ) ?? $safe;

        $safe = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $safe) ?? $safe;
        $safe = preg_replace('/_(.*?)_/', '<em>$1</em>', $safe) ?? $safe;

        $lines = preg_split('/\R/', $safe) ?: [];
        $html = [];
        $inList = false;

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if ($trimmed === '') {
                if ($inList) {
                    $html[] = '</ul>';
                    $inList = false;
                }

                continue;
            }

            if (str_starts_with($trimmed, '- ')) {
                if (! $inList) {
                    $html[] = '<ul style="padding-left:20px;margin:8px 0;">';
                    $inList = true;
                }

                $html[] = '<li>'.substr($trimmed, 2).'</li>';

                continue;
            }

            if ($inList) {
                $html[] = '</ul>';
                $inList = false;
            }

            if (str_starts_with($trimmed, '### ')) {
                $html[] = '<h3 style="margin:12px 0 8px;font-size:18px;">'.substr($trimmed, 4).'</h3>';

                continue;
            }

            if (str_starts_with($trimmed, '## ')) {
                $html[] = '<h2 style="margin:12px 0 8px;font-size:20px;">'.substr($trimmed, 3).'</h2>';

                continue;
            }

            $html[] = '<p style="margin:0 0 12px;">'.$trimmed.'</p>';
        }

        if ($inList) {
            $html[] = '</ul>';
        }

        return implode('', $html);
    }
}
