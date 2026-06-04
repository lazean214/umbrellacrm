<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AuthenticationTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Test user registration.
     */
    public function test_user_can_register(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                ->type('name', 'Test User')
                ->type('email', 'test@example.com')
                ->type('password', 'Password123!')
                ->type('password_confirmation', 'Password123!')
                ->click('[data-test="register-user-button"]')
                ->assertPathIs('/dashboard')
                ->assertSee('Test User');
        });
    }

    /**
     * Test user login.
     */
    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
            'email' => 'login@example.com',
            'password' => bcrypt('Password123!'),
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/login')
                ->type('email', $user->email)
                ->type('password', 'Password123!')
                ->click('[data-test="login-button"]')
                ->assertPathIs('/dashboard')
                ->assertSee($user->name);
        });
    }
}
