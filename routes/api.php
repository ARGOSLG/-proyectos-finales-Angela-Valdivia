<?php
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\IoTController;
use App\Http\Controllers\Api\ProtocolController;

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

    // Ruta de prueba — solo admin puede acceder
    Route::middleware('role:admin')->group(function () {
        Route::get('admin/test', function () {
            return response()->json([
                'message' => 'Eres admin, tienes acceso'
            ]);
        });
    });
// ─── Protocolos ──────────────────────────────────────────────
Route::prefix('protocols')->group(function () {
    //Esta ruta es para listar los protocolos de la empresa
    Route::get('/',                          [ProtocolController::class, 'index']);
    Route::post('/',                         [ProtocolController::class, 'store']);
    Route::get('/{id}',                      [ProtocolController::class, 'show']);
    Route::post('/{id}/execute',             [ProtocolController::class, 'execute']);
    Route::patch('/executions/{id}/step',    [ProtocolController::class, 'updateStep']);
    Route::post('/executions/{id}/motor-cut',[ProtocolController::class, 'motorCut']);
});
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
