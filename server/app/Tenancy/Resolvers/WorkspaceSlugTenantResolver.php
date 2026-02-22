<?php

declare(strict_types=1);

namespace App\Tenancy\Resolvers;

use Stancl\Tenancy\Contracts\Tenant;
use Stancl\Tenancy\Database\Models\Domain;
use Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedByRequestDataException;
use Stancl\Tenancy\Resolvers\RequestDataTenantResolver;

class WorkspaceSlugTenantResolver extends RequestDataTenantResolver
{
    /**
     * Resolve the tenant by the workspace slug sent in the X-Tenant header.
     * The slug matches the `domain` column in the `domains` table.
     */
    public function resolveWithoutCache(...$args): Tenant
    {
        $slug = $args[0];

        if ($slug) {
            $domain = Domain::where('domain', $slug)->first();

            if ($domain) {
                return $domain->tenant;
            }
        }

        throw new TenantCouldNotBeIdentifiedByRequestDataException($slug);
    }
}
