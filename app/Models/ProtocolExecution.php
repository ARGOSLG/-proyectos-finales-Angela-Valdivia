<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ProtocolExecution extends Model
{
    use HasUuids;

    protected $fillable = [
        'protocol_id',
        'incident_id',
        'operator_id',
        'steps_state',
        'status',
        'motor_cut_sent',
        'completed_at',
    ];

    protected $casts = [
        'steps_state'    => 'array',
        'motor_cut_sent' => 'boolean',
        'completed_at'   => 'datetime',
    ];

    // ─── Relaciones ───────────────────────────────────────────

    public function protocol()
    {
        return $this->belongsTo(Protocol::class);
    }

    public function incident()
    {
        return $this->belongsTo(Incident::class);
    }

    public function operator()
    {
        return $this->belongsTo(User::class, 'operator_id');
    }
}