<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Services\Tenancy\CreateTenantService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        private CreateTenantService $createTenantService,
    ) {}

    /**
     * Register a new user and create their tenant
     */
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        try {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);
        } catch (QueryException $e) {
            if ($e->errorInfo[1] == 1062 || $e->errorInfo[1] == 2067) {
                throw ValidationException::withMessages([
                    'email' => ['An account with this email or name already exists.'],
                ]);
            }

            throw $e;
        }

        $tenantResult = $this->createTenantService->execute($user, $data['company_name']);

        event(new Registered($user));

        return response()->json([
            'message' => 'Registration successful! Please check your email to verify your account.',
            'email' => $user->email,
            'subdomain' => $tenantResult['subdomain'],
        ], 201);
    }

    /**
     * Login user (create session)
     */
    public function login(LoginRequest $request)
    {
        $data = $request->validated();

        // Attempt to authenticate
        if (! Auth::attempt($data)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Get the authenticated user
        $user = Auth::user();

        // Check if email is verified
        if (! $user->hasVerifiedEmail()) {
            Auth::logout();

            return response()->json([
                'message' => 'Please verify your email address before logging in.',
                'email_not_verified' => true,
            ], 403);
        }

        // Store tenant_id in session so InitializeTenancyBySession works
        // when the owner accesses tenant-scoped routes (roles, invite, etc.)
        $tenant = $user->tenant;
        if ($tenant) {
            $request->session()->put('tenant_id', $tenant->id);
        }

        // Regenerate session to prevent fixation attacks
        $request->session()->regenerate();

        return response()->json([
            'message' => 'Login successful',
            'user' => $this->userResource(Auth::user()),
        ]);
    }

    /**
     * Logout user (destroy session)
     */
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Logout successful',
        ]);
    }

    /**
     * Get authenticated user
     */
    public function user(Request $request)
    {
        return response()->json([
            'user' => $this->userResource($request->user()),
        ]);
    }

    /**
     * Transform user object (minimal exposure)
     */
    private function userResource(User $user): array
    {
        $data = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->avatar,
        ];

        $tenant = $user->tenant;

        if ($tenant) {
            $data['tenant'] = [
                'id' => $tenant->id,
                'subdomain' => $tenant->domains->first()?->domain,
                'company_name' => $tenant->company_name,
                'logo_url' => $tenant->logo_path
                    ? Storage::disk('central_public')->url($tenant->logo_path)
                    : null,
            ];
        }

        return $data;
    }
}
