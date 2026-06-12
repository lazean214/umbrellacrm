<?php

use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\DealController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {
<<<<<<< HEAD
    Route::apiResource('contacts', ContactController::class)->names('api.contacts');
    Route::apiResource('deals', DealController::class)->names('api.deals');
    Route::apiResource('users', UserController::class)->names('api.users');
=======
    Route::apiResource('contacts', ContactController::class);
    Route::apiResource('deals', DealController::class);
    Route::apiResource('users', UserController::class);
>>>>>>> 2e63ca614e8ce820dd4ded4c7c30f6ddc83b383c
});
