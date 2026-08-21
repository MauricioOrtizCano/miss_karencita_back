<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LeadController;

Route::group(['prefix' => 'leads'], function () {
    Route::post('/', [LeadController::class, 'storeLeads'])
        ->middleware('throttle:leads');
});