<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Exibe a visão de solicitação de link de redefinição de senha.
     *
     * @return \Illuminate\View\View
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Processa uma requisição de link de redefinição de senha recebida.
     *
     * @param \Illuminate\Http\Request $request A requisição atual.
     * @return \Illuminate\Http\RedirectResponse Redireciona de volta com status ou erros.
     * @throws \Illuminate\Validation\ValidationException Se a validação falhar.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status == Password::RESET_LINK_SENT
                    ? back()->with('status', __('passwords.sent'))
                    : back()->withInput($request->only('email'))
                        ->withErrors(['email' => __($status)]);
    }
}