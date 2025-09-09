<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImpersonationController;

/*
|--------------------------------------------------------------------------
| Impersonation Routes
|--------------------------------------------------------------------------
|
| These routes handle admin impersonation functionality in the restaurant panel.
| They are session-based and don't rely on URL parameters.
|
*/

// Impersonation API routes
Route::prefix('api')->group(function () {
    // Check if there's an active impersonation session
    Route::get('/check-impersonation', [ImpersonationController::class, 'checkImpersonation']);
    
    // Process the impersonation and log in the user
    Route::post('/process-impersonation', [ImpersonationController::class, 'processImpersonation']);
    
    // End impersonation session
    Route::post('/end-impersonation', [ImpersonationController::class, 'endImpersonation']);
    
    // Get current impersonation status
    Route::get('/impersonation-status', [ImpersonationController::class, 'getImpersonationStatus']);
});
