<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AuditLog extends Model // ◄── Corregido a singular para que coincida con tu Controlador
{
    use HasUuids;

    // Fuerza al modelo a usar la tabla en plural que renombramos en PostgreSQL
    protected $table = 'audit_logs';
    
    // Los logs nunca se modifican — solo created_at
    public $timestamps = false;
    
    protected $fillable = [
        'user_id',
        'action',
        'table_name',
        'record_id',
        'payload',
        'ip_address',
        'created_at',
    ];

    protected $casts = [
        'payload'    => 'array',
        'created_at' => 'datetime',
    ];

    // ─── Relaciones ───────────────────────────────────────────

    // Un log pertenece a un usuario — nullable si fue el sistema
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ─── Método estático para registrar fácilmente ────────────

    // Uso: AuditLog::register('resolved_alert', 'alerts', $alertId, $data)
    public static function register(
        string $action,
        string $tableName,
        string $recordId = null,
        array  $payload = [],
        string $ipAddress = null
    ): void {
        self::create([
            'user_id'    => auth()->id(),
            'action'     => $action,
            'table_name' => $tableName,
            'record_id'  => $recordId,
            'payload'    => $payload,
            'ip_address' => $ipAddress ?? request()->ip(),
            'created_at' => now(),
        ]);
    }

    // ─── Scopes ───────────────────────────────────────────────

    // Logs de una tabla específica
    public function scopeForTable($query, string $table)
    {
        return $query->where('table_name', $table);
    }

    // Logs de un usuario específico
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}