<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Get existing indexes
        $indexes = collect(
            DB::select("SHOW INDEX FROM deals")
        )->pluck('Key_name')->toArray();

        Schema::table('deals', function (Blueprint $table) use ($indexes) {

            /*
            |--------------------------------------------------------------------------
            | Main Kanban Query
            |--------------------------------------------------------------------------
            */

            if (!in_array('deals_stage_user_updated_idx', $indexes)) {
                $table->index(
                    ['stage', 'user_id', 'updated_at'],
                    'deals_stage_user_updated_idx'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Sorting
            |--------------------------------------------------------------------------
            */

            if (!in_array('deals_updated_at_idx', $indexes)) {
                $table->index(
                    'updated_at',
                    'deals_updated_at_idx'
                );
            }

            if (!in_array('deals_stage_updated_at_idx', $indexes)) {
                $table->index(
                    'stage_updated_at',
                    'deals_stage_updated_at_idx'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Financial filtering
            |--------------------------------------------------------------------------
            */

            if (!in_array('deals_amount_idx', $indexes)) {
                $table->index(
                    'amount',
                    'deals_amount_idx'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Date + Stage filtering
            |--------------------------------------------------------------------------
            */

            if (!in_array('deals_created_stage_idx', $indexes)) {
                $table->index(
                    ['created_at', 'stage'],
                    'deals_created_stage_idx'
                );
            }
        });
    }

    public function down(): void
    {
        $indexes = collect(
            DB::select("SHOW INDEX FROM deals")
        )->pluck('Key_name')->toArray();

        Schema::table('deals', function (Blueprint $table) use ($indexes) {

            if (in_array('deals_stage_user_updated_idx', $indexes)) {
                $table->dropIndex(
                    'deals_stage_user_updated_idx'
                );
            }

            if (in_array('deals_updated_at_idx', $indexes)) {
                $table->dropIndex(
                    'deals_updated_at_idx'
                );
            }

            if (in_array('deals_stage_updated_at_idx', $indexes)) {
                $table->dropIndex(
                    'deals_stage_updated_at_idx'
                );
            }

            if (in_array('deals_amount_idx', $indexes)) {
                $table->dropIndex(
                    'deals_amount_idx'
                );
            }

            if (in_array('deals_created_stage_idx', $indexes)) {
                $table->dropIndex(
                    'deals_created_stage_idx'
                );
            }
        });
    }
};