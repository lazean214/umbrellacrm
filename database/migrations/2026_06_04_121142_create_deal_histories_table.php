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
        Schema::create('deal_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action'); // created, stage_moved, details_updated, association_updated, owner_changed
            $table->string('field')->nullable(); // Which field was changed (for updates)
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->text('details')->nullable(); // Human readable description
            $table->json('metadata')->nullable(); // Additional context
            $table->timestamps();

            $table->index(['deal_id', 'created_at']);
            $table->index('action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deal_histories');
    }
};
