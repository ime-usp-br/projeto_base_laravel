<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ConfirmablePasswordController extends Controller
{
    /**
     * Exibe a visão de confirmação de senha.
     *
     * @return \Illuminate\View\View
     */
    public function show(): View
    {
        return view('auth.confirm-password');
    }

    /**
     * Confirma a senha do usuário.
     *
     * @param \Illuminate\Http\Request $request A requisição atual.
     * @return \Illuminate\Http\RedirectResponse Redireciona para a intenção original ou dashboard.
     * @throws \Illuminate\Validation\ValidationException Se a senha fornecida for inválida.
     */
    public function store(Request $request): RedirectResponse
    {
        if (! Auth::guard('web')->validate([
            'email' => $request->user()->email,
            'password' => $request->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        $request->session()->put('auth.password_confirmed_at', time());

        return redirect()->intended(route('dashboard', absolute: false));
    }
}