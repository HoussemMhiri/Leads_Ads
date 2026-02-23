<?php

namespace App\Services\Employee;

use App\Mail\EmployeeInvitationMail;
use App\Models\Employee;
use App\Models\EmployeeWorkspace;
use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class InviteEmployeeService
{
    /**
     * Invite multiple employees to a tenant.
     *
     * @return array{invited: string[], already_exists: string[]}
     */
    /**
     * Invite employees to the tenant.
     * Tenancy must already be initialized before calling this method
     * (handled by InitializeTenancyBySession middleware on the invite route).
     *
     * Each invited employee is written to both the tenant DB (Employee record)
     * and the central DB (EmployeeWorkspace mapping) so login can resolve
     * the tenant from the email without a workspace field.
     */
    public function execute(Tenant $tenant, array $emails, string $role = 'member', ?string $invitedBy = null): array
    {
        $results = ['invited' => [], 'already_exists' => []];

        $invitedByUser     = Auth::user();
        $invitedByEmployee = Auth::guard('employee')->user();
        $invitedBy       ??= $invitedByUser?->email ?? $invitedByEmployee?->email;
        $invitedByType     = $invitedByUser ? 'owner' : ($invitedByEmployee ? 'employee' : null);
        $tenantName = $tenant->company_name ?? $tenant->domains->first()?->domain ?? $tenant->id;

        foreach ($emails as $email) {
            $existingMapping = EmployeeWorkspace::where('email', $email)->first();

            // Block if already active in central mapping
            if ($existingMapping && $existingMapping->status === 'active') {
                $results['already_exists'][] = $email;
                continue;
            }

            // Block if pending and not yet expired
            if ($existingMapping && $existingMapping->status === 'pending' && ! $existingMapping->isExpired()) {
                $results['already_exists'][] = $email;
                continue;
            }

            // At this point: no mapping, or mapping is expired → allow re-invite
            // Clean up stale tenant DB record if re-inviting after expiry
            if ($existingMapping) {
                Employee::where('email', $email)->delete();
                $existingMapping->delete();
            }

            $expiresAt = now()->addHours(72);

            $employee = Employee::create([
                'name'       => Str::before($email, '@'),
                'email'      => $email,
                'password'   => null,
                'role'       => $role,
                'invited_by' => $invitedByEmployee?->id ?? null,
                'invited_at' => now(),
            ]);

            $employee->assignRole($role);

            try {
                EmployeeWorkspace::create([
                    'email'           => $email,
                    'tenant_id'       => $tenant->id,
                    'tenant_name'     => $tenantName,
                    'invited_by'      => $invitedBy,
                    'invited_by_type' => $invitedByType,
                    'invited_at'      => now(),
                    'expires_at'      => $expiresAt,
                ]);
            } catch (\Exception $e) {
                $employee->delete();
                throw $e;
            }

            $acceptUrl = $this->generateInvitationUrl($tenant, $employee);
            $loginUrl  = config('app.frontend_url') . '/employee/sign-in';

            Mail::to($email)->queue(new EmployeeInvitationMail(
                acceptUrl:  $acceptUrl,
                tenantName: $tenant->domains->first()?->domain ?? $tenant->id,
                loginUrl:   $loginUrl,
            ));

            $results['invited'][] = $email;
        }

        return $results;
    }

    private function generateInvitationUrl(Tenant $tenant, Employee $employee): string
    {
        $signedUrl = URL::temporarySignedRoute(
            'employee.invitation.accept',
            now()->addHours(72),
            [
                'tenant' => $tenant->id,
                'employee' => $employee->id,
            ]
        );

        $parsed = parse_url($signedUrl);
        parse_str($parsed['query'] ?? '', $queryParams);

        return config('app.frontend_url')
            . '/invitation/accept'
            . '?tenant=' . $tenant->id
            . '&employee=' . $employee->id
            . '&expires=' . $queryParams['expires']
            . '&signature=' . $queryParams['signature'];
    }
}
