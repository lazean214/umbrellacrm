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
        Schema::table('email_templates', function (Blueprint $table) {
            $table->boolean('is_html')
                ->default(true)
                ->after('body');

            $table->string('editor_mode')
                ->default('legacy')
                ->after('is_html');

            $table->json('sections')
                ->nullable()
                ->after('editor_mode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_templates', function (Blueprint $table) {
            $table->dropColumn([
                'is_html',
                'editor_mode',
                'sections',
            ]);
        });
    }
};
