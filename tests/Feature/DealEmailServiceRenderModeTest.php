<?php

use App\Mail\DealEmailMailable;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\EmailTemplate;
use App\Models\User;
use App\Services\DealEmailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

function createDealGraph(User $user): array
{
    $deal = Deal::query()->create([
        'user_id' => $user->id,
        'name' => 'Website Designer Deal',
    ]);

    $contact = Contact::query()->create([
        'first_name' => 'Emma',
        'last_name' => 'Client',
        'email' => 'emma@example.test',
    ]);

    $company = Company::query()->create([
        'name' => 'Acme Ltd',
        'email' => 'hello@acme.test',
    ]);

    $deal->contacts()->attach($contact->id, ['is_primary' => true]);
    $deal->companies()->attach($company->id, ['is_primary' => true]);

    return [$deal, $contact, $company];
}

test('deal email service sends html templates as html mode', function () {
    Mail::fake();

    $user = User::factory()->create();
    $this->actingAs($user);

    [$deal] = createDealGraph($user);

    $template = EmailTemplate::query()->create([
        'name' => 'HTML Template',
        'subject' => 'Welcome [contact.first_name]',
        'body' => '<h1>Hello [contact.first_name]</h1>',
        'is_html' => true,
        'editor_mode' => 'legacy',
        'is_active' => true,
        'created_by' => $user->id,
    ]);

    DealEmailService::send(
        deal: $deal,
        templateId: $template->id,
        to: 'recipient@example.test',
    );

    Mail::assertSent(DealEmailMailable::class, function (DealEmailMailable $mailable): bool {
        return $mailable->isHtml === true
            && str_contains($mailable->bodyContent, '<h1>Hello Emma</h1>');
    });
});

test('deal email service sends plain templates with html mode disabled', function () {
    Mail::fake();

    $user = User::factory()->create();
    $this->actingAs($user);

    [$deal] = createDealGraph($user);

    $template = EmailTemplate::query()->create([
        'name' => 'Plain Template',
        'subject' => 'Update [contact.first_name]',
        'body' => "Hi [contact.first_name]\n\n**Important** update",
        'is_html' => false,
        'editor_mode' => 'legacy',
        'is_active' => true,
        'created_by' => $user->id,
    ]);

    DealEmailService::send(
        deal: $deal,
        templateId: $template->id,
        to: 'recipient@example.test',
    );

    Mail::assertSent(DealEmailMailable::class, function (DealEmailMailable $mailable): bool {
        return $mailable->isHtml === false
            && str_contains($mailable->bodyContent, '<strong>Important</strong>')
            && str_contains($mailable->bodyContent, 'Emma');
    });
});
