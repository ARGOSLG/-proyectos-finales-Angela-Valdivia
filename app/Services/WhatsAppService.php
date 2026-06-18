<?php

namespace App\Services;

use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected Client $client;
    protected string $from;

    public function __construct()
    {
        $this->client = new Client(
            config('services.twilio.sid'),
            config('services.twilio.token')
        );
        $this->from = config('services.twilio.whatsapp_from');
    }

    /**
     * Enviar mensaje de WhatsApp
     */
    public function send(string $to, string $message): bool
    {
        try {
            $this->client->messages->create(
                "whatsapp:{$to}",
                [
                    'from' => $this->from,
                    'body' => $message,
                ]
            );
            return true;
        } catch (\Exception $e) {
            Log::error('WhatsApp error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Enviar alerta crítica
     */
    public function sendCriticalAlert(
        string $to,
        string $unitNumber,
        string $driverName,
        string $alertType,
        string $keyword = null
    ): bool {
        $message = "🚨 *ALERTA CRÍTICA ARGOS*\n\n" .
                   "Unidad: *{$unitNumber}*\n" .
                   "Conductor: *{$driverName}*\n" .
                   "Tipo: *{$alertType}*\n" .
                   ($keyword ? "Palabra detectada: *{$keyword}*\n" : "") .
                   "Hora: *" . now()->format('d/m/Y H:i:s') . "*\n\n" .
                   "⚡ Requiere atención inmediata.";

        return $this->send($to, $message);
    }

    /**
     * Enviar notificación de emergencia al contacto del conductor
     */
    public function sendEmergencyContact(
        string $to,
        string $driverName,
        string $unitNumber
    ): bool {
        $message = "🚨 *EMERGENCIA ARGOS*\n\n" .
                   "El conductor *{$driverName}* ha activado una alerta de emergencia.\n" .
                   "Unidad: *{$unitNumber}*\n" .
                   "Hora: *" . now()->format('d/m/Y H:i:s') . "*\n\n" .
                   "Por favor comuníquese de inmediato.";

        return $this->send($to, $message);
    }
}