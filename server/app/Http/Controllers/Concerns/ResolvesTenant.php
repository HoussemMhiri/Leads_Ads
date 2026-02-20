<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Tenant;
use Illuminate\Http\Request;

trait ResolvesTenant
{
    /**
     * Get the tenant for the authenticated user.
     * Returns null if the user has no associated tenant.
     */
    protected function getTenant(Request $request): ?Tenant
    {
        return $request->user()?->tenant;
    }
}
