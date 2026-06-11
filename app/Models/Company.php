<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Company extends Model
{
    use HasUuids;
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'rfc',
        'contact_email',
        'phone',
        'plan',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    // Una empresa tiene muchos conductores
    public function drivers()
    {
        return $this->hasMany(Driver::class);
    }

    // Una empresa tiene muchos vehículos
    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }
}