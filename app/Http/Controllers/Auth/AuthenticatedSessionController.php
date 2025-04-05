<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Exibe a visão de login.
     *
     * @return \Illuminate\View\View
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Processa uma requisição de autenticação recebida.
     *
     * @param \App\Http\Requests\Auth\LoginRequest $request A requisição de login validada.
     * @return \Illuminate\Http\RedirectResponse Redireciona para a página inicial após o login.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect('/');
    }

    /**
     * Destrói uma sessão autenticada (logout).
     *
     * @param \Illuminate\Http\Request $request A requisição atual.
     * @return \Illuminate\Http\RedirectResponse Redireciona para a página inicial após o logout.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}