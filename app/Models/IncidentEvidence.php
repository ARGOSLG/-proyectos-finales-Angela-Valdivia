<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class IncidentEvidence extends Model
{
    use HasUuids;
    protected $table = 'incident_evidences';

    protected $fillable = [
        'incident_id',
        'uploaded_by',
        'type',
        'file_path',
        'original_name',
        'mime_type',
        'size_bytes',
        'duration_seconds',
    ];

    protected $casts = [
        'size_bytes'       => 'integer',
        'duration_seconds' => 'integer',
    ];

    // Una evidencia pertenece a un incidente
    public function incident()
    {
        return $this->belongsTo(Incident::class);
    }

    // Quién la subió
    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // URL pública para acceder al archivo
    public function getUrlAttribute()
    {
        return asset('storage/' . $this->file_path);
    }
}