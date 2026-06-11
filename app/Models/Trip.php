<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Trip extends Model
{
    use HasUuids;

    protected $fillable = [
        'vehicle_id',
        'driver_id',
        'company_id',
        'started_at',
        'ended_at',
        'distance_km',
        'fuel_used',
        'drive_time_minutes',
        'idle_time_minutes',
        'idle_percentage',
        'max_speed_kmh',
        'status',
    ];

    protected $casts = [
        'started_at'    => 'datetime',
        'ended_at'      => 'datetime',
        'distance_km'   => 'float',
        'fuel_used'     => 'float',
        'idle_percentage' => 'float',
        'max_speed_kmh' => 'float',
    ];

    // ─── Relaciones ───────────────────────────────────────────

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // ─── Scopes ───────────────────────────────────────────────

    // Viajes activos en curso
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Viajes completados
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Viajes de un rango de fechas
    public function scopeBetweenDates($query, $from, $to)
    {
        return $query->whereBetween('started_at', [$from, $to]);
    }
}