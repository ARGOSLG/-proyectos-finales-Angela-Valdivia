<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Incident extends Model
{
    use HasUuids;

    protected $fillable = [
        'alert_id',
        'driver_id',
        'vehicle_id',
        'operator_id',
        'type',
        'keyword_detected',
        'lat',
        'lng',
        'status',
        'started_at',
        'closed_at',
    ];

    protected $casts = [
        'lat'        => 'float',
        'lng'        => 'float',
        'started_at' => 'datetime',
        'closed_at'  => 'datetime',
    ];

    // ─── Relaciones ───────────────────────────────────────────

    // Un incidente se originó de una alerta
    public function alert()
    {
        return $this->belongsTo(Alert::class);
    }

    // Un incidente involucra a un conductor
    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    // Un incidente involucra a un vehículo
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    // Un incidente es atendido por un operador
    public function operator()
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    // Un incidente tiene muchas evidencias
    // Un incidente tiene muchas evidencias
    public function evidences()
    {
        return $this->hasMany(IncidentEvidence::class);
    }

    // ─── Scopes ───────────────────────────────────────────────

    // Solo incidentes abiertos
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    // Solo incidentes en progreso
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }
}