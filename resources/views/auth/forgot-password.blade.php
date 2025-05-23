@extends('layouts.app') {{-- Use USP Theme Layout --}}

@section('title', 'Esqueci a Senha') {{-- Set Title --}}

@section('content') {{-- Wrap Content --}}
<div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100"> 
     <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg"> 
        <div class="mb-4 text-sm text-gray-600"> 
            {{ __('Esqueceu sua senha? Sem problemas. Informe seu endereço de e-mail e enviaremos um link para redefinição de senha que permitirá que você escolha uma nova.') }}
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" /> 

        <form method="POST" action="{{ route('password.email') }}" {{ App\Helpers\disableValidationIfTesting() }}>
            @csrf

            <!-- Email Address -->
            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus /> 
                <x-input-error :messages="$errors->get('email')" class="mt-2" /> 
            </div>

            <div class="flex items-center justify-end mt-4"> 
                <x-primary-button>
                    {{ __('Enviar Link de Redefinição de Senha') }}
                </x-primary-button>
            </div>
        </form>
    </div>
</div>
@endsection