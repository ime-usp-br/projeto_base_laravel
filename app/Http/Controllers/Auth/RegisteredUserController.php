<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Auth\RegistrationRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;

class RegisteredUserController extends Controller
{
    /**
     * Exibe a visão de registro.
     *
     * @return \Illuminate\View\View
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Processa uma requisição de registro recebida.
     *
     * @param \App\Http\Requests\Auth\RegistrationRequest $request A requisição de registro validada.
     * @return \Illuminate\Http\RedirectResponse Redireciona para a rota de aviso de confirmação.
     */
    public function store(RegistrationRequest $request): RedirectResponse
    {
        $userType = $request->input('user_type');
        $roleName = ($userType === 'usp') ? 'usp_user' : 'external_user';
        $guardName = 'web';

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'codpes' => ($userType === 'usp') ? $request->codpes : null,
            'email_verified_at' => null,
        ];

        $user = User::create($userData);

        $role = Role::firstOrCreate(
            ['name' => $roleName],
            ['guard_name' => $guardName]
        );
        $user->assignRole($role);

        event(new Registered($user));

        Log::info("Registered user type: {$userType}, Role assigned: {$roleName} for User ID: {$user->id}. Requires verification.");

        Auth::login($user);

        return redirect()->route('auth.confirm-notice');
    }
}