<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RequestLocalPasswordLinkRequest extends FormRequest
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
            'email' => ['required', 'string', 'email', 'max:255', 'ends_with:usp.br', 'exists:users,email'],
        ];
    }

    /**
     * Obtém as mensagens de erro personalizadas para as regras de validação.
     *
     * @return array<string, string> Mensagens de erro personalizadas.
     */
     public function messages(): array {
         return [
             'email.exists' => 'Nenhum usuário USP encontrado com este email.',
             'email.ends_with' => 'Por favor, forneça um email usp.br válido.',
         ];
     }
}