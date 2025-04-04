@extends('layouts.app') {{-- Use USP Theme Layout --}}

@section('title', 'Verificar Email') {{-- Set Title --}}

@section('content') {{-- Wrap Content --}}
<div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100"> 
    <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg"> 
        <div class="mb-4 text-sm text-gray-600"> 
            {{ __('Obrigado por se inscrever! Antes de começar, você poderia verificar seu endereço de e-mail clicando no link que acabamos de enviar para você? Se você não recebeu o e-mail, teremos prazer em lhe enviar outro.') }}
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="mb-4 font-medium text-sm text-green-600"> 
                {{ __('Um novo link de verificação foi enviado para o endereço de e-mail que você forneceu durante o registro.') }}
            </div>
        @endif

        <div class="mt-4 flex items-center justify-between"> 
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf

                <div>
                    <x-primary-button>
                        {{ __('Reenviar Email de Verificação') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection