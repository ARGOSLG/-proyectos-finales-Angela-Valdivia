<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SafePoint extends Model
{
    use HasUuids;

    protected $fillable = [
        'company_id',
        'name',
        'address',
        'lat',
        'lng',
        'type',
        'active',
    ];

    protected $casts = [
        'lat'    => 'float',
        'lng'    => 'float',
        'active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}