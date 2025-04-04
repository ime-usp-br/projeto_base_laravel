{{-- resources/views/auth/set-local-password.blade.php --}}
@extends('layouts.app') {{-- Use USP Theme Layout --}}

@section('title', 'Definir Senha Local') {{-- Set Title --}}

@section('content') {{-- Wrap Content --}}
{{-- Container for centering and padding --}}
<div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
    {{-- Card styling for the form --}}
    <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">

        {{-- Check if required parameters exist from the signed URL. Added safeguard. --}}
        @if (!isset($email) || !isset($expires) || !isset($signature))
            <div class="mb-4 font-medium text-sm text-red-600">
                 Link inválido ou expirado. Por favor, solicite um novo link.
            </div>
             <div class="flex items-center justify-start mt-4">
                 <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('local-password.request') }}">
                     {{ __('Solicitar Novo Link') }}
                 </a>
             </div>
        @else
            <h2 class="text-lg font-medium text-gray-900 mb-4">
                Defina sua nova senha local para {{ $email }}
            </h2>

            <form method="POST" action="{{ route('local-password.set') }}"> {{-- POST to the setPassword route --}}
                @csrf

                {{-- Hidden fields to pass data necessary for POST validation --}}
                {{-- The controller MUST re-validate the signature on POST --}}
                <input type="hidden" name="email" value="{{ $email }}">
                <input type="hidden" name="expires" value="{{ $expires }}">
                <input type="hidden" name="signature" value="{{ $signature }}">


                <!-- New Password -->
                <div class="mt-4">
                    <x-input-label for="password" :value="__('Nova Senha')" />
                    <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
                    {{-- Display validation errors for password --}}
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Confirm New Password -->
                <div class="mt-4">
                    <x-input-label for="password_confirmation" :value="__('Confirmar Nova Senha')" />
                    <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
                    {{-- Display validation errors for password_confirmation --}}
                     <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                <div class="flex items-center justify-end mt-4">
                    <x-primary-button>
                        {{ __('Definir Nova Senha') }}
                    </x-primary-button>
                </div>
            </form>
        @endif
    </div>
</div>
@endsection