<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\AuditLog;
use App\Models\DeviceToken;
use App\Models\VehicleLocation;
use App\Models\Trip;
use Illuminate\Http\Request;

class IoTController extends Controller
{
    /**
     * AUDIO EVENT — Arduino detectó una palabra clave
     * POST /api/iot/audio-event
     * Header: X-Device-Token: {token}
     */
    public function audioEvent(Request $request)
    {
        // 1. Validar datos que manda el Arduino
        $request->validate([
            'keyword'    => 'required|string',   // palabra detectada
            'confidence' => 'required|numeric',  // qué tan seguro está 0-1
            'duration'   => 'nullable|string',   // duración de la detección
            'lat'        => 'nullable|numeric',
            'lng'        => 'nullable|numeric',
        ]);

        // 2. Obtener el dispositivo autenticado
        $device = $request->device;
        $vehicle = $device->vehicle;

        // 3. Determinar severidad según la palabra detectada
        $criticalKeywords = ['ayuda', 'auxilio', 'pistola', 'robo', 'asalto'];
        $severity = in_array(
            strtolower($request->keyword),
            $criticalKeywords
        ) ? 'critical' : 'warning';

        // 4. Crear la alerta
        $alert = Alert::create([
            'company_id' => $vehicle->company_id,
            'driver_id'  => $vehicle->driver_id,
            'vehicle_id' => $vehicle->id,
            'type'       => 'keyword_detected',
            'severity'   => $severity,
            'source'     => 'arduino',
            'metadata'   => [
                'keyword'    => $request->keyword,
                'confidence' => $request->confidence,
                'duration'   => $request->duration,
            ],
            'status' => 'active',
        ]);

        // 5. Actualizar última posición del vehículo si viene GPS
        if ($request->lat && $request->lng) {
            $vehicle->update([
                'lat'         => $request->lat,
                'lng'         => $request->lng,
                'location_at' => now(),
            ]);
        }

        // 6. Registrar en bitácora
        AuditLog::register('audio_event', 'alerts', $alert->id, [
            'keyword'  => $request->keyword,
            'severity' => $severity,
        ]);

        return response()->json([
            'message'  => 'Alerta registrada correctamente',
            'alert_id' => $alert->id,
            'severity' => $severity,
        ], 201);
    }

    /**
     * LOCATION — Módulo GPS manda posición cada 10s
     * POST /api/iot/location
     * Header: X-Device-Token: {token}
     */
    public function location(Request $request)
    {
        // 1. Validar datos del GPS
        $request->validate([
            'lat'         => 'required|numeric',
            'lng'         => 'required|numeric',
            'speed_kmh'   => 'nullable|numeric',
            'heading'     => 'nullable|numeric',
            'accuracy_m'  => 'nullable|numeric',
            'carrier'     => 'nullable|string',   // telcel/att/movistar
            'signal_dbm'  => 'nullable|integer',
            'recorded_at' => 'nullable|date',     // fecha real del dispositivo
        ]);

        $device  = $request->device;
        $vehicle = $device->vehicle;

        // 2. Guardar en historial de ubicaciones
        VehicleLocation::create([
            'vehicle_id'  => $vehicle->id,
            'lat'         => $request->lat,
            'lng'         => $request->lng,
            'speed_kmh'   => $request->speed_kmh,
            'heading'     => $request->heading,
            'accuracy_m'  => $request->accuracy_m,
            'carrier'     => $request->carrier,
            'signal_dbm'  => $request->signal_dbm,
            // Si viene fecha del dispositivo la usamos, si no usamos ahora
            'recorded_at' => $request->recorded_at ?? now(),
        ]);

        // 3. Actualizar posición actual del vehículo
        $vehicle->update([
            'lat'         => $request->lat,
            'lng'         => $request->lng,
            'location_at' => now(),
        ]);

        // 4. Actualizar last_seen del dispositivo
        $device->markAsSeen();

        // 5. Verificar si la velocidad es excesiva — alerta automática
        if ($request->speed_kmh && $request->speed_kmh > 120) {
            Alert::create([
                'company_id' => $vehicle->company_id,
                'driver_id'  => $vehicle->driver_id,
                'vehicle_id' => $vehicle->id,
                'type'       => 'speeding',
                'severity'   => $request->speed_kmh > 140 ? 'critical' : 'warning',
                'source'     => 'gps_module',
                'metadata'   => [
                    'speed_kmh' => $request->speed_kmh,
                    'lat'       => $request->lat,
                    'lng'       => $request->lng,
                ],
                'status' => 'active',
            ]);
        }

        // 6. Verificar frenada brusca — comparar con última velocidad registrada
        if ($request->speed_kmh !== null) {
            $lastLocation = VehicleLocation::where('vehicle_id', $vehicle->id)
                ->orderByDesc('recorded_at')
                ->skip(1)
                ->first();

            if ($lastLocation && $lastLocation->speed_kmh !== null) {
                $speedDrop = $lastLocation->speed_kmh - $request->speed_kmh;

                if ($speedDrop >= 30) {
                    Alert::create([
                        'company_id' => $vehicle->company_id,
                        'driver_id'  => $vehicle->driver_id,
                        'vehicle_id' => $vehicle->id,
                        'type'       => 'harsh_braking',
                        'severity'   => $speedDrop >= 50 ? 'critical' : 'warning',
                        'source'     => 'gps_module',
                        'metadata'   => [
                            'speed_before_kmh' => $lastLocation->speed_kmh,
                            'speed_after_kmh'  => $request->speed_kmh,
                            'speed_drop_kmh'   => $speedDrop,
                            'lat'              => $request->lat,
                            'lng'              => $request->lng,
                        ],
                        'status' => 'active',
                    ]);
                }
            }
        }

        return response()->json([
            'message' => 'Ubicación registrada',
        ], 201);
    }

    /**
     * BATCH LOCATION — Envío de ubicaciones guardadas offline
     * POST /api/iot/location/batch
     * Header: X-Device-Token: {token}
     */
    public function locationBatch(Request $request)
    {
        $request->validate([
            'locations'             => 'required|array|max:500',
            'locations.*.lat'       => 'required|numeric',
            'locations.*.lng'       => 'required|numeric',
            'locations.*.recorded_at' => 'required|date',
            'locations.*.speed_kmh' => 'nullable|numeric',
            'locations.*.carrier'   => 'nullable|string',
        ]);

        $device  = $request->device;
        $vehicle = $device->vehicle;

        // Insertar todas las ubicaciones de golpe
        $locations = collect($request->locations)->map(fn($loc) => [
            'id'          => \Illuminate\Support\Str::uuid(),
            'vehicle_id'  => $vehicle->id,
            'lat'         => $loc['lat'],
            'lng'         => $loc['lng'],
            'speed_kmh'   => $loc['speed_kmh'] ?? null,
            'carrier'     => $loc['carrier'] ?? null,
            'recorded_at' => $loc['recorded_at'],
        ]);

        VehicleLocation::insert($locations->toArray());

        // Actualizar posición actual con la más reciente
        $latest = collect($request->locations)
            ->sortByDesc('recorded_at')
            ->first();

        $vehicle->update([
            'lat'         => $latest['lat'],
            'lng'         => $latest['lng'],
            'location_at' => now(),
        ]);

        $device->markAsSeen();

        return response()->json([
            'message' => 'Lote de ubicaciones registrado',
            'count'   => $locations->count(),
        ], 201);
    }
}