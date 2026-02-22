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
    public function execute(string $subdomain, string $companyName, int $userId): Tenant
    {
        // Base DB name from company name
        $baseDbName = 'tenant_'.Str::slug($companyName);

        // Check if a tenant with this DB name exists
        if (Tenant::where('tenancy_db_name', $baseDbName)->exists()) {
            // Add a short random string to make it unique
            $baseDbName .= '-'.Str::lower(Str::random(4));
        }

        // Create the tenant with its owner and company name
        $tenant = Tenant::create([
            'tenancy_db_name' => $baseDbName,
            'user_id'         => $userId,
            'company_name'    => $companyName,
        ]);

        // Create the subdomain
        $tenant->domains()->create([
            'domain' => $subdomain,
        ]);

        return $tenant;
    }
}
