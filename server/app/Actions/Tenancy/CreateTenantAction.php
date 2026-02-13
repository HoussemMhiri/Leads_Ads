<?php

namespace App\Actions\Tenancy;

use App\Models\Tenant;
use Illuminate\Support\Str;

class CreateTenantAction
{
    /**
     * Create a tenant with a subdomain and a DB name.
     * If the DB name already exists, append a random suffix.
     */
    public function execute(string $subdomain, string $userName): Tenant
    {
        // Base DB name from user name
        $baseDbName = 'tenant_'.Str::slug($userName);

        // Check if a tenant with this DB name exists
        if (Tenant::where('tenancy_db_name', $baseDbName)->exists()) {
            // Add a short random string to make it unique
            $baseDbName .= '_'.Str::lower(Str::random(4));
        }

        // Create the tenant
        $tenant = Tenant::create([
            'tenancy_db_name' => $baseDbName,
        ]);

        // Create the subdomain
        $tenant->domains()->create([
            'domain' => $subdomain,
        ]);

        return $tenant;
    }
}
