<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;
use App\Models\User;

class RegistrationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)],
            'password' => ['required', 'confirmed', Password::defaults()],
            'user_type' => ['required', Rule::in(['usp', 'external'])],
            'codpes' => [
                Rule::requiredIf($this->input('user_type') === 'usp'),
                'nullable',
                'numeric',
                Rule::unique(User::class),
            ],
        ];

        if ($this->input('user_type') === 'usp') {
            $rules['email'][] = function ($attribute, $value, $fail) {
                if (!str_ends_with(strtolower($value), 'usp.br')) {
                    $fail('Para membros da comunidade USP, o email deve terminar com usp.br.');
                }
            };
        }

        return $rules;
    }
     public function messages()
    {
        return [
            'codpes.required' => 'O campo Número USP é obrigatório para membros da comunidade USP.',
            'codpes.numeric' => 'O campo Número USP deve ser um número.',
            'codpes.unique' => 'Este Número USP já está cadastrado.',
            'email.unique' => 'Este email já está cadastrado.',
            'email.ends_with' => 'Para membros da comunidade USP, o email deve terminar com @usp.br.',
        ];
    }
}
