<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleLocation;
use Illuminate\Http\Request;

class VehicleLocationController extends Controller
{
    // POST /api/vehicles/{vehicle}/locations
    // La app del conductor manda su posición cada 10 segundos
    public function store(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'lat'         => 'required|numeric|between:-90,90',
            'lng'         => 'required|numeric|between:-180,180',
            'speed_kmh'   => 'nullable|numeric|min:0',
            'heading'     => 'nullable|numeric|between:0,360',
            'accuracy_m'  => 'nullable|numeric|min:0',
            'ignition'    => 'nullable|boolean',
            'carrier'     => 'nullable|string|max:50',
            'signal_dbm'  => 'nullable|integer',
            'recorded_at' => 'nullable|date',
        ]);

        $validated['vehicle_id'] = $vehicle->id;
        $validated['recorded_at'] = $validated['recorded_at'] ?? now();

        // Actualizar última posición conocida en el vehículo
        $vehicle->update([
            'lat'         => $validated['lat'],
            'lng'         => $validated['lng'],
            'location_at' => $validated['recorded_at'],
        ]);

        $location = VehicleLocation::create($validated);

        return response()->json([
            'success' => true,
            'data'    => $location,
        ], 201);
    }

    // GET /api/vehicles/{vehicle}/locations
    // Historial de rutas con filtros opcionales de fecha
    public function index(Request $request, Vehicle $vehicle)
    {
        $request->validate([
            'from'  => 'nullable|date',
            'to'    => 'nullable|date',
            'limit' => 'nullable|integer|min:1|max:1000',
        ]);

        $query = VehicleLocation::where('vehicle_id', $vehicle->id)
            ->orderByDesc('recorded_at');

        if ($request->from && $request->to) {
            $query->betweenDates($request->from, $request->to);
        }

        $limit = $request->limit ?? 100;
        $locations = $query->limit($limit)->get();

        return response()->json([
            'success' => true,
            'vehicle' => $vehicle->only(['id', 'unit_number', 'plate']),
            'total'   => $locations->count(),
            'data'    => $locations,
        ]);
    }

    // GET /api/vehicles/{vehicle}/locations/last
    // Última posición conocida del vehículo
    public function last(Vehicle $vehicle)
    {
        $location = VehicleLocation::where('vehicle_id', $vehicle->id)
            ->orderByDesc('recorded_at')
            ->first();

        return response()->json([
            'success' => true,
            'vehicle' => $vehicle->only(['id', 'unit_number', 'plate']),
            'data'    => $location,
        ]);
    }
}