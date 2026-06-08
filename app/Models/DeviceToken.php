<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class DeviceToken extends Model
{
    use HasUuids;

    protected $fillable = [
        'vehicle_id',
        'device_type',
        'token',
        'active',
        'last_seen_at',
    ];

    protected $casts = [
        'active'       => 'boolean',
        'last_seen_at' => 'datetime',
    ];

    // ─── Relaciones ───────────────────────────────────────────

    // Un device token pertenece a un vehículo
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    // ─── Métodos útiles ───────────────────────────────────────

    // Verificar si el token es válido y está activo
    public static function findByToken(string $token): ?self
    {
        return self::where('token', $token)
                   ->where('active', true)
                   ->first();
    }

    // Actualizar la última vez que se conectó el dispositivo
    public function markAsSeen(): void
    {
        $this->update(['last_seen_at' => now()]);
    }
}