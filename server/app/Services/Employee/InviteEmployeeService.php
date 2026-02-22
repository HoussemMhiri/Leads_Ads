<?php

namespace App\Services\Employee;

use App\Mail\EmployeeInvitationMail;
use App\Models\Employee;
use App\Models\Tenant;
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
    public function execute(Tenant $tenant, array $emails, string $role = 'member'): array
    {
        $results = ['invited' => [], 'already_exists' => []];

        tenancy()->initialize($tenant);

        try {
            foreach ($emails as $email) {
                if (Employee::where('email', $email)->exists()) {
                    $results['already_exists'][] = $email;
                    continue;
                }

                $employee = Employee::create([
                    'name' => Str::before($email, '@'),
                    'email' => $email,
                    'password' => null,
                    'role' => $role,
                    'invited_by' => null,
                    'invited_at' => now(),
                ]);

                $employee->assignRole($role);

                $acceptUrl = $this->generateInvitationUrl($tenant, $employee);

                $workspaceSlug = $tenant->domains->first()?->domain ?? $tenant->id;
                $loginUrl = config('app.frontend_url') . '/employee/sign-in?workspace=' . $workspaceSlug;

                Mail::to($email)->queue(new EmployeeInvitationMail(
                    acceptUrl: $acceptUrl,
                    tenantName: $workspaceSlug,
                    loginUrl: $loginUrl,
                ));

                $results['invited'][] = $email;
            }
        } finally {
            tenancy()->end();
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
