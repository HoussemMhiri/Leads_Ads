<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Central DB mapping: employee email → tenant.
 * This model always uses the central DB connection, even when tenancy is active,
 * because we explicitly set $connection to the central connection name.
 */
class EmployeeWorkspace extends Model
{
    protected $connection = 'pgsql';

    protected $fillable = ['email', 'tenant_id', 'tenant_name', 'invited_by', 'invited_by_type', 'status', 'invited_at', 'expires_at'];

    protected $casts = [
        'invited_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
