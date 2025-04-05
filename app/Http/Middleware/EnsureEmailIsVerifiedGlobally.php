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
    /**
     * Processa uma requisição recebida.
     *
     * Redireciona usuários autenticados mas não verificados para a página de aviso de verificação,
     * a menos que estejam acessando rotas relacionadas à verificação ou logout.
     *
     * @param \Illuminate\Http\Request $request A requisição HTTP recebida.
     * @param \Closure $next O próximo middleware na pipeline.
     * @return \Symfony\Component\HttpFoundation\Response|\Illuminate\Http\RedirectResponse A resposta HTTP.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (
            Auth::guard('web')->check() &&
            ($user = Auth::guard('web')->user()) instanceof MustVerifyEmail &&
            ! $user->hasVerifiedEmail() &&

            ! $request->routeIs([
                'verification.notice',
                'verification.send',
                'verification.verify',
                'logout',

                ])
        ) {
            return redirect()->route('verification.notice');
        }

        return $next($request);
    }
}