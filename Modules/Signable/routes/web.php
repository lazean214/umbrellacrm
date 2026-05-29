<?php

use Modules\Signable\App\Http\Controllers\SendEnvelopeController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function (): void {
    Route::view('/envelopes', 'signable::envelopes.index')->name('envelopes.index');
    Route::get('/envelopes/send', SendEnvelopeController::class)->name('envelopes.send');
});

