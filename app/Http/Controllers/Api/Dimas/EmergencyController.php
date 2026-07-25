<?php

namespace App\Http\Controllers\Api\Dimas;

use App\Http\Controllers\Controller;
use App\Events\AlertCreated;
use App\Jobs\ProcessAlert;
use App\Models\Alert;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class EmergencyController extends Controller
{
    /**
     * Botón SOS — emergencia crítica del conductor
     * POST /api/dimas/emergencia
     */
    public function sos(Request $request)
    {
        $request->validate([
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
        ]);

        $driver  = $request->user();
        $vehicle = $driver->vehicles()->where('status', 'on_route')->first();

        // Crear alerta crítica
        $alert = Alert::create([
            'company_id' => $driver->company_id,
            'driver_id'  => $driver->id,
            'vehicle_id' => $vehicle?->id,
            'type'       => 'sos_button',
            'severity'   => 'critical',
            'source'     => 'manual',
            'metadata'   => [
                'lat'     => $request->lat,
                'lng'     => $request->lng,
                'trigger' => 'driver_sos_button',
            ],
            'status' => 'active',
        ]);

        // Actualizar posición del vehículo
        if ($vehicle && $request->lat && $request->lng) {
            $vehicle->update([
                'lat'         => $request->lat,
                'lng'         => $request->lng,
                'location_at' => now(),
            ]);
        }

        // Disparar WhatsApp y WebSocket
        ProcessAlert::dispatch($alert);
        AlertCreated::dispatch($alert->load('vehicle', 'driver'));

        AuditLog::register('sos_activated', 'alerts', $alert->id, [
            'driver_id' => $driver->id,
        ]);

        return response()->json([
            'message'  => 'SOS activado — monitoristas notificados',
            'alert_id' => $alert->id,
        ], 201);
    }

    /**
     * Auxilio vial — conductor necesita ayuda mecánica
     * POST /api/dimas/auxilio-vial
     */
    public function auxilioVial(Request $request)
    {
        $request->validate([
            'lat'         => 'nullable|numeric',
            'lng'         => 'nullable|numeric',
            'description' => 'nullable|string',
        ]);

        $driver  = $request->user();
        $vehicle = $driver->vehicles()->where('status', 'on_route')->first();

        $alert = Alert::create([
            'company_id' => $driver->company_id,
            'driver_id'  => $driver->id,
            'vehicle_id' => $vehicle?->id,
            'type'       => 'manual',
            'severity'   => 'warning',
            'source'     => 'manual',
            'metadata'   => [
                'lat'         => $request->lat,
                'lng'         => $request->lng,
                'description' => $request->description ?? 'Auxilio vial solicitado',
                'trigger'     => 'driver_auxilio_vial',
            ],
            'status' => 'active',
        ]);

        ProcessAlert::dispatch($alert);
        AlertCreated::dispatch($alert->load('vehicle', 'driver'));

        AuditLog::register('auxilio_vial', 'alerts', $alert->id);

        return response()->json([
            'message'  => 'Auxilio vial solicitado — monitoristas notificados',
            'alert_id' => $alert->id,
        ], 201);
    }

    /**
     * Cancelar auxilio vial
     * POST /api/dimas/auxilio-vial/{id}/cancelar
     */
    public function cancelarAuxilio(Request $request, string $id)
    {
        $driver = $request->user();

        $alert = Alert::where('id', $id)
                      ->where('driver_id', $driver->id)
                      ->where('type', 'manual')
                      ->firstOrFail();

        $alert->update([
            'status'      => 'resolved',
            'resolved_at' => now(),
        ]);

        AuditLog::register('auxilio_vial_cancelado', 'alerts', $alert->id);

        return response()->json([
            'message' => 'Auxilio vial cancelado',
        ]);
    }
}