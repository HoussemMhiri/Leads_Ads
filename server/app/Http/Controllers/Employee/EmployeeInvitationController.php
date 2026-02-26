<?php

namespace App\Http\Controllers\Employee;

use App\Enums\EmployeeWorkspaceStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\AcceptInvitationRequest;
use App\Http\Requests\Employee\InviteEmployeesRequest;
use App\Http\Requests\Employee\UpdateEmployeeRoleRequest;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use App\Models\EmployeeWorkspace;
use App\Models\Tenant;
use App\Services\Employee\InviteEmployeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class EmployeeInvitationController extends Controller
{
    /**
     * Return all team members for the current tenant.
     * Tenancy is already initialized by InitializeTenancyBySession middleware.
     */
    public function members(): JsonResponse
    {
        $members = Employee::query()
            ->orderBy('name')
            ->get()
            ->map(fn (Employee $employee) => (new EmployeeResource($employee))->toArray(request()));

        return response()->json($members);
    }

    /**
     * Return all roles available for employees.
     * Tenancy is already initialized by InitializeTenancyBySession middleware.
     */
    public function roles(): JsonResponse
    {
        $roles = Role::where('guard_name', 'employee')->get(['id', 'name']);

        return response()->json($roles);
    }

    /**
     * Update the role of a specific employee.
     * Tenancy is already initialized by InitializeTenancyBySession middleware.
     */
    public function updateRole(UpdateEmployeeRoleRequest $request, Employee $employee): JsonResponse
    {
        $role = $request->validated('role');

        $employee->update(['role' => $role]);
        $employee->syncRoles([$role]);

        return response()->json(['message' => 'Role updated successfully.']);
    }

    /**
     * Remove an employee from the workspace.
     * Deletes from tenant DB and clears central mapping.
     */
    public function remove(Employee $employee): JsonResponse
    {
        $email = $employee->email;
        $employee->delete();
        EmployeeWorkspace::where('email', $email)->delete();

        return response()->json(['message' => 'Employee removed from workspace.']);
    }

    /**
     * Send invitations to multiple employees.
     * Tenancy is already initialized by InitializeTenancyBySession middleware.
     */
    public function invite(InviteEmployeesRequest $request, InviteEmployeeService $service): JsonResponse
    {
        $results = $service->execute(
            tenant: tenancy()->tenant,
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
                'workspace' => $tenant->domains->first()?->domain,
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

            EmployeeWorkspace::where('email', $employee->email)
                ->update(['status' => EmployeeWorkspaceStatus::Active, 'expires_at' => null]);

            return response()->json([
                'message' => 'Invitation accepted. You can now sign in.',
            ]);
        } finally {
            tenancy()->end();
        }
    }
}
