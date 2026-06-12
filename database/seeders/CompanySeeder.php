<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $count = 20;

        for ($i = 0; $i < $count; $i++) {
            $name = fake()->unique()->company();

            Company::firstOrCreate(
                ['name' => $name],
                [
                    'email' => fake()->unique()->companyEmail(),
                    'domain' => fake()->domainName(),
                    'phone' => fake()->phoneNumber(),
                ]
            );
        }

        $this->command->info("{$count} companies seeded successfully.");
    }
}
