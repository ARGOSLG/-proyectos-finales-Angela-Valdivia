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

        return response()->json($sensor, 201);
    }
}