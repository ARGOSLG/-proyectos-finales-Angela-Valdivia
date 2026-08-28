<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\Vehicle;
use App\Models\VehicleLocation;
use App\Models\VehicleSensor;

class GoldenRuleService
{
    public function evaluate(Vehicle $vehicle): ?Alert
    {
        $sensor = VehicleSensor::where('vehicle_id', $vehicle->id)->latest('recorded_at')->first();
        $location = VehicleLocation::where('vehicle_id', $vehicle->id)->latest('recorded_at')->first();

        if (!$sensor || !$location) {
            return null;
        }

        $isMoving = ($location->speed_kmh ?? 0) > 0;
        $ignitionOff = $location->ignition === false;

        if (!$sensor->fifth_wheel_locked && $isMoving) {
            return $this->createGoldenAlert($vehicle, 'fifth_wheel_unlock', 'critical',
                'Desbloqueo de 5ª rueda detectado en movimiento', [
                    'speed_kmh' => $location->speed_kmh,
                ]);
        }

        if (!$sensor->landing_gear_attached && $isMoving && $ignitionOff) {
            return $this->createGoldenAlert($vehicle, 'trailer_theft_in_progress', 'critical',
                'Robo en proceso: patines retirados con movimiento y sin ignición registrada', [
                    'speed_kmh' => $location->speed_kmh,
                    'ignition' => $location->ignition,
                ]);
        }

        return null;
    }

    private function createGoldenAlert(Vehicle $vehicle, string $type, string $severity, string $description, array $metadata): Alert
    {
        $alert = Alert::create([
            'company_id' => $vehicle->company_id,
            'driver_id'  => $vehicle->driver_id,
            'vehicle_id' => $vehicle->id,
            'type'       => $type,
            'severity'   => $severity,
            'source'     => 'system',
            'metadata'   => array_merge($metadata, ['description' => $description]),
            'status'     => 'active',
        ]);

        \App\Events\AlertCreated::dispatch($alert->load('vehicle', 'driver'));

        return $alert;
    }
}