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
    public function create(): View
    {
        return view('auth.register');
    }

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
            'email_verified_at' => null, // <-- Always null on registration
        ];

        // Create the user
        $user = User::create($userData);

        // Ensure role exists and assign
        $role = Role::firstOrCreate(
            ['name' => $roleName],
            ['guard_name' => $guardName]
        );
        $user->assignRole($role);

        // Fire the registration event (triggers verification email because User implements MustVerifyEmail)
        event(new Registered($user));

        Log::info("Registered user type: {$userType}, Role assigned: {$roleName} for User ID: {$user->id}. Requires verification.");

        Auth::login($user);

        return redirect()->route('auth.confirm-notice');
    }
}