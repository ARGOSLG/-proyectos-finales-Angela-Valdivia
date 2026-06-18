<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasUuids, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'company_id',
        'active',
        'phone',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'active'            => 'boolean',
        ];
    }

    // ─── Relaciones ───────────────────────────────────────────

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function resolvedAlerts()
    {
        return $this->hasMany(Alert::class, 'resolved_by');
    }

    public function incidents()
    {
        return $this->hasMany(Incident::class, 'operator_id');
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }
}