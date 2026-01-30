<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    /**
     * Password broker status messages mapping
     */
    private const STATUS_MESSAGES = [
        Password::RESET_LINK_SENT => 'We have emailed your password reset link!',
        Password::PASSWORD_RESET => 'Your password has been reset successfully!',
        Password::INVALID_USER => 'We can\'t find a user with that email address.',
        Password::INVALID_TOKEN => 'This password reset token is invalid or has expired.',
        Password::RESET_THROTTLED => 'Please wait before retrying.',
    ];

    /**
     * Send password reset link to user's email
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => self::STATUS_MESSAGES[$status],
            ], 200);
        }

        $this->throwValidationError($status);
    }

    /**
     * Reset the user's password
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8', 'confirmed'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'message' => self::STATUS_MESSAGES[$status],
            ], 200);
        }

        $this->throwValidationError($status);
    }

    /**
     * Throw validation exception with appropriate message
     *
     * @param string $status
     * @throws ValidationException
     */
    private function throwValidationError(string $status): void
    {
        throw ValidationException::withMessages([
            'email' => [self::STATUS_MESSAGES[$status] ?? 'An error occurred. Please try again.'],
        ]);
    }
}
