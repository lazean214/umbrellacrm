<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            $table->index('stage');
            $table->index('consultant_name');
            $table->index('recruitment_agency');
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->index('deal_id');
            $table->index('type');
            $table->index('status');
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->index('marked_for_deletion_on');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('marked_for_deletion_on');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['marked_for_deletion_on']);
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->dropIndex(['marked_for_deletion_on']);
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex(['deal_id']);
            $table->dropIndex(['type']);
            $table->dropIndex(['status']);
        });

        Schema::table('deals', function (Blueprint $table) {
            $table->dropIndex(['stage']);
            $table->dropIndex(['consultant_name']);
            $table->dropIndex(['recruitment_agency']);
        });
    }
};
