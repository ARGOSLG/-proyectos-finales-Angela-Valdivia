<?php
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\DriverController;
use App\Http\Controllers\Api\VehicleController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\IoTController;
use App\Http\Controllers\Api\VehicleLocationController;

/*

| Todas las rutas empiezan con /api/ automáticamente
|
| Públicas  — no necesitan token (login)
| Protegidas — necesitan token en el header:
|              Authorization: Bearer {token}
|
*/

// ─── Rutas públicas — no necesitan token ─────────────────────
Route::prefix('auth')->group(function () {

    // POST /api/auth/login
    // body: { email, password }
    Route::post('login', [AuthController::class, 'login']);

});

// ─── Rutas protegidas — necesitan token ──────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // POST /api/auth/logout
    Route::post('auth/logout', [AuthController::class, 'logout']);

    // GET /api/auth/me
    Route::get('auth/me', [AuthController::class, 'me']);

    // Flota
    Route::apiResource('companies', CompanyController::class);
    Route::apiResource('drivers', DriverController::class);
    Route::apiResource('vehicles', VehicleController::class);

    // Ruta de prueba — solo admin puede acceder
    Route::middleware('role:admin')->group(function () {
        Route::get('admin/test', function () {
            return response()->json([
                'message' => 'Eres admin, tienes acceso'
            ]);
        });
    });

    // Ruta validacion de vehiculos
    Route::get('vehicles/{vehicle}/locations', [VehicleLocationController::class, 'index']);
    Route::post('vehicles/{vehicle}/locations', [VehicleLocationController::class, 'store']);
    Route::get('vehicles/{vehicle}/locations/last', [VehicleLocationController::class, 'last']);

});
//  Rutas IoT — autenticadas con device token 
// El Arduino y el módulo GPS usan estas rutas
// Header requerido: X-Device-Token: {token}
Route::middleware('device.token')->prefix('iot')->group(function () {

    // Arduino detectó palabra clave
    // POST /api/iot/audio-event
    Route::post('audio-event', [IoTController::class, 'audioEvent']);

    // Módulo GPS manda posición cada 10s
    // POST /api/iot/location
    Route::post('location', [IoTController::class, 'location']);

    // Módulo GPS manda lote de ubicaciones guardadas offline
    // POST /api/iot/location/batch
    Route::post('location/batch', [IoTController::class, 'locationBatch']);

});


