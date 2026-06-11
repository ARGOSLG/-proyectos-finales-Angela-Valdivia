<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    // GET /api/vehicles
    public function index()
    {
        $vehicles = Vehicle::with(['company', 'driver'])->get();

        return response()->json([
            'success' => true,
            'data'    => $vehicles,
        ]);
    }

    // POST /api/vehicles
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id'  => 'required|uuid|exists:companies,id',
            'driver_id'   => 'nullable|uuid|exists:drivers,id',
            'unit_number' => 'required|string|max:255',
            'plate'       => 'required|string|max:255|unique:vehicles',
            'brand'       => 'nullable|string|max:255',
            'model'       => 'nullable|string|max:255',
            'status'      => 'in:available,on_route,maintenance,inactive',
            'lat'         => 'nullable|numeric',
            'lng'         => 'nullable|numeric',
            'location_at' => 'nullable|date',
        ]);

        $vehicle = Vehicle::create($validated);

        return response()->json([
            'success' => true,
            'data'    => $vehicle,
        ], 201);
    }

    // GET /api/vehicles/{id}
    public function show(Vehicle $vehicle)
    {
        return response()->json([
            'success' => true,
            'data'    => $vehicle->load(['company', 'driver']),
        ]);
    }

    // PUT /api/vehicles/{id}
    public function update(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'company_id'  => 'sometimes|uuid|exists:companies,id',
            'driver_id'   => 'nullable|uuid|exists:drivers,id',
            'unit_number' => 'sometimes|string|max:255',
            'plate'       => 'sometimes|string|max:255|unique:vehicles,plate,' . $vehicle->id,
            'brand'       => 'nullable|string|max:255',
            'model'       => 'nullable|string|max:255',
            'status'      => 'in:available,on_route,maintenance,inactive',
            'lat'         => 'nullable|numeric',
            'lng'         => 'nullable|numeric',
            'location_at' => 'nullable|date',
        ]);

        $vehicle->update($validated);

        return response()->json([
            'success' => true,
            'data'    => $vehicle,
        ]);
    }

    // DELETE /api/vehicles/{id}
    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();

        return response()->json([
            'success' => true,
            'message' => 'Vehículo eliminado correctamente',
        ]);
    }
}