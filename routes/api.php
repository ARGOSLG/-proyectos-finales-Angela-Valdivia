<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\DriverController;
use Illuminate\Support\Facades\Route;

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

});


Route::apiResource('companies', CompanyController::class);
Route::apiResource('drivers', DriverController::class);