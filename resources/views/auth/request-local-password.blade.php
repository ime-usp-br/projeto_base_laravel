{{-- resources/views/auth/request-local-password.blade.php --}}
@extends('layouts.app') {{-- Use USP Theme Layout --}}

@section('title', 'Solicitar Senha Local USP') {{-- Set Title --}}

@section('content') {{-- Wrap Content --}}
{{-- Container for centering and padding --}}
<div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
    {{-- Card styling for the form --}}
    <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">

        <div class="mb-4 text-sm text-gray-600">
            {{ __('Precisa definir uma senha local para acessar via email/senha em vez da Senha Única USP? Informe seu e-mail institucional (usp.br) e enviaremos um link para configuração.') }}
        </div>

        <!-- Session Status (Displays success message after link is sent) -->
        @if (session('status'))
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('local-password.request') }}" {{ App\Helpers\disableValidationIfTesting() }}> {{-- POST to the sendLink route --}}
            @csrf

            <!-- Email Address -->
            <div>
                <x-input-label for="email" :value="__('Email Institucional (usp.br)')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus/>
                {{-- Display validation errors for the email field --}}
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="flex items-center justify-end mt-4">
                <x-primary-button>
                    {{ __('Enviar Link para Definir Senha Local') }}
                </x-primary-button>
            </div>
        </form>
         {{-- Optional: Link back to login --}}
         <div class="flex items-center justify-start mt-4">
             <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                 {{ __('Voltar para Login') }}
             </a>
         </div>
    </div>
</div>
@endsection