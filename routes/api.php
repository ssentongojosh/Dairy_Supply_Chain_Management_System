<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DocumentController; // Make sure this import is present

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Group for document processing related API endpoints, protected by Sanctum
Route::middleware('auth:sanctum')->prefix('v1')->name('api.v1.')->group(function () {

    // TEMPORARILY MODIFIED: Changed {user} to {userId}
    Route::get('/users/{userId}/business-document', [DocumentController::class, 'downloadBusinessDocument'])
        ->name('users.business-document.download');

    // Endpoint for the Java server to update a user's verification status
    // Example: POST /api/v1/users/123/verify-document
    // {user} will be resolved to a User model instance
    Route::post('/users/{user}/verify-document', [DocumentController::class, 'updateVerificationStatus'])
        ->name('users.verify-document.update')
        // Optional: You could add specific middleware for abilities here if needed
        // ->middleware('ability:update:verification')
        ;

});
