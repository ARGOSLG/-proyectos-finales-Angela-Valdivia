<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\DriverController;

Route::apiResource('companies', CompanyController::class);
Route::apiResource('drivers', DriverController::class);