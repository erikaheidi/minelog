<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;
use Throwable;

class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to Google's OAuth consent screen.
     */
    public function redirect(): SymfonyRedirectResponse
    {
        return $this->driver()->redirect();
    }

    /**
     * Handle the callback from Google and log the user in.
     */
    public function callback(): RedirectResponse
    {
        try {
            $googleUser = $this->driver()->user();
        } catch (Throwable) {
            return redirect()->route('login')->withErrors([
                'email' => __('Unable to sign in with Google. Please try again.'),
            ]);
        }

        $user = User::where('google_id', $googleUser->getId())->first();

        if ($user === null) {
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user !== null) {
                $user->update(['google_id' => $googleUser->getId()]);
            } else {
                $user = User::create([
                    'name' => $googleUser->getName() ?? $googleUser->getNickname() ?? $googleUser->getEmail(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                ]);

                // Google has already verified ownership of the email address.
                $user->forceFill(['email_verified_at' => now()])->save();
            }
        }

        Auth::login($user, remember: true);

        return redirect()->intended(config('fortify.home'));
    }

    /**
     * Resolve the Google driver with an explicit redirect URL.
     *
     * The redirect URI must be identical on the redirect and the token
     * exchange, so it is set here for both. It defaults to the named
     * callback route (derived from APP_URL) and only falls back to the
     * configured value when GOOGLE_REDIRECT_URI is explicitly set.
     */
    private function driver(): Provider
    {
        config([
            'services.google.redirect' => config('services.google.redirect') ?: route('google.callback'),
        ]);

        return Socialite::driver('google');
    }
}
