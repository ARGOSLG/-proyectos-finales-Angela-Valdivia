<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Company extends Model
{
    use HasUuids;
    protected $keyType = 'string';
    public $incrementing = false;
    // Campos que se pueden llenar masivamente
    protected $fillable = [
        'name',
        'rfc',
        'contact_email',
        'phone',
        'plan',
        'active',
        
    ];

    // Tipos de datos automáticos
    protected $casts = [
        'active' => 'boolean',
    ];

    // ─── Relaciones ───────────────────────────────────────────

    // Una empresa tiene muchos usuarios (operadores y admins)
    public function users()
    {
        return $this->hasMany(User::class);
    }

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

    // Una empresa tiene muchas alertas
    public function alerts()
    {
        return $this->hasMany(Alert::class);
    }

    // Una empresa tiene muchos protocolos
    public function protocols()
    {
        return $this->hasMany(Protocol::class);
    }

    // Una empresa tiene muchos puntos seguros
    public function safePoints()
    {
        return $this->hasMany(SafePoint::class);
    }
}