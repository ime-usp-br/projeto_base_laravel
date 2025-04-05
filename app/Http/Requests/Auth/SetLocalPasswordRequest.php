<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class SetLocalPasswordRequest extends FormRequest
{
    /**
     * Determina se o usuário está autorizado a fazer esta requisição.
     *
     * @return bool Sempre retorna `true`.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Obtém as regras de validação que se aplicam à requisição.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string> Regras de validação.
     */
    public function rules(): array {
        return [
            'password' => ['required', 'confirmed', Password::defaults()],
            'email' => ['required', 'email'],
             'signature' => ['required', 'string'],
             'expires' => ['required', 'integer'],
        ];
    }
}