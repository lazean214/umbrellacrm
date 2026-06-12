<?php

namespace Database\Factories;

use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contact>
 */
class ContactFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
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
    }
}
