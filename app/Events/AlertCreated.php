<?php

namespace App\Events;

use App\Models\Alert;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AlertCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Alert $alert
    ) {}

    /**
     * Canal donde se transmite el evento
     * El panel admin escucha este canal por empresa
     */
    public function broadcastOn(): array
    {
        return [
            // Canal privado por empresa — solo operadores de esa empresa lo ven
            new Channel('company.' . $this->alert->company_id),
        ];
    }

    /**
     * Nombre del evento que escucha el frontend
     */
    public function broadcastAs(): string
    {
        return 'alert.created';
    }

    /**
     * Datos que se envían al panel admin
     */
    public function broadcastWith(): array
    {
        return [
            'id'         => $this->alert->id,
            'type'       => $this->alert->type,
            'severity'   => $this->alert->severity,
            'source'     => $this->alert->source,
            'status'     => $this->alert->status,
            'metadata'   => $this->alert->metadata,
            'vehicle'    => [
                'id'          => $this->alert->vehicle?->id,
                'unit_number' => $this->alert->vehicle?->unit_number,
                'plate'       => $this->alert->vehicle?->plate,
            ],
            'driver'     => [
                'id'   => $this->alert->driver?->id,
                'name' => $this->alert->driver?->name,
            ],
            'created_at' => $this->alert->created_at,
        ];
    }
}
