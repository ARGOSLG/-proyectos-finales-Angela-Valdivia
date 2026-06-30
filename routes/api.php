<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\IoTController;
use App\Http\Controllers\Api\ProtocolController;
use App\Http\Controllers\Api\SafePointController;
use Illuminate\Support\Facades\Route;

// ─── Rutas públicas ───────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
});

// ─── Rutas protegidas con token ───────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me',      [AuthController::class, 'me']);

    // Solo admin
    Route::middleware('role:admin')->group(function () {
        Route::get('admin/test', function () {
            return response()->json(['message' => 'Eres admin, tienes acceso']);
        });
    });

    // Protocolos
    Route::prefix('protocols')->group(function () {
        Route::get('/',                           [ProtocolController::class, 'index']);
        Route::post('/',                          [ProtocolController::class, 'store']);
        Route::get('/{id}',                       [ProtocolController::class, 'show']);
        Route::post('/{id}/execute',              [ProtocolController::class, 'execute']);
        Route::patch('/executions/{id}/step',     [ProtocolController::class, 'updateStep']);
        Route::post('/executions/{id}/motor-cut', [ProtocolController::class, 'motorCut']);
    });

    // Puntos seguros
    Route::prefix('safe-points')->group(function () {
        Route::get('/',        [SafePointController::class, 'index']);
        Route::post('/',       [SafePointController::class, 'store']);
        Route::get('/nearby',  [SafePointController::class, 'nearby']);
        Route::get('/{id}',    [SafePointController::class, 'show']);
        Route::put('/{id}',    [SafePointController::class, 'update']);
        Route::delete('/{id}', [SafePointController::class, 'destroy']);
    });

});

// ─── Rutas IoT — device token ─────────────────────────────────
Route::middleware('device.token')->prefix('iot')->group(function () {
    Route::post('audio-event',    [IoTController::class, 'audioEvent']);
    Route::post('location',       [IoTController::class, 'location']);
    Route::post('location/batch', [IoTController::class, 'locationBatch']);
});
