<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'tenancy_db_name',
            'user_id',
            'created_at',
            'updated_at',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────────────

    /**
     * The user (owner) who owns this tenant.
     * FK: tenants.user_id → users.id
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * All employees belonging to this tenant.
     * Requires tenancy to be initialized before calling.
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
