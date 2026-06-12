<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $indexes = collect(
            DB::select("SHOW INDEX FROM deals")
        )->pluck('Key_name')->unique();

        /*
        |--------------------------------------------------------------------------
        | Remove redundant stage index
        |--------------------------------------------------------------------------
        |
        | Covered by:
        | deals_stage_user_updated_idx
        |
        */

        if ($indexes->contains('deals_stage_index')) {
            DB::statement("
                ALTER TABLE deals
                DROP INDEX deals_stage_index
            ");
        }
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE deals
            ADD INDEX deals_stage_index(stage)
        ");
    }
};