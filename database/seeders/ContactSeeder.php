<?php

namespace Database\Seeders;

use App\Models\Contact;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    public function run(): void
    {
        $count = 50;

        for ($i = 0; $i < $count; $i++) {
            $data = [
                'first_name' => fake()->firstName(),
                'last_name' => fake()->lastName(),
                'phone' => fake()->phoneNumber(),
                'street_address' => fake()->streetAddress(),
                'city' => fake()->city(),
                'state' => fake()->state(),
                'postal_code' => fake()->postcode(),
                'country' => fake()->country(),
                'ni_number' => strtoupper(fake()->bothify('??######?')),
                'bank' => fake()->company(),
                'account_number' => fake()->numerify('########'),
                'sort_code' => fake()->numerify('##-##-##'),
                'date_of_birth' => fake()->date(),
                'marital_status' => fake()->randomElement(['single', 'married', 'divorced', 'widowed']),
                'gender' => fake()->randomElement(['male', 'female', 'other']),
            ];

            // updateOrCreate on email — if contact exists, update their details
            Contact::updateOrCreate(
                ['email' => fake()->unique()->safeEmail()],
                $data
            );
        }

        $this->command->info("{$count} contacts seeded / updated successfully.");
    }
}
