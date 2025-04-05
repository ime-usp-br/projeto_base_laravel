<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RequestLocalPasswordLinkRequest;
use App\Http\Requests\Auth\SetLocalPasswordRequest;
use App\Models\User;
use App\Notifications\SendLocalPasswordLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class UspLocalPasswordController extends Controller
{
    /**
     * Exibe o formulário para solicitar o link de definição de senha local.
     *
     * @return \Illuminate\View\View
     */
    public function showRequestForm(): View
    {
        return view('auth.request-local-password');
    }

    /**
     * Envia o link para definição de senha local para um usuário USP válido.
     *
     * @param \App\Http\Requests\Auth\RequestLocalPasswordLinkRequest $request Requisição validada contendo o e-mail USP.
     * @return \Illuminate\Http\RedirectResponse Redireciona de volta com status ou erros.
     */
    public function sendLink(RequestLocalPasswordLinkRequest $request): RedirectResponse
    {
        $user = User::where('email', $request->validated()['email'])->first();

        if (!$user || empty($user->codpes) || !($user->can('user', 'senhaunica'))) {
            Log::warning("Tentativa de senha local para usuário não USP ou sem permissão/role: {$request->validated()['email']}");
             return back()->withErrors(['email' => 'Este email não pertence a um usuário USP válido no sistema.']);
        }

        try {
            $user->notify(new SendLocalPasswordLink($user));
        } catch (\Exception $e) {
             Log::error("Erro ao enviar link de senha local para {$user->email}", ['exception' => $e]);
             return back()->withErrors(['email' => 'Não foi possível enviar o email no momento. Tente novamente mais tarde.']);
        }

        return back()->with('status', __('Se um usuário USP válido existir com este email, um link para definir senha local será enviado!'));
    }

    /**
     * Exibe o formulário para definir a senha local, validando os parâmetros da URL assinada.
     *
     * @param \Illuminate\Http\Request $request A requisição atual contendo os parâmetros da URL assinada.
     * @return \Illuminate\View\View|\Illuminate\Http\Response Retorna a visão ou aborta com 403 se os parâmetros forem inválidos.
     */
    public function showSetForm(Request $request): View|\Illuminate\Http\Response
    {
        $email = $request->query('email');

        if (!$email) {
            abort(403, 'Parâmetro de email ausente.');
        }

        $expires = $request->query('expires');
        $signature = $request->query('signature');

        if (!$email || !$expires || !$signature) {
            Log::warning('Tentativa de acesso a set-local-password com parâmetros ausentes.', $request->query());
            abort(403, 'Link inválido ou expirado. Por favor, solicite um novo link.');
        }

        return view('auth.set-local-password', [
            'email' => $email,
            'signature' => $signature,
            'expires' => $expires,
        ]);
    }

    /**
     * Define a senha local para o usuário USP.
     *
     * @param \App\Http\Requests\Auth\SetLocalPasswordRequest $request Requisição validada contendo a nova senha e os parâmetros da URL.
     * @return \Illuminate\Http\RedirectResponse Redireciona para a página inicial com status ou de volta com erros.
     */
    public function setPassword(SetLocalPasswordRequest $request): RedirectResponse
    {
         $originalParams = [
             'email' => $request->input('email'),
         ];

        $user = User::where('email', $request->validated()['email'])->first();

        if (!$user || empty($user->codpes) || !($user->can('user', 'senhaunica'))) {
            Log::error("Falha ao definir senha local: usuário não encontrado ou inválido (POST).", ['email' => $request->validated()['email']]);
            return back()->withErrors(['email' => 'Usuário não encontrado ou inválido.']);
        }

        $user->password = Hash::make($request->validated()['password']);
        if (is_null($user->email_verified_at)) {
            $user->email_verified_at = now();
        }
        $user->save();

        Auth::login($user);

        return redirect('/')->with('status', 'Senha local definida com sucesso!');
    }
}