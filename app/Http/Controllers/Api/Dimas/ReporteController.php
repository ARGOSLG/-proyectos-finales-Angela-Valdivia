<?php

namespace App\Http\Controllers\Api\Dimas;

use App\Events\AlertCreated;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessAlert;
use App\Models\Alert;
use App\Models\Reporte;
use Illuminate\Http\Request;
use App\Events\ReporteActualizado;

class ReporteController extends Controller
{
    /**
     * Crear reporte — genera alerta en panel admin automáticamente
     * POST /api/dimas/reportes
     */
    public function store(Request $request)
    {
        $request->validate([
            'tipo'        => 'required|in:emergencia,auxilio_vial,palabra_clave,comportamiento,desaceleracion',
            'lat'         => 'nullable|numeric',
            'lng'         => 'nullable|numeric',
            'description' => 'nullable|string',
            'metadata'    => 'nullable|array',
        ]);

        $driver = $request->user();
        $vehicle = $driver->vehicles()->where('status', 'on_route')->first();

        // 1. Crear alerta para el panel admin
        $alert = Alert::create([
            'company_id' => $driver->company_id,
            'driver_id'  => $driver->id,
            'vehicle_id' => $vehicle?->id,
            'type'       => $request->tipo === 'emergencia' ? 'sos_button' : 'manual',
            'severity'   => $request->tipo === 'emergencia' ? 'critical' : 'warning',
            'source'     => 'manual',
            'metadata'   => array_merge($request->metadata ?? [], [
                'lat'         => $request->lat,
                'lng'         => $request->lng,
                'description' => $request->description,
                'trigger'     => 'dimas_app',
            ]),
            'status' => 'active',
        ]);

        // 2. Crear reporte del conductor vinculado a la alerta
        $reporte = Reporte::create([
            'driver_id'   => $driver->id,
            'alert_id'    => $alert->id,
            'tipo'        => $request->tipo,
            'estado'      => 'enviado',
            'lat'         => $request->lat,
            'lng'         => $request->lng,
            'description' => $request->description,
            'metadata'    => $request->metadata,
        ]);

        // 3. Notificar panel admin por WhatsApp y WebSocket
        ProcessAlert::dispatch($alert);
        AlertCreated::dispatch($alert->load('vehicle', 'driver'));

        return response()->json([
            'message'    => 'Reporte enviado correctamente',
            'reporte_id' => $reporte->id,
            'alert_id'   => $alert->id,
            'estado'     => $reporte->estado,
        ], 201);
    }

    /**
     * Ver reporte específico
     * GET /api/dimas/reportes/{id}
     */
    public function show(Request $request, string $id)
    {
        $reporte = Reporte::where('driver_id', $request->user()->id)
                          ->with('alert')
                          ->findOrFail($id);

        return response()->json($reporte);
    }

    /**
     * Cancelar reporte
     * POST /api/dimas/reportes/{id}/cancelar
     */
    public function cancelar(Request $request, string $id)
    {
        $reporte = Reporte::where('driver_id', $request->user()->id)
                          ->findOrFail($id);
        $reporte->update(['estado' => 'cancelado']);

        ReporteActualizado::dispatch($reporte);

        // Resolver también la alerta
        if ($reporte->alert) {
            $reporte->alert->update([
                'status'      => 'resolved',
                'resolved_at' => now(),
            ]);
        }

        return response()->json([
            'message' => 'Reporte cancelado',
        ]);
    }

    /**
     * Historial de reportes del conductor
     * GET /api/dimas/reportes
     */
    public function index(Request $request)
    {
        $reportes = Reporte::where('driver_id', $request->user()->id)
                           ->orderByDesc('created_at')
                           ->paginate(20);

        return response()->json($reportes);
    }

    /**
     * Actualizar estado del reporte
     * POST /api/dimas/reportes/{id}/estado
     */
    public function actualizarEstado(Request $request, string $id)
    {
        $request->validate([
            'estado' => 'required|in:enviado,confirmado,en_progreso,resuelto,cancelado',
        ]);

        $reporte = Reporte::findOrFail($id);

        $reporte->update(['estado' => $request->estado]);

        // Notificar al conductor en tiempo real via WebSocket
        ReporteActualizado::dispatch($reporte);

        return response()->json([
            'message' => 'Estado actualizado',
            'reporte' => $reporte,
        ]);
    }
}
