<?php

namespace Tests\Browser;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\EmailTemplate;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class CRMWorkflowTest extends DuskTestCase
{
    /**
     * Test the complete CRM workflow.
     */
    public function test_complete_workflow(): void
    {
        $this->browse(function (Browser $browser) {
            $email = 'john'.time().'@example.com';

            // 1. User registration
            $browser->visit('/register')
                ->waitForText('Create an account')
                ->type('input[name="name"]', 'John Doe')
                ->type('input[name="email"]', $email)
                ->type('input[name="password"]', 'Password123!')
                ->type('input[name="password_confirmation"]', 'Password123!')
                ->press('Create account')
                ->waitForLocation('/dashboard', 15);

            // Verify user manually to bypass verified middleware
            $user = User::where('email', $email)->first();
            $user->markEmailAsVerified();

            // 2. Creating deal
            $browser->visit('/deals')
                ->waitForText('New Deal', 20)
                ->click('.deal-trigger-btn') // Click "New Deal"
                ->waitFor('.deal-modal')
                ->type('input[wire\:model="name"]', 'Big Software Deal')
                ->type('input[wire\:model="amount"]', '5000')
                ->type('input[wire\:model\.live="email"]', 'client@example.com')
                ->pause(2000) // Wait for live sync to detect contact/company
                ->type('input[wire\:model="first_name"]', 'Jane')
                ->type('input[wire\:model="last_name"]', 'Smith')
                ->type('input[wire\:model="consultant_name"]', 'Awesome Recruiter')
                ->click('.deal-submit-btn')
                ->waitForText('Big Software Deal', 30)
                ->assertSee('Jane Smith');

            $deal = Deal::where('name', 'Big Software Deal')->first();

            // 3. Managing deal stages
            $browser->visit("/deals/{$deal->id}")
                ->waitFor('button[wire\:click="setStage(\'doc signed\')"]')
                ->click('button[wire\:click="setStage(\'doc signed\')"]')
                ->waitForText('Deal stage updated.', 15)
                ->assertSee('Doc Signed');

            // 4. Activity: Task
            $browser->click('button[wire\:click="$set(\'openTabs\', \'activities\')"]')
                ->waitForText('Activity Feed')
                ->type('input[wire\:model="activityName"]', 'Follow up call')
                ->type('textarea[wire\:model="message"]', 'Call Jane to discuss contract')
                ->click('button[type="submit"]') // The "Save" button
                ->waitForText('Follow up call', 15)
                ->assertSee('Call Jane to discuss contract');

            // 5. Activity: Comment on task
            $browser->click('button[wire\:click="$set(\'showForm\', true)"]')
                ->waitFor('textarea[wire\:model="comment"]')
                ->type('textarea[wire\:model="comment"]', 'Called but no answer')
                ->press('Comment')
                ->waitForText('Called but no answer', 15);

            // 6. Activity: Email
            // We need an email template first
            EmailTemplate::create([
                'name' => 'Welcome Template',
                'subject' => 'Welcome to the project',
                'body' => 'Hi {{first_name}}, welcome!',
                'is_active' => true,
                'created_by' => $user->id,
            ]);

            $browser->select('select[wire\:model\.live="type"]', 'email')
                ->waitFor('input[wire\:model="userEmail"]', 15)
                ->type('input[wire\:model="activityName"]', 'Welcome Email')
                ->type('input[wire\:model="userEmail"]', 'client@example.com')
                ->select('select[wire\:model="emailTemplateId"]', '1')
                ->press('Send Email')
                ->waitForText('Welcome Email', 15)
                ->assertSee('Welcome Email');

            // 7. Data export
            $browser->visit('/deals')
                ->waitForText('New Deal', 15)
                ->click('button[wire\:click="setView(\'table\')"]')
                ->waitForText('Table', 15)
                ->assertPresent('a[href*="/deals/export"]');
        });
    }
}
