{{-- resources/views/auth/login.blade.php --}}
@extends('layouts.app') {{-- Use USP Theme Layout --}}

@section('title', 'Login') {{-- Set Title --}}

@section('content') {{-- Wrap Content --}}
<div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
    {{-- Widened the card: Changed sm:max-w-md to sm:max-w-lg --}}
    <div class="w-full sm:max-w-lg mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" {{ App\Helpers\disableValidationIfTesting() }}>
            @csrf

            <!-- Email Address -->
            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Password -->
            <div class="mt-4">
                <x-input-label for="password" :value="__('Senha')" />

                <x-text-input id="password" class="block mt-1 w-full"
                                type="password"
                                name="password"
                                required autocomplete="current-password" />

                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

             {{-- Moved Login Button Above Links --}}
            <div class="flex items-center justify-end mt-4">
                <a href="{{ url('socialite/login') }}"
                class="inline-flex items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-black uppercase tracking-widest hover:bg-yellow-400 focus:bg-yellow-400 active:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    {{ __('Login com Senha Única') }}
                </a>

                <x-primary-button>
                    {{ __('Log in') }}
                </x-primary-button>
            </div>

            {{-- Links Section Below Button --}}
            <div class="flex items-center justify-between mt-4"> {{-- Changed justify-end to justify-between --}}

                {{-- Group the secondary links together --}}
                <div class="flex items-center space-x-4"> {{-- Added space-x-4 for spacing --}}
                    @if (Route::has('password.request'))
                        <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                            {{ __('Esqueceu sua senha?') }}
                        </a>
                    @endif

                     {{-- Link para Registro --}}
                     @if (Route::has('register'))
                         <a href="{{ route('register') }}" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                             {{ __('Registrar-se') }}
                         </a>
                     @endif

                     {{-- Link para Senha Local USP --}}
                     @if (Route::has('local-password.request'))
                         <a href="{{ route('local-password.request') }}" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                             {{ __('Definir senha local USP') }}
                         </a>
                     @endif
                </div>

            </div>
        </form>
    </div>
</div>
@endsection