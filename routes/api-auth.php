<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\ApiAuthController;

// Public Auth Routes (Tidak butuh token)
Route::post('/login', [ApiAuthController::class, 'login']);

// Protected Auth Routes (Butuh token)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [ApiAuthController::class, 'logout']);

    Route::get('/me', function (Request $request) {
        return response()->json([
            'success' => true,
            'message' => 'User data retrieved',
            'data' => $request->user()
        ]);
    });
});
