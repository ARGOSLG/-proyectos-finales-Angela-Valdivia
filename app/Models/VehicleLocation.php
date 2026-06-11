<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class VehicleLocation extends Model
{
    use HasUuids;

    // Esta tabla no tiene updated_at — los registros GPS nunca se modifican
    public $timestamps = false;

    protected $fillable = [
        'vehicle_id',
        'lat',
        'lng',
        'speed_kmh',
        'heading',
        'accuracy_m',
        'carrier',
        'signal_dbm',
        'recorded_at',
    ];

    protected $casts = [
        'lat'         => 'float',
        'lng'         => 'float',
        'speed_kmh'   => 'float',
        'heading'     => 'float',
        'accuracy_m'  => 'float',
        'signal_dbm'  => 'integer',
        'recorded_at' => 'datetime',
    ];

    // ─── Relaciones ───────────────────────────────────────────

    // Una ubicación pertenece a un vehículo
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    // ─── Scopes — filtros reutilizables ───────────────────────

    // Obtener las últimas N ubicaciones de un vehículo
    public function scopeRecent($query, int $limit = 100)
    {
        return $query->orderByDesc('recorded_at')->limit($limit);
    }

    // Filtrar por rango de fechas
    public function scopeBetweenDates($query, $from, $to)
    {
        return $query->whereBetween('recorded_at', [$from, $to]);
    }
}