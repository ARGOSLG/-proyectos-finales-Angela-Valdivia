<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; 

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ─── Relaciones ─────────────────────────────────────────

    // Un usuario (operador) pertenece a una empresa
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // Un usuario puede haber resuelto muchas alertas
    public function resolvedAlerts()
    {
        return $this->hasMany(Alert::class, 'resolved_by');
    }

    // Un usuario puede haber atendido muchos incidentes
    public function incidents()
    {
        return $this->hasMany(Incident::class, 'operator_id');
    }

    // Un usuario tiene muchos registros en la bitácora
    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }
}