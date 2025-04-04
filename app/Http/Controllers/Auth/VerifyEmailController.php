<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User; // Import User model
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request; // Use standard Request
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // For logging potential issues

class VerifyEmailController extends Controller
{
    /**
     * Mark the user's email address as verified.
     *
     * This method handles the incoming request from the verification link.
     * It does not require the user to be pre-authenticated.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        // 1. Find the user by ID from the route parameter
        $user = User::find($request->route('id'));

        // Check if user exists
        if (! $user) {
             Log::warning('Verification attempt failed: User not found.', ['id' => $request->route('id')]);
             // Redirect to login or registration with an error? Or show a generic error view?
             // Redirecting to login is often safest.
             return redirect()->route('login')->withErrors(['email' => 'Link de verificação inválido ou usuário não encontrado.']);
        }

        // 2. Manually verify the hash
        // Check if the hash from the URL matches the SHA1 hash of the user's email
        if (! hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
            Log::warning('Verification attempt failed: Invalid hash.', ['id' => $user->id, 'route_hash' => $request->route('hash')]);
            // Don't proceed if the hash doesn't match
             return redirect()->route('login')->withErrors(['email' => 'Link de verificação inválido.']);
        }

        // 3. Check if already verified
        if ($user->hasVerifiedEmail()) {
            // Already verified, just log them in and redirect
            Log::info('User already verified, logging in.', ['id' => $user->id]);
            Auth::login($user);
            // Redirect to root or dashboard, maybe with a different message?
            return redirect('/?verified=1&already=1');
        }

        // 4. Mark the email as verified
        if ($user->markEmailAsVerified()) {
            // Fire the Verified event
            event(new Verified($user));
            Log::info('User email marked as verified.', ['id' => $user->id]);
        } else {
             Log::error('Failed to mark email as verified for user.', ['id' => $user->id]);
             // Handle potential failure to update the database
             return redirect()->route('login')->withErrors(['email' => 'Não foi possível verificar o email. Tente novamente mais tarde.']);
        }

        // 5. Log the user in
        Auth::login($user);
        Log::info('User logged in after verification.', ['id' => $user->id]);

        // 6. Redirect to the root path (or dashboard)
        return redirect('/?verified=1');
    }
}