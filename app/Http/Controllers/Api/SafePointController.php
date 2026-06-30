<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SafePoint;
use Illuminate\Http\Request;

class SafePointController extends Controller
{
    /**
     * Listar puntos seguros de la empresa
     * GET /api/safe-points
     */
    public function index(Request $request)
    {
        $safePoints = SafePoint::where('company_id', $request->user()->company_id)
                               ->where('active', true)
                               ->get();

        return response()->json($safePoints);
    }

    /**
     * Crear punto seguro
     * POST /api/safe-points
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'    => 'required|string',
            'address' => 'required|string',
            'lat'     => 'required|numeric',
            'lng'     => 'required|numeric',
            'type'    => 'required|in:gas_station,police,hospital,base,other',
        ]);

        $safePoint = SafePoint::create([
            'company_id' => $request->user()->company_id,
            'name'       => $request->name,
            'address'    => $request->address,
            'lat'        => $request->lat,
            'lng'        => $request->lng,
            'type'       => $request->type,
            'active'     => true,
        ]);

        AuditLog::register('created', 'safe_points', $safePoint->id);

        return response()->json($safePoint, 201);
    }

    /**
     * Ver punto seguro
     * GET /api/safe-points/{id}
     */
    public function show(Request $request, string $id)
    {
        $safePoint = SafePoint::where('company_id', $request->user()->company_id)
                              ->findOrFail($id);

        return response()->json($safePoint);
    }

    /**
     * Actualizar punto seguro
     * PUT /api/safe-points/{id}
     */
    public function update(Request $request, string $id)
    {
        $safePoint = SafePoint::where('company_id', $request->user()->company_id)
                              ->findOrFail($id);

        $safePoint->update($request->only([
            'name', 'address', 'lat', 'lng', 'type', 'active'
        ]));

        AuditLog::register('updated', 'safe_points', $safePoint->id);

        return response()->json($safePoint);
    }

    /**
     * Eliminar punto seguro
     * DELETE /api/safe-points/{id}
     */
    public function destroy(Request $request, string $id)
    {
        $safePoint = SafePoint::where('company_id', $request->user()->company_id)
                              ->findOrFail($id);

        $safePoint->update(['active' => false]);

        AuditLog::register('deleted', 'safe_points', $safePoint->id);

        return response()->json(['message' => 'Punto seguro eliminado']);
    }

    /**
     * Puntos seguros cercanos a una ubicación
     * GET /api/safe-points/nearby?lat=19.43&lng=-99.13&radius=10
     */
    public function nearby(Request $request)
    {
        $request->validate([
            'lat'    => 'required|numeric',
            'lng'    => 'required|numeric',
            'radius' => 'nullable|numeric|min:1|max:100',
        ]);

        $radius = $request->radius ?? 10; // km por default

        // Fórmula Haversine en SQL para calcular distancia
        $safePoints = SafePoint::where('company_id', $request->user()->company_id)
            ->where('active', true)
            ->selectRaw("*, 
                (6371 * acos(
                    cos(radians(?)) * cos(radians(lat)) * 
                    cos(radians(lng) - radians(?)) + 
                    sin(radians(?)) * sin(radians(lat))
                )) AS distance", 
                [$request->lat, $request->lng, $request->lat]
            )
            ->having('distance', '<=', $radius)
            ->orderBy('distance')
            ->get();

        return response()->json($safePoints);
    }
}