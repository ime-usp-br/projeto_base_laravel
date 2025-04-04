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

class UspLocalPasswordController extends Controller
{
    public function showRequestForm() {
        return view('auth.request-local-password');
    }

    public function sendLink(RequestLocalPasswordLinkRequest $request) {
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

    public function showSetForm(Request $request) {
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
    
    public function setPassword(SetLocalPasswordRequest $request) {
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
