<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Exibe o formulário de perfil do usuário.
     *
     * @param \Illuminate\Http\Request $request A requisição atual.
     * @return \Illuminate\View\View
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Atualiza as informações de perfil do usuário.
     *
     * @param \App\Http\Requests\ProfileUpdateRequest $request Requisição validada com os dados do perfil.
     * @return \Illuminate\Http\RedirectResponse Redireciona de volta para a página de edição do perfil com status.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Exclui a conta do usuário.
     *
     * @param \Illuminate\Http\Request $request A requisição atual.
     * @return \Illuminate\Http\RedirectResponse Redireciona para a página inicial.
     * @throws \Illuminate\Validation\ValidationException Se a senha fornecida for inválida.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}