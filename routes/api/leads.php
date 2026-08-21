<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LeadController;

Route::group(['prefix' => 'leads'], function () {
    Route::post('/', [LeadController::class, 'store'])
        ->middleware('throttle:leads');
});