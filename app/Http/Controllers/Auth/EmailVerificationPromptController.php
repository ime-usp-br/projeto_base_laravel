<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationPromptController extends Controller
{
    /**
     * Exibe o aviso de verificação de e-mail ou redireciona se já verificado.
     *
     * @param \Illuminate\Http\Request $request A requisição atual.
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View Redireciona para a página inicial se verificado, caso contrário, exibe a visão de aviso.
     */
    public function __invoke(Request $request): RedirectResponse|View
    {
        return $request->user()->hasVerifiedEmail()
                    ? redirect('/?verified=1')
                    : view('auth.verify-email');
    }
}