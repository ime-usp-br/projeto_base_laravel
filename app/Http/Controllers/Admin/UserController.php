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

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::with('roles')->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    public function createUsp() {
        return view('admin.users.create_usp');
    }

    public function storeUsp(StoreUspUserRequest $request) {
        $codpes = $request->validated()['codpes'];

        try {
            $pessoa = Pessoa::fetch($codpes); // Adjust if fetch method is different

            if (!$pessoa) {
                return back()->withInput()->withErrors(['codpes' => 'Número USP não encontrado no Replicado.']);
            }

            $emailPrincipal = Pessoa::email($codpes); // Get primary USP email
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
            
            return redirect()->route('admin.dashboard') // Or admin user list
                   ->with('success', "Usuário USP {$pessoa['nompes']} ({$codpes}) criado com sucesso. Senha inicial: {$generatedPassword}"); // TEMPORARY/INSECURE for demo

        } catch (\Exception $e) {
            Log::error("Erro ao buscar/criar usuário via Replicado: {$codpes}", ['exception' => $e]);
            return back()->withInput()->withErrors(['codpes' => 'Erro ao conectar ou processar dados do Replicado. Verifique o log.']);
        }
    }

    public function createManual() {
         $suggestedPassword = Str::password(16);
         return view('admin.users.create_manual', ['suggestedPassword' => $suggestedPassword]);
    }

    public function storeManual(StoreManualUserRequest $request) {
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

        // If external user, trigger email verification (optional here, could be done on first login attempt)
        if ($role === 'external_user') {
            // Consider sending verification email immediately or let the standard flow handle it
            // $user->sendEmailVerificationNotification(); // Requires MustVerifyEmail interface
        }

        return redirect()->route('admin.dashboard') // Or admin user list
               ->with('success', "Usuário {$validated['name']} criado manualmente com sucesso.");
    }
}
