<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreManualUserRequest;
use App\Http\Requests\Admin\StoreUspUserRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Uspdev\Replicado\Pessoa;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class UserController extends Controller
{
    /**
     * Exibe uma lista paginada de usuários com seus papéis.
     *
     * @return \Illuminate\View\View Visão com a lista de usuários.
     */
    public function index(): View
    {
        $users = User::with('roles')->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Exibe o formulário para criar um usuário buscando dados da USP.
     *
     * @return \Illuminate\View\View Visão do formulário de criação de usuário USP.
     */
    public function createUsp(): View
    {
        return view('admin.users.create_usp');
    }

    /**
     * Armazena um novo usuário USP buscando dados do Replicado.
     *
     * @param \App\Http\Requests\Admin\StoreUspUserRequest $request Requisição validada contendo o 'codpes'.
     * @return \Illuminate\Http\RedirectResponse Redireciona para o dashboard do admin com mensagem de sucesso ou de volta ao formulário com erros.
     * @throws \Exception Em caso de erro na comunicação ou processamento do Replicado.
     */
    public function storeUsp(StoreUspUserRequest $request): RedirectResponse
    {
        $codpes = $request->validated()['codpes'];

        try {
            $pessoa = Pessoa::fetch($codpes);

            if (!$pessoa) {
                return back()->withInput()->withErrors(['codpes' => 'Número USP não encontrado no Replicado.']);
            }

            $emailPrincipal = Pessoa::email($codpes);
            if (empty($emailPrincipal)) {
                 return back()->withInput()->withErrors(['codpes' => 'Usuário sem email principal cadastrado no Replicado.']);
            }

            if (User::where('codpes', $codpes)->orWhere('email', $emailPrincipal)->exists()) {
                 return back()->withInput()->withErrors(['codpes' => 'Usuário já cadastrado no sistema (por Nº USP ou Email).']);
            }

            $generatedPassword = Str::password(16);

            $user = User::create([
                'codpes' => $codpes,
                'name' => $pessoa['nompes'] ?? 'Nome não encontrado',
                'email' => $emailPrincipal,
                'password' => Hash::make($generatedPassword),
                'email_verified_at' => now(),
            ]);

            $user->assignRole('usp_user');

            return redirect()->route('admin.dashboard')
                   ->with('success', "Usuário USP {$pessoa['nompes']} ({$codpes}) criado com sucesso. Senha inicial: {$generatedPassword}");

        } catch (\Exception $e) {
            Log::error("Erro ao buscar/criar usuário via Replicado: {$codpes}", ['exception' => $e]);
            return back()->withInput()->withErrors(['codpes' => 'Erro ao conectar ou processar dados do Replicado. Verifique o log.']);
        }
    }

    /**
     * Exibe o formulário para criar um usuário manualmente.
     *
     * @return \Illuminate\View\View Visão do formulário de criação manual com uma senha sugerida.
     */
    public function createManual(): View
    {
         $suggestedPassword = Str::password(16);
         return view('admin.users.create_manual', ['suggestedPassword' => $suggestedPassword]);
    }

    /**
     * Armazena um novo usuário criado manualmente.
     *
     * @param \App\Http\Requests\Admin\StoreManualUserRequest $request Requisição validada com os dados do usuário.
     * @return \Illuminate\Http\RedirectResponse Redireciona para o dashboard do admin com mensagem de sucesso.
     */
    public function storeManual(StoreManualUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'codpes' => $validated['codpes'] ?? null,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'email_verified_at' => isset($validated['codpes']) ? now() : null,
        ]);

        $role = isset($validated['codpes']) ? 'usp_user' : 'external_user';
        $user->assignRole($role);

        if ($role === 'external_user') {

        }

        return redirect()->route('admin.dashboard')
               ->with('success', "Usuário {$validated['name']} criado manualmente com sucesso.");
    }
}