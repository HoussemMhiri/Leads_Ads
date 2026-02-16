<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\AcceptInvitationRequest;
use App\Http\Requests\Employee\InviteEmployeesRequest;
use App\Models\Employee;
use App\Models\Tenant;
use App\Services\Employee\InviteEmployeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeInvitationController extends Controller
{
    /**
     * Send invitations to multiple employees.
     * Called by the tenant owner (authenticated User).
     */
    public function invite(InviteEmployeesRequest $request, InviteEmployeeService $service): JsonResponse
    {
        $user = $request->user();
        $tenant = $user->tenant;

        if (! $tenant) {
            return response()->json(['message' => 'No tenant associated with this account.'], 403);
        }

        $results = $service->execute(
            tenant: $tenant,
            emails: $request->validated('emails'),
            role: $request->validated('role') ?? 'member',
        );

        return response()->json([
            'message' => 'Invitations processed.',
            'invited' => $results['invited'],
            'already_exists' => $results['already_exists'],
        ]);
    }

    /**
     * Show invitation details for the frontend to display.
     * Validates the signed URL.
     */
    public function show(Request $request, string $tenantId, string $employeeId): JsonResponse
    {
        $tenant = Tenant::findOrFail($tenantId);
        tenancy()->initialize($tenant);

        try {
            $employee = Employee::findOrFail($employeeId);

            if (! $employee->isPending()) {
                return response()->json(['message' => 'This invitation has already been accepted.'], 410);
            }

            return response()->json([
                'email' => $employee->email,
                'tenant' => $tenantId,
            ]);
        } finally {
            tenancy()->end();
        }
    }

    /**
     * Accept an invitation — employee sets their name and password.
     */
    public function accept(AcceptInvitationRequest $request, string $tenantId, string $employeeId): JsonResponse
    {
        $tenant = Tenant::findOrFail($tenantId);
        tenancy()->initialize($tenant);

        try {
            $employee = Employee::findOrFail($employeeId);

            if (! $employee->isPending()) {
                return response()->json(['message' => 'This invitation has already been accepted.'], 410);
            }

            $employee->update([
                'name' => $request->validated('name'),
                'password' => $request->validated('password'),
            ]);

            $employee->markInvitationAccepted();

            return response()->json([
                'message' => 'Invitation accepted. You can now sign in.',
            ]);
        } finally {
            tenancy()->end();
        }
    }
}
