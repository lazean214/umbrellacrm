<?php

use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\DealController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::apiResource('contacts', ContactController::class)->names('api.contacts');
    Route::apiResource('deals', DealController::class)->names('api.deals');
    Route::apiResource('users', UserController::class)->names('api.users');
});
