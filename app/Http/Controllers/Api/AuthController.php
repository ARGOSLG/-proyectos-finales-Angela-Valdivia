<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * LOGIN — el operador manda email y password
     * Devuelve un token si las credenciales son correctas
     */
    public function login(Request $request)
    {
        // 1. Validar que lleguen los datos correctos
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        // 2. Buscar el usuario por email
        $user = User::where('email', $request->email)->first();

        // 3. Verificar que existe y que la contraseña es correcta
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Credenciales incorrectas'
            ], 401);
        }

        // 4. Verificar que el usuario está activo
        if (!$user->active) {
            return response()->json([
                'message' => 'Usuario inactivo — contacta al administrador'
            ], 403);
        }

        // 5. Generar el token de acceso
        $token = $user->createToken('argos-panel')->plainTextToken;

        // 6. Registrar en bitácora
        AuditLog::register('login', 'users', $user->id);

        // 7. Devolver el token y datos del usuario
        return response()->json([
            'token' => $token,
            'user'  => [
                'id'         => $user->id,
                'name'       => $user->name,
                'email'      => $user->email,
                'role'       => $user->role,
                'company_id' => $user->company_id,
            ]
        ]);
    }

    /**
     * LOGOUT — invalida el token actual
     */
    public function logout(Request $request)
    {
        // Eliminar el token con el que se autenticó
        $request->user()->currentAccessToken()->delete();

        // Registrar en bitácora
        AuditLog::register('logout', 'users', $request->user()->id);

        return response()->json([
            'message' => 'Sesión cerrada correctamente'
        ]);
    }

    /**
     * ME — devuelve los datos del usuario autenticado
     * El panel usa esto para saber quién está logueado
     */
    public function me(Request $request)
    {
        $user = $request->user()->load('company');

        return response()->json([
            'id'         => $user->id,
            'name'       => $user->name,
            'email'      => $user->email,
            'role'       => $user->role,
            'company'    => $user->company,
        ]);
    }
}