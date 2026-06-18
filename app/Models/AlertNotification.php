<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AlertNotification extends Model
{
    use HasUuids;

    protected $table = 'notifications';

    protected $fillable = [
        'alert_id',
        'driver_id',
        'channel',
        'recipient',
        'message',
        'status',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function alert()
    {
        return $this->belongsTo(Alert::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}