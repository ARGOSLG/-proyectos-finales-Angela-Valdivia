<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    // GET /api/drivers
    public function index()
    {
        $drivers = Driver::with('company')->get();

        return response()->json([
            'success' => true,
            'data'    => $drivers,
        ]);
    }

    // POST /api/drivers
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id'              => 'required|uuid|exists:companies,id',
            'name'                    => 'required|string|max:255',
            'employee_id'             => 'nullable|string|max:255',
            'phone'                   => 'nullable|string|max:20',
            'license_number'          => 'nullable|string|max:255',
            'emergency_contact_name'  => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'status'                  => 'in:available,on_route,resting,inactive',
        ]);

        $driver = Driver::create($validated);

        return response()->json([
            'success' => true,
            'data'    => $driver,
        ], 201);
    }

    // GET /api/drivers/{id}
    public function show(Driver $driver)
    {
        return response()->json([
            'success' => true,
            'data'    => $driver->load('company'),
        ]);
    }

    // PUT /api/drivers/{id}
    public function update(Request $request, Driver $driver)
    {
        $validated = $request->validate([
            'company_id'              => 'sometimes|uuid|exists:companies,id',
            'name'                    => 'sometimes|string|max:255',
            'employee_id'             => 'nullable|string|max:255',
            'phone'                   => 'nullable|string|max:20',
            'license_number'          => 'nullable|string|max:255',
            'emergency_contact_name'  => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'status'                  => 'in:available,on_route,resting,inactive',
        ]);

        $driver->update($validated);

        return response()->json([
            'success' => true,
            'data'    => $driver,
        ]);
    }

    // DELETE /api/drivers/{id}
    public function destroy(Driver $driver)
    {
        $driver->delete();

        return response()->json([
            'success' => true,
            'message' => 'Conductor eliminado correctamente',
        ]);
    }
}