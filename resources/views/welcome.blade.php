{{-- resources/views/welcome.blade.php --}}
@extends('layouts.app')

@section('title', 'Bem-vindo')

@section('content')
<div class="container mx-auto px-4 py-12">
    <div class="bg-white shadow-md rounded-lg px-8 py-10 text-center">

        @guest
            {{-- Guest View --}}
            <h1 class="text-3xl font-semibold text-gray-800 mb-4">
                Bem-vindo ao {{ config('app.name', 'Sistema USPdev') }}
            </h1>
            <p class="text-gray-600 mb-8">
                Autentique-se para acessar os recursos do sistema.
            </p>

            <div class="flex flex-col sm:flex-row justify-center items-center space-y-4 sm:space-y-0 sm:space-x-4">
                {{-- Senha Única Login Button --}}
                <a href="{{ url(config('senhaunica.prefix', 'socialite') . '/login') }}"
                   class="inline-flex items-center px-6 py-3 bg-yellow-500 border border-transparent rounded-md font-semibold text-sm text-black uppercase tracking-widest hover:bg-yellow-400 focus:bg-yellow-400 active:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-5 h-5 mr-2 -ml-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10 2a8 8 0 100 16 8 8 0 000-16zm0 14a6 6 0 110-12 6 6 0 010 12z M9 9V5h2v4h1v2H8V9h1z"></path></svg> {{-- Simple icon placeholder --}}
                    Login com Senha Única USP
                </a>

                 {{-- Standard Login Link/Button --}}
                 <a href="{{ route('login') }}"
                    class="inline-flex items-center px-6 py-3 bg-gray-800 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                     Login com Email/Senha Local
                 </a>

                {{-- Registration Link/Button --}}
                @if (Route::has('register'))
                    <a href="{{ route('register') }}"
                       class="inline-flex items-center px-6 py-3 bg-blue-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-blue-500 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Registrar-se
                    </a>
                @endif
            </div>
        @endguest

        @auth
            {{-- Authenticated View --}}
            <h1 class="text-3xl font-semibold text-gray-800 mb-4">
                Bem-vindo(a), {{ Auth::user()->name }}!
            </h1>
            <p class="text-gray-600 mb-8">
                Você está autenticado no {{ config('app.name', 'Sistema USPdev') }}.
            </p>

            <div class="flex flex-col sm:flex-row justify-center items-center space-y-4 sm:space-y-0 sm:space-x-4">
                 {{-- Dashboard Link/Button --}}
                <a href="{{ route('dashboard') }}"
                   class="inline-flex items-center px-6 py-3 bg-green-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-green-500 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Acessar Painel Principal
                </a>

                 {{-- Admin Area Link/Button (Conditional) --}}
                 @hasrole('admin') {{-- Using Spatie's directive --}}
                     <a href="{{ route('admin.dashboard') }}"
                        class="inline-flex items-center px-6 py-3 bg-red-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-red-500 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                         Área Administrativa
                     </a>
                 @endhasrole

                 {{-- Logout Form Button --}}
                 <form method="POST" action="{{ route('logout') }}" class="inline">
                     @csrf
                     <button type="submit"
                             class="inline-flex items-center px-6 py-3 bg-gray-500 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-600 active:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                         Sair
                     </button>
                 </form>
            </div>

        @endauth
    </div>
</div>
@endsection