<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Alert extends Model
{
    use HasUuids;

    protected $fillable = [
        'company_id',
        'driver_id',
        'vehicle_id',
        'type',
        'severity',
        'source',
        'metadata',
        'status',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'metadata'    => 'array',  // JSON se convierte automático a array
        'resolved_at' => 'datetime',
    ];

    // ─── Relaciones ───────────────────────────────────────────

    // Una alerta pertenece a una empresa
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // Una alerta pertenece a un conductor
    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    // Una alerta pertenece a un vehículo
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    // Una alerta fue resuelta por un usuario operador
    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    // Una alerta puede escalar a un incidente
    public function incident()
    {
        return $this->hasOne(Incident::class);
    }

    // Una alerta dispara muchas notificaciones
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    // ─── Scopes — filtros reutilizables ───────────────────────

    // Solo alertas activas
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Solo alertas críticas
    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }

    // Alertas de una empresa específica
    public function scopeForCompany($query, string $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}