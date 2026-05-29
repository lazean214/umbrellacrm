<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::view('deals', 'deals.deals')->name('deals');
    Route::get('deals/{deal}', [\App\Http\Controllers\DealController::class, 'show'])->name('deals.show');
    Route::view('contacts', 'contacts')->name('contacts');
    Route::view('companies', 'companies')->name('companies');
    Route::view('designer', 'email.index')->name('designer');
    Route::view('designer/create', 'email.create')->name('designer.create');
    Route::view('designer/{email}', 'email.edit')->name('designer.edit');
});

require __DIR__.'/settings.php';

// /api/webhooks/signable