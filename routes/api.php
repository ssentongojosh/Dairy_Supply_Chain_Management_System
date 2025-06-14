<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DocumentController;

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

// RE-ADD 'auth:sanctum' to this group
Route::middleware('auth:sanctum')->prefix('v1')->name('api.v1.')->group(function () {

    Route::get('/ping', function () {
        Log::info('API [DEBUG]: /api/v1/ping route hit!');
        return response()->json(['message' => 'pong from api/v1/ping']);
    })->name('ping');

    // Changed back to {user}
    Route::get('/users/{user}/business-document', [DocumentController::class, 'downloadBusinessDocument'])
        ->name('users.business-document.download');

    Route::post('/users/{user}/verify-document', [DocumentController::class, 'updateVerificationStatus'])
        ->name('users.verify-document.update');
});
