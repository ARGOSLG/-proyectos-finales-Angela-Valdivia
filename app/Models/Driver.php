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

    // ─── Relaciones ───────────────────────────────────────────

    // Un conductor pertenece a una empresa
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // Un conductor puede tener muchos vehículos asignados
    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }

    // Un conductor tiene muchas alertas generadas
    public function alerts()
    {
        return $this->hasMany(Alert::class);
    }

    // Un conductor tiene muchos incidentes
    public function incidents()
    {
        return $this->hasMany(Incident::class);
    }

    // Un conductor tiene muchas notificaciones recibidas
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}