<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VisionAIService
{
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.google.vision_key');
    }

    /**
     * Analizar los bytes de la imagen de la cámara del vehículo
     * Recibe el archivo directamente desde la memoria temporal del Request
     */
    public function analyzeDriverImage(string $fileData, Vehicle $vehicle): ?Alert
    {
        try {
            // Convertir binario directo a base64
            $imageData = base64_encode($fileData);

            // Llamar a Google Vision API
            $response = Http::post(
                "https://vision.googleapis.com/v1/images:annotate?key={$this->apiKey}",
                [
                    'requests' => [
                        [
                            'image' => [
                                'content' => $imageData
                            ],
                            'features' => [
                                ['type' => 'OBJECT_LOCALIZATION', 'maxResults' => 10],
                                ['type' => 'FACE_DETECTION',      'maxResults' => 5],
                                ['type' => 'LABEL_DETECTION',     'maxResults' => 15], 
                            ]
                        ]
                    ]
                ]
            );

            if (!$response->successful()) {
                Log::error('Google Vision API error: ' . $response->body());
                return null;
            }

            $result = $response->json();
            
            // TEMPORAL PARA POSTMAN: Forzamos el volcado para ver qué detecta Google
            dd($result);

            Log::info('Google Vision response: ' . json_encode($result));
            return $this->processVisionResult($result, $vehicle);
        } catch (\Exception $e) {
            Log::error('VisionAI error: ' . $e->getMessage());
            return null;
        }
    }

    private function processVisionResult(array $result, Vehicle $vehicle): ?Alert
    {
        $annotations = $result['responses'][0] ?? [];
        $labels      = collect($annotations['labelAnnotations'] ?? []);
        $objects     = collect($annotations['localizedObjectAnnotations'] ?? []);

        $confidenceScore = 0;

        // 1. DETECTAR USO DE TELÉFONO
        $detectedObject = $objects->first(fn($obj) =>
            in_array(strtolower($obj['name']), [
                'cell phone', 'mobile phone', 'telephone', 'smartphone', 
                'phone', 'iphone', 'android', 'mobile device', 'electronic device'
            ]) && $obj['score'] > 0.45 
        );

        $phoneDetected = !is_null($detectedObject);
        if ($phoneDetected) {
            $confidenceScore = $detectedObject['score'];
        }

        // Revisar labels si los objetos no lo capturaron
        if (!$phoneDetected) {
            $detectedLabel = $labels->first(fn($label) =>
                in_array(strtolower($label['description']), [
                    'mobile phone', 'cell phone', 'smartphone', 'telephone', 
                    'gadget', 'communication device', 'telephony', 'selfie'
                ]) && $label['score'] > 0.5
            );

            if ($detectedLabel) {
                $phoneDetected = true;
                $confidenceScore = $detectedLabel['score'];
            }
        }

        // 2. DETECTAR SOMNOLENCIA
        $faces     = $annotations['faceAnnotations'] ?? [];
        $drowsiness = false;
        
        foreach ($faces as $face) {
            if (($face['eyeOpenLikelihood'] ?? 'UNKNOWN') === 'VERY_UNLIKELY' || 
                ($face['eyeOpenLikelihood'] ?? 'UNKNOWN') === 'UNLIKELY') {
                $drowsiness = true;
                $confidenceScore = 0.85;
                break;
            }
        }

        // 3. DETECTAR DISTRACCIÓN GENERAL
        $distractionLabel = null;
        if (!$phoneDetected && !$drowsiness) {
            $distractionLabel = $labels->first(fn($label) =>
                in_array(strtolower($label['description']), [
                    'distracted', 'inattentive', 'looking away', 'boredom', 'sleep'
                ]) && $label['score'] > 0.65 
            );
        }

        // 4. CREAR ALERTAS
        if ($phoneDetected) {
            return $this->createCameraAlert($vehicle, 'phone_usage', 'critical', [
                'confidence' => $confidenceScore,
                'source'     => 'google_vision',
            ]);
        }

        if ($drowsiness) {
            return $this->createCameraAlert($vehicle, 'drowsiness', 'critical', [
                'confidence' => $confidenceScore,
                'source'     => 'google_vision',
            ]);
        }

        if ($distractionLabel) {
            return $this->createCameraAlert($vehicle, 'distraction', 'warning', [
                'confidence' => $distractionLabel['score'],
                'source'     => 'google_vision',
            ]);
        }

        return null;
    }

    private function createCameraAlert(Vehicle $vehicle, string $type, string $severity, array $metadata): Alert
    {
        return Alert::create([
            'company_id' => $vehicle->company_id,
            'driver_id'  => $vehicle->driver_id,
            'vehicle_id' => $vehicle->id,
            'type'       => $type,
            'severity'   => $severity,
            'source'     => 'camera',
            'metadata'   => $metadata,
            'status'     => 'active',
        ]);
    }
}