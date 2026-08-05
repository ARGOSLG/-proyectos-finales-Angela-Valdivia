<?php

namespace App\Events;

use App\Models\Reporte;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReporteActualizado implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Reporte $reporte
    ) {}

    /**
     * Canal privado por conductor
     * La app escucha este canal y recibe actualizaciones en tiempo real
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('driver.' . $this->reporte->driver_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'reporte.actualizado';
    }

    public function broadcastWith(): array
    {
        return [
            'id'          => $this->reporte->id,
            'tipo'        => $this->reporte->tipo,
            'estado'      => $this->reporte->estado,
            'alert_id'    => $this->reporte->alert_id,
            'description' => $this->reporte->description,
            'updated_at'  => $this->reporte->updated_at,
        ];
    }
}