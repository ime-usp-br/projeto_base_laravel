{{-- resources/views/dashboard.blade.php --}}
@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container mx-auto py-12 px-4 sm:px-6 lg:px-8">

    {{-- Header remains the same --}}
    <header class="bg-white shadow mb-6">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard
                {{-- Add a subtle indicator for admins on their main dashboard --}}
                @role('admin')
                    <span class="text-sm text-gray-500 ml-2">(Visão Geral - Admin)</span>
                @endrole
            </h2>
        </div>
    </header>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        {{-- Default logged-in message --}}
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 text-gray-900">
                {{ __('Você está logado!') }} Bem-vindo(a), {{ Auth::user()->name }}!
            </div>
        </div>

        {{-- ADMIN QUICK LINKS BLOCK --}}
        @role('admin')
        {{-- Container com cor suave (ex: azul claro) --}}
        <div class="bg-blue-50 border border-blue-200 p-6 rounded-lg shadow-sm mb-6">
             <h3 class="text-lg font-semibold text-blue-800 mb-3"><i class="fas fa-user-shield mr-2"></i>Acesso Rápido Admin</h3>
             <p class="text-sm text-blue-700 mb-4">Atalhos para as funções administrativas.</p>
             {{-- Grid para organizar os botões --}}
             <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                 {{-- Botão para Listar Usuários (implementado) --}}
                 <a href="{{ route('admin.users.index') }}"
                    class="inline-block bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded text-sm text-center transition-colors duration-200">
                     <i class="fas fa-users mr-1"></i> Listar Usuários
                 </a>

                 {{-- Botão para Criar Usuário USP --}}
                 <a href="{{ route('admin.users.create.usp') }}"
                    class="inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm text-center transition-colors duration-200">
                     <i class="fas fa-user-plus mr-1"></i> Novo Usuário (Busca USP)
                 </a>

                 {{-- Botão para Criar Usuário Manual --}}
                 <a href="{{ route('admin.users.create.manual') }}"
                    class="inline-block bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm text-center transition-colors duration-200">
                     <i class="fas fa-user-edit mr-1"></i> Novo Usuário (Manual)
                 </a>

                 {{-- Link para a listagem de usuários do senhaunica-socialite (se ainda for útil) --}}
                 <a href="{{ route('senhaunica-users.index') }}"
                    class="inline-block bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-sm text-center transition-colors duration-200"
                    title="Gerenciamento de permissões via pacote Senha Única">
                     <i class="fas fa-key mr-1"></i> Permissões (Senha Única)
                 </a>

             </div>
             {{-- Nota Explicativa Opcional --}}
             <p class="text-xs text-gray-600 mt-4">
                 Acesse a <a href="{{ route('admin.dashboard') }}" class="underline hover:text-blue-700">Área Administrativa Completa</a> para mais opções.
             </p>
        </div>
        @endrole

        {{-- Placeholder Content for other roles (if needed) --}}
        {{--
        @role('usp_user|external_user')
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 text-gray-900">
                Conteúdo específico para usuários não-admin.
            </div>
        </div>
        @endrole
        --}}

    </div> {{-- End max-w-7xl --}}
</div> {{-- End container --}}
@endsection