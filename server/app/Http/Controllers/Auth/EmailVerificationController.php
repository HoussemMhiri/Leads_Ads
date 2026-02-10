<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EmailVerificationController extends Controller
{
    public function verify(Request $request)
    {
        $user = User::findOrFail($request->route('id'));

        if (! $request->hasValidSignature()) {
            abort(403, 'Invalid or expired verification link.');
        }

        $frontendUrl = config('app.frontend_url', 'http://localhost:5173');

        if ($user->hasVerifiedEmail()) {
            return redirect()->away("{$frontendUrl}/auth/sign-in?verified=already");
        }

        $user->markEmailAsVerified();

        return redirect()->away("{$frontendUrl}/auth/sign-in?verified=success");
    }

    public function resend(Request $request)
    {
        $request->user()->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Verification link sent!',
        ]);
    }

    public function resendByEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => ['No account found with this email address.'],
            ]);
        }

        if ($user->hasVerifiedEmail()) {
            throw ValidationException::withMessages([
                'email' => ['This email is already verified.'],
            ]);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Verification email sent! Please check your inbox.',
        ]);
    }
}
