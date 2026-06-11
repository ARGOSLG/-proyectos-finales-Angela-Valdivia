<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Driver extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'company_id',
        'name',
        'employee_id',
        'phone',
        'license_number',
        'emergency_contact_name',
        'emergency_contact_phone',
        'status',
    ];

    // Un conductor pertenece a una empresa
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // Un conductor puede tener un vehículo asignado
    public function vehicle()
    {
        return $this->hasOne(Vehicle::class);
    }
}