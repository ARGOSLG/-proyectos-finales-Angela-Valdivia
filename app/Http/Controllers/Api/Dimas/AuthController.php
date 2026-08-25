<?php

namespace App\Http\Controllers\Api\Dimas;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

 

class AuthController extends Controller
{
    /**
     * LOGIN con ID de empleado y contraseña
     * POST /api/dimas/login
     */
    public function login(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|string',
            'password'    => 'required|string',
        ]);

        // Buscar conductor por employee_id
        $driver = Driver::where('employee_id', $request->employee_id)
                        ->where('status', '!=', 'inactive')
                        ->first();

        if (!$driver || !Hash::check($request->password, $driver->password)) {
            return response()->json([
                'message' => 'Credenciales incorrectas'
            ], 401);
        }

        // Generar token para el conductor
        $token = $driver->createToken('dimas-app')->plainTextToken;

        AuditLog::register('driver_login', 'drivers', $driver->id);

        return response()->json([
            'token'  => $token,
            'driver' => [
                'id'          => $driver->id,
                'name'        => $driver->name,
                'employee_id' => $driver->employee_id,
                'vehicle'     => $driver->vehicles()->where('status', 'on_route')->first(),
            ]
        ]);
    }

    /**
     * LOGIN con NFC — placeholder para cuando se tenga el hardware
     * POST /api/dimas/login-nfc
     */
    public function loginNfc(Request $request)
    {
        $request->validate([
            'nfc_token' => 'required|string',
        ]);

        // Por ahora busca el conductor por nfc_token en la tabla drivers
        $driver = Driver::where('nfc_token', $request->nfc_token)
                        ->where('status', '!=', 'inactive')
                        ->first();

        if (!$driver) {
            return response()->json([
                'message' => 'NFC no reconocido'
            ], 401);
        }

        $token = $driver->createToken('dimas-app-nfc')->plainTextToken;

        AuditLog::register('driver_login_nfc', 'drivers', $driver->id);

        return response()->json([
            'token'  => $token,
            'driver' => [
                'id'          => $driver->id,
                'name'        => $driver->name,
                'employee_id' => $driver->employee_id,
                'vehicle'     => $driver->vehicles()->where('status', 'on_route')->first(),
            ]
        ]);
    }

    /**
     * LOGOUT del conductor
     * POST /api/dimas/logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        AuditLog::register('driver_logout', 'drivers', $request->user()->id);

        return response()->json([
            'message' => 'Sesión cerrada'
        ]);
    }

    /**
     * Perfil del conductor autenticado
     * GET /api/dimas/perfil
     */
    public function perfil(Request $request)
    {
        $driver = $request->user()->load('vehicles');

        return response()->json([
            'id'                    => $driver->id,
            'name'                  => $driver->name,
            'employee_id'           => $driver->employee_id,
            'phone'                 => $driver->phone,
            'license_number'        => $driver->license_number,
            'emergency_contact'     => $driver->emergency_contact_name,
            'emergency_phone'       => $driver->emergency_contact_phone,
            'status'                => $driver->status,
            'vehicle'               => $driver->vehicles()->where('status', 'on_route')->first(),
        ]);
    }
}