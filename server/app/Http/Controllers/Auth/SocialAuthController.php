<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Redirect to Google OAuth
     */
    public function redirect()
    {
        return Socialite::driver('google')
            ->stateless()
            ->redirect();
    }

    /**
     * Handle Google OAuth callback.
     *
     * Auth::login() is not called here — the session cookie would be scoped
     * to the callback domain (e.g. ngrok), not the SPA origin.  A one-time
     * code is stored instead; the SPA exchanges it via a proxied request
     * which sets the session on the correct origin.
     */
    public function callback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')
                ->stateless()
                ->user();

            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'email_verified_at' => now(),
                    'password' => Hash::make(Str::random(24)),
                ]);
            } else {
                if (!$user->google_id) {
                    $user->update([
                        'google_id' => $googleUser->getId(),
                        'avatar' => $googleUser->getAvatar(),
                    ]);
                }
            }

            $code = Str::random(40);
            cache()->put('google_auth_' . $code, $user->id, now()->addMinutes(5));

            return redirect(env('FRONTEND_URL') . '/auth/google/callback?code=' . $code);

        } catch (\Exception $e) {
            Log::error('Google OAuth callback failed', ['exception' => $e]);
            return redirect(env('FRONTEND_URL') . '/auth/google/callback?error=authentication_failed');
        }
    }

    /**
     * Exchange a one-time code for a session.
     * Called by the SPA through the dev-server proxy so the session cookie
     * is set on the SPA origin.
     */
    public function exchange(Request $request)
    {
        $code = $request->input('code');
        $userId = cache()->pull('google_auth_' . $code);

        if (!$userId) {
            return response()->json(['error' => 'Invalid or expired code'], 401);
        }

        $user = User::find($userId);
        Auth::login($user);

        return response()->json([
            'message' => 'Authenticated',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
            ],
        ]);
    }
}