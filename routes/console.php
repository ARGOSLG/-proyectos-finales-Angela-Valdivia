<?php

use App\Models\Company;
use App\Models\DeviceToken;
use App\Models\Vehicle;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('argos:test-data', function () {
    $company = Company::first();
    $vehicle = Vehicle::create([
        'company_id'  => $company->id,
        'unit_number' => 'EW1015',
        'plate'       => 'ABC-123',
        'brand'       => 'Kenworth',
        'model'       => 'T680',
        'status'      => 'on_route',
    ]);
    DeviceToken::create([
        'vehicle_id'  => $vehicle->id,
        'device_type' => 'arduino_mic',
        'token'       => 'test-arduino-token-001',
        'active'      => true,
    ]);
    DeviceToken::create([
        'vehicle_id'  => $vehicle->id,
        'device_type' => 'gps_module',
        'token'       => 'test-gps-token-001',
        'active'      => true,
    ]);
    $this->info('Datos de prueba creados correctamente');
})->purpose('Crea datos de prueba para IoT');