<?php

namespace App\Services;

use App\Models\Deal;
use App\Models\User;
use App\Models\Contact;
use App\Models\Company;

class EmailTemplateParser
{
    public static function parse(
        string $content,
        ?Deal $deal = null,
        ?Contact $contact = null,
        ?Company $company = null,
        ?User $user = null,
    ): string {

        $tokens = [
            // Deal
            '[deal.name]' => $deal?->name ?? '',
            '[deal.amount]' => $deal?->amount ?? '',
            '[deal.stage]' => $deal?->stage ?? '',
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
                ($contact?->first_name ?? '') .
                ' ' .
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

        return str_replace(
            array_keys($tokens),
            array_values($tokens),
            $content
        );
    }
}