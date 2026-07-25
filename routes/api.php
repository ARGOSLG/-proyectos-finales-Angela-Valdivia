<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CameraController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\DriverController;
use App\Http\Controllers\Api\EvidenceController;
use App\Http\Controllers\Api\IncidentEvidenceController;
use App\Http\Controllers\Api\IoTController;
use App\Http\Controllers\Api\ProtocolController;
use App\Http\Controllers\Api\SafePointController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\VehicleLocationController;
use App\Http\Controllers\Api\Dimas\AuthController as DimasAuthController;
use App\Http\Controllers\Api\Dimas\EmergencyController as DimasEmergencyController;
use Illuminate\Support\Facades\Route;

// ─── Rutas públicas ───────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
});

// ─── Rutas protegidas con token ───────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me',      [AuthController::class, 'me']);

    Route::middleware('role:admin')->group(function () {
        Route::get('admin/test', function () {
            return response()->json(['message' => 'Eres admin, tienes acceso']);
        });
    });

    // Flota
    Route::apiResource('companies', CompanyController::class);
    Route::apiResource('drivers',   DriverController::class);
    Route::apiResource('vehicles',  VehicleController::class);

    Route::get('vehicles/{vehicle}/locations',      [VehicleLocationController::class, 'index']);
    Route::post('vehicles/{vehicle}/locations',     [VehicleLocationController::class, 'store']);
    Route::get('vehicles/{vehicle}/locations/last', [VehicleLocationController::class, 'last']);

    Route::get('incidents/{incident}/evidences',    [IncidentEvidenceController::class, 'index']);
    Route::post('incidents/{incident}/evidences',   [IncidentEvidenceController::class, 'store']);
    Route::delete('incidents/{incident}/evidences/{evidence}', [IncidentEvidenceController::class, 'destroy']);

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

    // Evidencias
    Route::prefix('evidence')->group(function () {
        Route::get('/',              [EvidenceController::class, 'index']);
        Route::post('/',             [EvidenceController::class, 'store']);
        Route::get('/{id}/download', [EvidenceController::class, 'download']);
    });

});

// ─── Rutas IoT — device token ─────────────────────────────────
Route::middleware('device.token')->prefix('iot')->group(function () {
    Route::post('camera',         [CameraController::class, 'analyze']);
    Route::post('audio-event',    [IoTController::class, 'audioEvent']);
    Route::post('location',       [IoTController::class, 'location']);
    Route::post('location/batch', [IoTController::class, 'locationBatch']);
});

// ─── Rutas DIMAS — App del conductor ─────────────────────────
Route::prefix('dimas')->group(function () {

    // Públicas — login
    Route::post('login',     [DimasAuthController::class, 'login']);
    Route::post('login-nfc', [DimasAuthController::class, 'loginNfc']);

    // Protegidas — requieren token del conductor
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout',  [DimasAuthController::class, 'logout']);
        Route::get('perfil',   [DimasAuthController::class, 'perfil']);

        // Emergencias
        Route::post('emergencia',                 [DimasEmergencyController::class, 'sos']);
        Route::post('auxilio-vial',               [DimasEmergencyController::class, 'auxilioVial']);
        Route::post('auxilio-vial/{id}/cancelar', [DimasEmergencyController::class, 'cancelarAuxilio']);
    });

});