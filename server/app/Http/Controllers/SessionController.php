<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SessionController extends Controller
{
    /**
     * Return whoever is currently authenticated — owner or employee.
     * Always returns 200 so the frontend never sees a 401 on app boot.
     */
    public function __invoke(Request $request): JsonResponse
    {
        // ── Owner check (central DB, no tenancy needed) ───────────────────────
        $user = Auth::guard('web')->user();

        if ($user) {
            $tenant = $user->tenant;

            return response()->json([
                'type' => 'owner',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar,
                    'tenant' => $tenant ? [
                        'id' => $tenant->id,
                        'subdomain' => $tenant->domains->first()?->domain,
                        'company_name' => $tenant->company_name,
                        'logo_url' => $tenant->logo_path
                            ? Storage::disk('central_public')->url($tenant->logo_path)
                            : null,
                    ] : null,
                ],
            ]);
        }

        // ── Employee check (needs tenant DB initialized from session) ─────────
        $tenantId = session('tenant_id');

        if ($tenantId) {
            $tenant = Tenant::find($tenantId);

            if ($tenant) {
                tenancy()->initialize($tenant);

                try {
                    $employee = Auth::guard('employee')->user();

                    if ($employee) {
                        return response()->json([
                            'type' => 'employee',
                            'employee' => [
                                'id' => $employee->id,
                                'name' => $employee->name,
                                'email' => $employee->email,
                                'role' => $employee->role,
                            ],
                            'tenant' => [
                                'id' => $tenant->id,
                                'workspace' => $tenant->domains->first()?->domain,
                                'name' => $tenant->company_name,
                                'logo_url' => $tenant->logo_path
                                    ? Storage::disk('central_public')->url($tenant->logo_path)
                                    : null,
                            ],
                        ]);
                    }
                } finally {
                    tenancy()->end();
                }
            }
        }

        // ── Nobody logged in ──────────────────────────────────────────────────
        return response()->json(['type' => null]);
    }
}
