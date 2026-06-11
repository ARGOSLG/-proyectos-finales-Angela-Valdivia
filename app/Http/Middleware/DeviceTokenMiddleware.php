<?php

namespace App\Http\Middleware;

use App\Models\DeviceToken;
use Closure;
use Illuminate\Http\Request;

class DeviceTokenMiddleware
{
    /**
     * Autentica dispositivos IoT (Arduino y módulo GPS)
     * El dispositivo manda su token en el header X-Device-Token
     */
    public function handle(Request $request, Closure $next): mixed
    {
        // 1. Obtener el token del header
        $token = $request->header('X-Device-Token');

        if (!$token) {
            return response()->json([
                'message' => 'Device token requerido'
            ], 401);
        }

        // 2. Buscar el dispositivo activo con ese token
        $device = DeviceToken::findByToken($token);

        if (!$device) {
            return response()->json([
                'message' => 'Dispositivo no autorizado'
            ], 401);
        }

        // 3. Cargar el vehículo relacionado
        $device->load('vehicle.company');

        // 4. Pasar el dispositivo al controlador
        $request->merge(['device' => $device]);
        $request->device = $device;

        return $next($request);
    }
}