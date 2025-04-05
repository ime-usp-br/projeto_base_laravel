<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class VerifyEmailController extends Controller
{
    /**
     * Marca o endereço de e-mail do usuário como verificado.
     *
     * Este método processa a requisição recebida do link de verificação.
     * Não requer que o usuário esteja pré-autenticado.
     *
     * @param \Illuminate\Http\Request $request A requisição atual contendo os parâmetros da rota 'id' e 'hash'.
     * @return \Illuminate\Http\RedirectResponse Redireciona para a página inicial com status ou para o login com erros.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $user = User::find($request->route('id'));

        if (! $user) {
             Log::warning('Verification attempt failed: User not found.', ['id' => $request->route('id')]);

             return redirect()->route('login')->withErrors(['email' => 'Link de verificação inválido ou usuário não encontrado.']);
        }

        if (! hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
            Log::warning('Verification attempt failed: Invalid hash.', ['id' => $user->id, 'route_hash' => $request->route('hash')]);

             return redirect()->route('login')->withErrors(['email' => 'Link de verificação inválido.']);
        }

        if ($user->hasVerifiedEmail()) {
            Log::info('User already verified, logging in.', ['id' => $user->id]);
            Auth::login($user);

            return redirect('/?verified=1&already=1');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
            Log::info('User email marked as verified.', ['id' => $user->id]);
        } else {
             Log::error('Failed to mark email as verified for user.', ['id' => $user->id]);

             return redirect()->route('login')->withErrors(['email' => 'Não foi possível verificar o email. Tente novamente mais tarde.']);
        }

        Auth::login($user);
        Log::info('User logged in after verification.', ['id' => $user->id]);

        return redirect('/?verified=1');
    }
}