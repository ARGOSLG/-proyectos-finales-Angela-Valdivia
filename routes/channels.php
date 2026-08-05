<?php

use App\Models\Driver;
use Illuminate\Support\Facades\Broadcast;

// Canal del usuario/operador
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Canal privado por empresa — panel admin recibe alertas
Broadcast::channel('company.{companyId}', function ($user, $companyId) {
    return $user->company_id === $companyId;
});

// Canal privado por conductor — app DIMAS recibe actualizaciones de reportes
Broadcast::channel('driver.{driverId}', function ($user, $driverId) {
    // Verificar que es el conductor correcto
    if ($user instanceof Driver) {
        return $user->id === $driverId;
    }
    // O un operador de la misma empresa
    $driver = Driver::find($driverId);
    return $driver && $user->company_id === $driver->company_id;
});