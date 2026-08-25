<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VehicleSensor;
use Illuminate\Http\Request;

class VehicleSensorController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'seat_occupied' => 'required|boolean',
            'fifth_wheel_locked' => 'required|boolean',
            'landing_gear_attached' => 'required|boolean',
        ]);

        $sensor = VehicleSensor::create([
            'vehicle_id' => $request->device->vehicle_id,
            ...$validated,
            'recorded_at' => now(),
        ]);

        $request->device->markAsSeen();

        app(\App\Services\GoldenRuleService::class)->evaluate($request->device->vehicle);

        return response()->json($sensor, 201);
    }

    public function index(\App\Models\Vehicle $vehicle)
    {
        $latest = VehicleSensor::where('vehicle_id', $vehicle->id)
            ->latest('recorded_at')
            ->first();

        if (!$latest) {
            return response()->json(['message' => 'No hay lecturas de sensores para este vehículo'], 404);
        }

        return response()->json($latest);
    }
}