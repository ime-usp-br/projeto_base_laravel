@extends('layouts.app') {{-- Use USP Theme Layout --}}

@section('title', 'Redefinir Senha') {{-- Set Title --}}

@section('content') {{-- Wrap Content --}}
<div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100"> 
    <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg"> 
        <form method="POST" action="{{ route('password.store') }}" {{ App\Helpers\disableValidationIfTesting() }}>
            @csrf

            <!-- Password Reset Token -->
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <!-- Email Address -->
            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" /> 
                <x-input-error :messages="$errors->get('email')" class="mt-2" /> 
            </div>

            <!-- Password -->
            <div class="mt-4"> 
                <x-input-label for="password" :value="__('Nova Senha')" />
                <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" /> 
                <x-input-error :messages="$errors->get('password')" class="mt-2" /> 
            </div>

            <!-- Confirm Password -->
            <div class="mt-4"> 
                <x-input-label for="password_confirmation" :value="__('Confirmar Nova Senha')" />
                <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" /> 
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" /> 
            </div>

            <div class="flex items-center justify-end mt-4"> 
                <x-primary-button>
                    {{ __('Redefinir Senha') }}
                </x-primary-button>
            </div>
        </form>
    </div>
</div>
@endsection