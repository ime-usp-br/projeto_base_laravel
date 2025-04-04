<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RequestLocalPasswordLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array {
        return [
            'email' => ['required', 'string', 'email', 'max:255', 'ends_with:usp.br', 'exists:users,email'], 
        ];
    }

     public function messages() {
         return [
             'email.exists' => 'Nenhum usuário USP encontrado com este email.',
             'email.ends_with' => 'Por favor, forneça um email usp.br válido.',
         ];
     }
}
