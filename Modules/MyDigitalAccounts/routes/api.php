<?php

use Illuminate\Support\Facades\Route;
use Modules\MyDigitalAccounts\Http\Controllers\MyDigitalAccountsController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('mydigitalaccounts', MyDigitalAccountsController::class)->names('mydigitalaccounts');
});
