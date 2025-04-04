{{-- resources/views/auth/register.blade.php --}}
@extends('layouts.app') {{-- Use USP Theme Layout --}}

@section('title', 'Registrar') {{-- Set Title --}}

@section('content') {{-- Wrap Content --}}
{{-- Add a container for centering and padding, adjust classes as needed for USP theme --}}
<div class="container mx-auto flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
    {{-- Card styling similar to Breeze's guest layout for visual consistency --}}
    <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">

        {{-- Alpine.js component scope --}}
        <form method="POST" action="{{ route('register') }}" x-data="{ userType: '{{ old('user_type', 'external') }}' }">
            @csrf

            <!-- Name -->
            <div>
                <x-input-label for="name" :value="__('Nome Completo')" />
                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <!-- Email Address -->
            <div class="mt-4">
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                {{-- Hint for USP email format (using Alpine to show conditionally) --}}
                <p x-show="userType === 'usp'" class="text-sm text-gray-600 mt-1">
                    O email para membros USP deve terminar com @usp.br.
                </p>
            </div>

            <!-- User Type Selection -->
            <div class="mt-4">
                <x-input-label :value="__('Tipo de Usuário')" />
                <div class="flex items-center mt-1 space-x-4">
                    {{-- External User Radio --}}
                    <label for="user_type_external" class="inline-flex items-center">
                        <input id="user_type_external" type="radio" name="user_type" value="external" x-model="userType"
                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <span class="ms-2 text-sm text-gray-600">{{ __('Externo') }}</span>
                    </label>
                    {{-- USP Community Radio --}}
                    <label for="user_type_usp" class="inline-flex items-center">
                        <input id="user_type_usp" type="radio" name="user_type" value="usp" x-model="userType"
                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <span class="ms-2 text-sm text-gray-600">{{ __('Comunidade USP') }}</span>
                    </label>
                </div>
                 <x-input-error :messages="$errors->get('user_type')" class="mt-2" />
            </div>

            <!-- CodPes (Conditional) -->
            {{-- This div is shown/hidden by Alpine based on userType --}}
            <div class="mt-4" x-show="userType === 'usp'" x-transition>
                <x-input-label for="codpes" :value="__('Número USP (CodPes)')" />
                {{-- The :required attribute makes HTML5 validation dynamic --}}
                <x-text-input id="codpes" class="block mt-1 w-full" type="number" name="codpes" :value="old('codpes')" x-bind:required="userType === 'usp'" autocomplete="off" />
                <x-input-error :messages="$errors->get('codpes')" class="mt-2" />
            </div>

            <!-- Password -->
            <div class="mt-4">
                <x-input-label for="password" :value="__('Senha')" />
                <x-text-input id="password" class="block mt-1 w-full"
                                type="password"
                                name="password"
                                required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Confirm Password -->
            <div class="mt-4">
                <x-input-label for="password_confirmation" :value="__('Confirmar Senha')" />
                <x-text-input id="password_confirmation" class="block mt-1 w-full"
                                type="password"
                                name="password_confirmation" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            {{-- Footer Links and Button --}}
            <div class="flex items-center justify-end mt-4">
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                    {{ __('Já registrado?') }}
                </a>

                <x-primary-button class="ms-4">
                    {{ __('Registrar') }}
                </x-primary-button>
            </div>
        </form>
    </div> {{-- End card --}}
</div> {{-- End container --}}
@endsection