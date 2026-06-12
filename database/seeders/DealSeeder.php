<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Deal;
use App\Models\User;
use Illuminate\Database\Seeder;

class DealSeeder extends Seeder
{
    public function run(): void
    {
        $userCount = User::count();
        $companyCount = Company::count();

        if ($userCount === 0 || $companyCount === 0) {
            $this->command->error(
                'Users or Companies table is empty. Seed them first.'
            );

            return;
        }

        $count = 100;

        Deal::factory($count)->create();

        $this->command->info("{$count} deals seeded successfully.");
    }
}
