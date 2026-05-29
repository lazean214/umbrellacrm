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
        Schema::create('deal_signable_envelopes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deal_id')->constrained()->cascadeOnDelete()->index();
            $table->unsignedBigInteger('user_id');
            $table->string('envelope_title');
            $table->string('envelope_fingerprint');
            $table->dateTime('date_created')->nullable();
            $table->dateTime('date_signed')->nullable();
            $table->string('envelope_status', 100)->nullable();
            $table->text('download_link')->nullable();
            $table->timestamps();
            $table->unique(['deal_id', 'envelope_fingerprint']);
            $table->index(['deal_id', 'date_created']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deal_signable_envelopes');
    }
};
