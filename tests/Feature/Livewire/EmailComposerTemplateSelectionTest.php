<?php

use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\EmailTemplate;
use App\Models\User;
use App\Services\EmailTemplateParser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createComposerDeal(User $user): Deal
{
    $deal = Deal::query()->create([
        'user_id' => $user->id,
        'name' => 'Template Selection Deal',
    ]);

    $contact = Contact::query()->create([
        'first_name' => 'Taylor',
        'last_name' => 'Contact',
        'email' => 'taylor@example.test',
    ]);

    $company = Company::query()->create([
        'name' => 'Builder Co',
        'email' => 'team@builder.test',
    ]);

    $deal->contacts()->attach($contact->id, ['is_primary' => true]);
    $deal->companies()->attach($company->id, ['is_primary' => true]);

    return $deal;
}

test('composer loads legacy html template and enables html preview mode', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $deal = createComposerDeal($user);

    $template = EmailTemplate::query()->create([
        'name' => 'Legacy HTML',
        'subject' => 'Welcome [contact.first_name]',
        'body' => '<h2>Hello [contact.first_name]</h2>',
        'is_html' => true,
        'editor_mode' => 'legacy',
        'is_active' => true,
        'created_by' => $user->id,
    ]);

    Livewire::test('activities.email.index', ['dealId' => $deal->id])
        ->set('templateId', $template->id)
        ->assertSet('renderBodyAsHtml', true)
        ->assertSet('body', '<h2>Hello Taylor</h2>');
});

test('composer loads builder template body from sections', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $deal = createComposerDeal($user);

    $template = EmailTemplate::query()->create([
        'name' => 'Builder Template',
        'subject' => 'Build [deal.name]',
        'body' => 'Unused legacy body',
        'is_html' => false,
        'editor_mode' => 'builder',
        'sections' => [
            [
                'id' => 't1',
                'type' => 'text',
                'content' => 'Hi [contact.first_name]',
                'image_url' => '',
                'media_id' => null,
                'alt' => '',
                'label' => '',
                'url' => '',
            ],
        ],
        'is_active' => true,
        'created_by' => $user->id,
    ]);

    $expectedBody = EmailTemplateParser::parse(
        $template->sections,
        $deal,
        $deal->primaryContact(),
        $deal->primaryCompany(),
        $user,
        true,
    );

    Livewire::test('activities.email.index', ['dealId' => $deal->id])
        ->set('templateId', $template->id)
        ->assertSet('renderBodyAsHtml', true)
        ->assertSet('body', $expectedBody);
});
