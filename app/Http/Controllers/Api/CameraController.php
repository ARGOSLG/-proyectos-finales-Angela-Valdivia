<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Events\AlertCreated;
use App\Jobs\ProcessAlert;
use App\Services\VisionAIService;
use Illuminate\Http\Request;

class CameraController extends Controller
{
    /**
     * Recibe imagen de la cámara del vehículo
     * POST /api/iot/camera
     */
    public function analyze(Request $request, VisionAIService $vision)
    {
        $request->validate([
            'image' => 'required|image|max:10240', // max 10MB
        ]);

        $device  = $request->device;
        $vehicle = $device->vehicle;
       
        //  OBTENER BINARIO DIRECTO DESDE LA MEMORIA TEMPORAL DEL REQUEST
        $fileData = file_get_contents($request->file('image')->getRealPath());

        // Enviamos los bytes directamente al servicio sin tocar el disco duro
        $alert = $vision->analyzeDriverImage($fileData, $vehicle);

        if ($alert) {
            // Disparar Job de WhatsApp
            ProcessAlert::dispatch($alert);

            // Emitir WebSocket al panel
            AlertCreated::dispatch($alert->load('vehicle', 'driver'));

            return response()->json([
                'message'  => 'Comportamiento peligroso detectado',
                'alert_id' => $alert->id,
                'type'     => $alert->type,
                'severity' => $alert->severity,
            ], 201);
        }

        return response()->json([
            'message' => 'Imagen analizada — conductor en condiciones normales',
        ]);
    }
}