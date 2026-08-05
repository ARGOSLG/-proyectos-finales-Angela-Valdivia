<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Reporte extends Model
{
    use HasUuids;

    protected $fillable = [
        'driver_id',
        'alert_id',
        'tipo',
        'estado',
        'lat',
        'lng',
        'description',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'lat'      => 'float',
        'lng'      => 'float',
    ];

    // ─── Relaciones ───────────────────────────────────────────

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function alert()
    {
        return $this->belongsTo(Alert::class);
    }
}