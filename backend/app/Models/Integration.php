<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class Integration extends Model
{
    use HasFactory, UsesTenantConnection;

    protected $fillable = [
        'tenant_id',
        'name',
        'type',
        'status',
        'configuration',
        'credentials',
        'last_sync_at',
        'error_message',
    ];

    protected $casts = [
        'configuration' => 'array',
        'credentials' => 'encrypted:array',
        'last_sync_at' => 'datetime',
    ];
}

