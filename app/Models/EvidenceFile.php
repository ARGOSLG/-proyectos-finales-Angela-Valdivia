<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class EvidenceFile extends Model
{
    use HasUuids;

  protected $fillable = [
    'incident_id',
    'alert_id',
    'file_path',
    'file_type',
    'duration_seconds',
    'camera_label',
    'sync_status',
    'recorded_at',
    'uploaded_by',
    'original_name',
    'mime_type',
    'size_bytes',
];

    protected $casts = [
        'recorded_at' => 'datetime',
    ];

    public function incident()
    {
        return $this->belongsTo(Incident::class);
    }

    public function alert()
    {
        return $this->belongsTo(Alert::class);
    }
    public function uploadedBy()
{
    return $this->belongsTo(User::class, 'uploaded_by');
}
}