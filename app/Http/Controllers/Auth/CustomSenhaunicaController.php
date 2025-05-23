<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Uspdev\SenhaunicaSocialite\Http\Controllers\SenhaunicaController as BaseSenhaunicaController;
use Illuminate\Http\RedirectResponse;
use Spatie\Permission\Models\Role;

class CustomSenhaunicaController extends BaseSenhaunicaController
{
    /**
     * Lida com o callback do provedor Senha Única e marca o e-mail como verificado.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleProviderCallback(Request $request): RedirectResponse
    {

        $redirectResponse = parent::handleProviderCallback($request);


        if (Auth::check()) {
            $user = Auth::user();

            $role = Role::firstOrCreate(
                ['name' => 'usp_user'],
                ['guard_name' => 'web']
            );

            if(!$user->hasRole($role)){
                $user->assignRole($role);
            }

            if ($user && is_null($user->email_verified_at)) {
                $user->email_verified_at = now();
                $user->save();
            }
        }


        return $redirectResponse;
    }
}