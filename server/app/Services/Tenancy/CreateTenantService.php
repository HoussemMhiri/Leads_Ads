<?php

namespace App\Services\Tenancy;

use App\Actions\Tenancy\CreateTenantAction;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Stancl\Tenancy\Database\Models\Domain;

class CreateTenantService
{
    public function __construct(
        private CreateTenantAction $createTenantAction,
    ) {}

    /**
     * Provision a new tenant for a user.
     */
    public function execute(User $user): array
    {
        $subdomain = $this->generateUniqueSubdomain($user->name);

        Log::info('user  name', ['userName: ' => $user->name]);

        try {
            // Create tenant with owner set in one step
            $tenant = $this->createTenantAction->execute($subdomain, $user->name, $user->id);

            Log::info('Tenant created', [
                'tenant_id' => $tenant->id,
                'subdomain' => $subdomain,
                'user_id' => $user->id,
            ]);

            return [
                'tenant' => $tenant,
                'subdomain' => $subdomain,
            ];

        } catch (\Throwable $e) {
            Log::error('Tenant creation failed', [
                'user_id' => $user->id,
                'subdomain' => $subdomain,
                'error' => $e->getMessage(),
            ]);

            // Cleanup if action partially succeeded
            if (isset($tenant)) {
                $tenant->delete();
            }

            throw $e;
        }
    }

    /**
     * Generate a unique subdomain from a name string.
     */
    private function generateUniqueSubdomain(string $name): string
    {
        $base = Str::slug($name);
        if (Str::length($base) < 3) {
            $base .= '-app';
        }

        for ($i = 0; $i < 10; $i++) {
            $candidate = $i === 0 ? $base : $base.'-'.Str::lower(Str::random(4));
            if (! Domain::where('domain', $candidate)->exists()) {
                return $candidate;
            }
        }

        throw new \RuntimeException("Unable to generate unique subdomain for '{$name}'");
    }
}
