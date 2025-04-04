<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\RedirectResponse;

class EnsureEmailIsVerifiedGlobally
{
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated using the 'web' guard (or your default)
        if (
            Auth::guard('web')->check() && // 1. Is user authenticated?
            ($user = Auth::guard('web')->user()) instanceof MustVerifyEmail && // 2. Do they need verification?
            ! $user->hasVerifiedEmail() && // 3. Have they NOT verified yet?
            // 4. Are they NOT trying to access allowed verification-related routes?
            ! $request->routeIs([
                'verification.notice',      // The page telling them to verify
                'verification.send',        // The route to resend the email
                'verification.verify',      // The route the email link points to
                'logout',                   // Allow logging out
                // Add any other public routes they MUST be able to access while logged in but unverified
                ])
        ) {
            // 5. Redirect to the verification notice route
            return redirect()->route('verification.notice');
        }

        // User is guest, doesn't need verification, is already verified, or is on an allowed route
        return $next($request);
    }
}