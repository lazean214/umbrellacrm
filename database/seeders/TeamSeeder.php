<?php

namespace Database\Seeders;

use App\Models\Team;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        Team::firstOrCreate(
            ['name' => 'Sales Team'],
            ['description' => 'Sales team members — can manage deals up to Compliant stage.'],
        );

        Team::firstOrCreate(
            ['name' => 'Compliance Team'],
            ['description' => 'Compliance team members — full access to all deal stages.'],
        );
    }
}
