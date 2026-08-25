<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleSensor extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'vehicle_id',
        'seat_occupied',
        'fifth_wheel_locked',
        'landing_gear_attached',
        'recorded_at',
    ];

    protected $casts = [
        'seat_occupied' => 'boolean',
        'fifth_wheel_locked' => 'boolean',
        'landing_gear_attached' => 'boolean',
        'recorded_at' => 'datetime',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}