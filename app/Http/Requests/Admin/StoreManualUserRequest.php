<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreManualUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return false;
    }

    public function rules(): array {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'codpes' => ['nullable', 'numeric', 'unique:users,codpes'],
        ];
    }
}
