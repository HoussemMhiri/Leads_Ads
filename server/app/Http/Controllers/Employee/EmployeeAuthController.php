<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\EmployeeLoginRequest;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class EmployeeAuthController extends Controller
{
    /**
     * Authenticate an employee within their tenant workspace.
     *
     * Tenancy is already initialized by InitializeTenancyByRequestData before
     * this controller runs — the DB is already pointing to the right tenant.
     * The X-Tenant header identifies the workspace; no subdomain needed.
     */
    public function login(EmployeeLoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        if (! Auth::guard('employee')->attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        /** @var Employee $employee */
        $employee = Auth::guard('employee')->user();

        if ($employee->isPending()) {
            Auth::guard('employee')->logout();

            return response()->json([
                'message' => 'Please accept your invitation before signing in.',
            ], 403);
        }

        $request->session()->regenerate();

        return response()->json([
            'message'  => 'Login successful',
            'employee' => $this->employeeResource($employee),
            'tenant'   => [
                'id'        => tenant('id'),
                'workspace' => tenancy()->tenant->domains->first()?->domain,
            ],
        ]);
    }

    /**
     * Invalidate the employee session.
     * Tenancy is already initialized by InitializeTenancyBySubdomain.
     */
    public function logout(Request $request): JsonResponse
    {
        Auth::guard('employee')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    /**
     * Return the currently authenticated employee.
     * Tenancy is already initialized by InitializeTenancyBySubdomain.
     */
    public function me(Request $request): JsonResponse
    {
        /** @var Employee $employee */
        $employee = Auth::guard('employee')->user();

        return response()->json([
            'employee' => $this->employeeResource($employee),
            'tenant'   => [
                'id'        => tenant('id'),
                'workspace' => tenancy()->tenant->domains->first()?->domain,
            ],
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function employeeResource(Employee $employee): array
    {
        return [
            'id'    => $employee->id,
            'name'  => $employee->name,
            'email' => $employee->email,
            'role'  => $employee->role,
        ];
    }

}
