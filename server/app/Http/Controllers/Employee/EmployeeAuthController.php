<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\EmployeeLoginRequest;
use App\Models\Employee;
use App\Models\EmployeeWorkspace;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class EmployeeAuthController extends Controller
{
    /**
     * Authenticate an employee within their tenant workspace.
     *
     * Tenant is resolved by looking up the employee email in the central
     * employee_workspaces table — no workspace field needed from the frontend.
     * After login, tenant_id is stored in session so all subsequent requests
     * use InitializeTenancyBySession middleware automatically.
     */
    public function login(EmployeeLoginRequest $request): JsonResponse
    {
        $email = $request->validated('email');

        $mapping = EmployeeWorkspace::where('email', $email)->first();

        if (! $mapping) {
            throw ValidationException::withMessages([
                'email' => ['No account found for this email.'],
            ]);
        }

        $tenant = Tenant::find($mapping->tenant_id);

        if (! $tenant) {
            throw ValidationException::withMessages([
                'email' => ['Workspace not found.'],
            ]);
        }

        tenancy()->initialize($tenant);

        try {
            if (! Auth::guard('employee')->attempt($request->only('email', 'password'))) {
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

            $request->session()->put('tenant_id', $tenant->id);
            $request->session()->regenerate();

            return response()->json([
                'message' => 'Login successful',
                'employee' => $this->employeeResource($employee),
                'tenant' => $this->tenantResource($tenant),
            ]);
        } finally {
            tenancy()->end();
        }
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
            'tenant' => $this->tenantResource(tenancy()->tenant),
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function employeeResource(Employee $employee): array
    {
        return [
            'id' => $employee->id,
            'name' => $employee->name,
            'email' => $employee->email,
            'role' => $employee->role,
        ];
    }

    private function tenantResource(Tenant $tenant): array
    {
        return [
            'id' => $tenant->id,
            'workspace' => $tenant->domains->first()?->domain,
            'name' => $tenant->company_name,
            'logo_url' => $tenant->logo_path
                ? Storage::disk('central_public')->url($tenant->logo_path)
                : null,
        ];
    }
}
