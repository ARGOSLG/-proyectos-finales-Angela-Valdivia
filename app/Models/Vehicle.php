<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Vehicle extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'company_id',
        'driver_id',
        'unit_number',
        'plate',
        'brand',
        'model',
        'status',
        'lat',
        'lng',
        'location_at',
    ];

    protected $casts = [
        'lat'         => 'float',
        'lng'         => 'float',
        'location_at' => 'datetime',
    ];

    // ─── Relaciones ───────────────────────────────────────────

    // Un vehículo pertenece a una empresa
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // Un vehículo tiene un conductor asignado
    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    // Un vehículo tiene muchas ubicaciones GPS guardadas
    public function locations()
    {
        return $this->hasMany(VehicleLocation::class);
    }

    // Un vehículo tiene muchos device tokens (Arduino + GPS)
    public function deviceTokens()
    {
        return $this->hasMany(DeviceToken::class);
    }

    // Un vehículo tiene muchas alertas generadas
    public function alerts()
    {
        return $this->hasMany(Alert::class);
    }

    // Un vehículo tiene muchos datos de telemetría
    public function telemetry()
    {
        return $this->hasMany(IotTelemetry::class);
    }
}