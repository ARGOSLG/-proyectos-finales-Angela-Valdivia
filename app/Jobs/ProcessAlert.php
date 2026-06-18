<?php

namespace App\Jobs;

use App\Models\Alert;
use App\Models\AuditLog;
use App\Models\AlertNotification;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessAlert implements ShouldQueue
{
    use Queueable;

    public int $tries   = 3;
    public int $timeout = 60;

    public function __construct(
        public Alert $alert
    ) {}

    public function handle(WhatsAppService $whatsapp): void
    {
        $vehicle = $this->alert->vehicle;
        $driver  = $this->alert->driver;

        $operators = User::where('company_id', $this->alert->company_id)
                         ->where('active', true)
                         ->whereIn('role', ['admin', 'operator'])
                         ->get();

        foreach ($operators as $operator) {
            $message = $this->buildOperatorMessage($vehicle, $driver);

            $notification = AlertNotification::create([
                'alert_id'  => $this->alert->id,
                'channel'   => 'whatsapp',
                'recipient' => $operator->phone ?? '',
                'message'   => $message,
                'status'    => 'pending',
            ]);

            if ($operator->phone) {
                $sent = $whatsapp->sendCriticalAlert(
                    $operator->phone,
                    $vehicle->unit_number ?? 'N/A',
                    $driver->name ?? 'N/A',
                    $this->alert->type,
                    $this->alert->metadata['keyword'] ?? null
                );

                $notification->update([
                    'status'  => $sent ? 'sent' : 'failed',
                    'sent_at' => $sent ? now() : null,
                ]);
            }
        }

        if ($this->alert->severity === 'critical' && $driver) {
            if ($driver->emergency_contact_phone) {
                $message = $this->buildEmergencyMessage($driver, $vehicle);

                $notification = AlertNotification::create([
                    'alert_id'  => $this->alert->id,
                    'driver_id' => $driver->id,
                    'channel'   => 'whatsapp',
                    'recipient' => $driver->emergency_contact_phone,
                    'message'   => $message,
                    'status'    => 'pending',
                ]);

                $sent = $whatsapp->sendEmergencyContact(
                    $driver->emergency_contact_phone,
                    $driver->name,
                    $vehicle->unit_number ?? 'N/A'
                );

                $notification->update([
                    'status'  => $sent ? 'sent' : 'failed',
                    'sent_at' => $sent ? now() : null,
                ]);
            }
        }

        AuditLog::register(
            'alert_processed',
            'alerts',
            $this->alert->id,
            ['severity' => $this->alert->severity]
        );
    }

    private function buildOperatorMessage($vehicle, $driver): string
    {
        return "🚨 ALERTA ARGOS\n\n" .
               "Tipo: " . strtoupper($this->alert->type) . "\n" .
               "Severidad: " . strtoupper($this->alert->severity) . "\n" .
               "Unidad: " . ($vehicle->unit_number ?? 'N/A') . "\n" .
               "Conductor: " . ($driver->name ?? 'N/A') . "\n" .
               "Hora: " . now()->format('d/m/Y H:i:s');
    }

    private function buildEmergencyMessage($driver, $vehicle): string
    {
        return "🚨 EMERGENCIA ARGOS\n\n" .
               "El conductor " . $driver->name . " activó una alerta de emergencia.\n" .
               "Unidad: " . ($vehicle->unit_number ?? 'N/A') . "\n" .
               "Hora: " . now()->format('d/m/Y H:i:s') . "\n\n" .
               "Por favor comuníquese de inmediato.";
    }
}