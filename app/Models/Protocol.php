<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Protocol extends Model
{
    use HasUuids;

    protected $fillable = [
        'company_id',
        'name',
        'trigger_type',
        'steps',
        'active',
    ];

    protected $casts = [
        'steps'  => 'array',
        'active' => 'boolean',
    ];

    // ─── Relaciones ───────────────────────────────────────────

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function executions()
    {
        return $this->hasMany(ProtocolExecution::class);
    }
}