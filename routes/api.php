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
use App\Http\Controllers\Api\Dimas\ReporteController as DimasReporteController;
use App\Http\Controllers\Api\VehicleSensorController;
use Illuminate\Support\Facades\Route;

// ─── Rutas públicas ───────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
});

// ─── Rutas protegidas con token ───────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me',      [AuthController::class, 'me']);

    Route::middleware(['role:admin'])->group(function () {
        Route::get('admin/test', function () {
            return response()->json(['message' => 'Eres admin, tienes acceso']);
        });
    });

    Route::middleware(['role:admin,supervisor'])->group(function () {
        Route::get('companies',            [CompanyController::class, 'index']);
        Route::get('companies/{company}',  [CompanyController::class, 'show']);
    });
    Route::middleware(['role:admin'])->group(function () {
        Route::post('companies',              [CompanyController::class, 'store']);
        Route::put('companies/{company}',     [CompanyController::class, 'update']);
        Route::patch('companies/{company}',   [CompanyController::class, 'update']);
        Route::delete('companies/{company}',  [CompanyController::class, 'destroy']);
    });

 
    Route::apiResource('drivers', DriverController::class);

    // ── Vehicles: admin CRUD completo, supervisor CRUD sin borrar, operator lectura + actualizar ──
    Route::middleware(['role:admin,supervisor,operator'])->group(function () {
        Route::get('vehicles',             [VehicleController::class, 'index']);
        Route::get('vehicles/{vehicle}',   [VehicleController::class, 'show']);
    });
    Route::middleware(['role:admin,supervisor'])->group(function () {
        Route::post('vehicles',            [VehicleController::class, 'store']);
    });
    Route::middleware(['role:admin,supervisor,operator'])->group(function () {
       
        Route::put('vehicles/{vehicle}',   [VehicleController::class, 'update']);
        Route::patch('vehicles/{vehicle}', [VehicleController::class, 'update']);
    });
    Route::middleware(['role:admin'])->group(function () {
        Route::delete('vehicles/{vehicle}', [VehicleController::class, 'destroy']);
    });

    // Telemetría / sensores del vehículo 
    Route::get('vehicles/{vehicle}/locations',      [VehicleLocationController::class, 'index']);
    Route::get('vehicles/{vehicle}/sensors',        [VehicleSensorController::class, 'index']);
    Route::post('vehicles/{vehicle}/locations',     [VehicleLocationController::class, 'store']);
    Route::get('vehicles/{vehicle}/locations/last', [VehicleLocationController::class, 'last']);

    Route::get('incidents/{incident}/evidences',    [IncidentEvidenceController::class, 'index']);
    Route::middleware(['role:admin,supervisor'])->group(function () {
        Route::post('incidents/{incident}/evidences',   [IncidentEvidenceController::class, 'store']);
    });
    Route::middleware(['role:admin'])->group(function () {
        Route::delete('incidents/{incident}/evidences/{evidence}', [IncidentEvidenceController::class, 'destroy']);
    });

    // ── Protocolos: admin + supervisor CRUD, operator solo lectura ──
    Route::prefix('protocols')->group(function () {
        Route::middleware(['role:admin,supervisor,operator'])->group(function () {
            Route::get('/',     [ProtocolController::class, 'index']);
            Route::get('/{id}', [ProtocolController::class, 'show']);
        });
        Route::middleware(['role:admin,supervisor'])->group(function () {
            Route::post('/',                          [ProtocolController::class, 'store']);
            Route::post('/{id}/execute',              [ProtocolController::class, 'execute']);
            Route::patch('/executions/{id}/step',     [ProtocolController::class, 'updateStep']);
            Route::post('/executions/{id}/motor-cut', [ProtocolController::class, 'motorCut']);
        });
    });

    // ── Puntos seguros: admin + supervisor CRUD, operator solo lectura ──
    Route::prefix('safe-points')->group(function () {
        Route::middleware(['role:admin,supervisor,operator'])->group(function () {
            Route::get('/',       [SafePointController::class, 'index']);
            Route::get('/nearby', [SafePointController::class, 'nearby']);
            Route::get('/{id}',   [SafePointController::class, 'show']);
        });
        Route::middleware(['role:admin,supervisor'])->group(function () {
            Route::post('/',       [SafePointController::class, 'store']);
            Route::put('/{id}',    [SafePointController::class, 'update']);
        });
        Route::middleware(['role:admin'])->group(function () {
            Route::delete('/{id}', [SafePointController::class, 'destroy']);
        });
    });

    // ── Evidencias: admin CRUD, supervisor lectura + crear, operator solo lectura ──
    Route::prefix('evidence')->group(function () {
        Route::middleware(['role:admin,supervisor,operator'])->group(function () {
            Route::get('/',              [EvidenceController::class, 'index']);
            Route::get('/{id}/download', [EvidenceController::class, 'download']);
        });
        Route::middleware(['role:admin,supervisor'])->group(function () {
            Route::post('/', [EvidenceController::class, 'store']);
        });
    });

});


// ─── Rutas IoT — device token ─────────────────────────────────
Route::middleware('device.token')->prefix('iot')->group(function () {
    Route::post('camera',         [CameraController::class, 'analyze']);
    Route::post('audio-event',    [IoTController::class, 'audioEvent']);
    Route::post('location',       [IoTController::class, 'location']);
    Route::post('location/batch', [IoTController::class, 'locationBatch']);
    Route::post('sensors',        [VehicleSensorController::class, 'store']);
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

        // Reportes
        Route::get('reportes/activo',         [DimasReporteController::class, 'reporteActivo']);
        Route::post('reportes',               [DimasReporteController::class, 'store']);
        Route::get('reportes',                [DimasReporteController::class, 'index']);
        Route::get('reportes/{id}',           [DimasReporteController::class, 'show']);
        Route::post('reportes/{id}/cancelar', [DimasReporteController::class, 'cancelar']);
        Route::patch('reportes/{id}/estado',  [DimasReporteController::class, 'actualizarEstado']);
    });

});