{{-- resources/views/auth/confirm-notice.blade.php (New File) --}}
@extends('layouts.app') {{-- Use USP Theme Layout --}}

@section('title', 'Confirme seu Email') {{-- Set Title --}}

@section('content') {{-- Wrap Content --}}
{{-- Container for centering and padding --}}
<div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
    {{-- Card styling --}}
    <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">

        <div class="mb-4 text-lg font-semibold text-gray-800">
            Registro Quase Completo!
        </div>

        <div class="mb-4 text-sm text-gray-600">
            Obrigado por se registrar! Enviamos um link de verificação para o seu endereço de e-mail.
        </div>
        <div class="mb-4 text-sm text-gray-600">
           **Por favor, clique no link enviado para ativar sua conta.** O link é válido por 60 minutos.
        </div>
        <div class="mb-4 text-sm text-gray-600">
           Se não encontrar o e-mail, verifique sua pasta de spam ou lixo eletrônico. Após verificar, você poderá fazer login.
        </div>

        {{-- Optional: Add a link back to login page --}}
        <div class="flex items-center justify-end mt-4">
             <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                 {{ __('Ir para Login') }}
             </a>
         </div>

    </div>
</div>
@endsection