<?php

use Illuminate\Support\Facades\Route;
use Modules\MyDigitalAccounts\Http\Controllers\MyDigitalAccountsController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('mydigitalaccounts', MyDigitalAccountsController::class)->names('mydigitalaccounts');
});
