<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class SetLocalPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    
    public function rules(): array {
        return [
            'password' => ['required', 'confirmed', Password::defaults()],
            'email' => ['required', 'email'],
             'signature' => ['required', 'string'],
             'expires' => ['required', 'integer'],
        ];
    }
}
