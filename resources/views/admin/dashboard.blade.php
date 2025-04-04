{{-- resources/views/admin/dashboard.blade.php --}}
@extends('layouts.admin') {{-- Use the admin layout --}}

@section('admin_content') {{-- Target the yield in the admin layout --}}
    <h2 class="text-xl font-semibold mb-4">Painel Administrativo</h2>

    <p class="text-gray-700 mb-6">
        Bem-vindo(a) à área administrativa. Selecione uma das opções abaixo para gerenciar o sistema.
    </p>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"> {{-- Adjusted grid columns for potential future items --}}

        {{-- Card for Listing Users --}}
        <div class="bg-gray-50 p-6 rounded-lg shadow hover:shadow-lg transition-shadow duration-200">
            <h3 class="text-lg font-semibold mb-3 text-purple-700"> <i class="fas fa-users mr-2"></i>Gerenciar Usuários</h3>
            <p class="text-sm text-gray-600 mb-4">
                Visualize, edite ou remova usuários existentes no sistema.
            </p>
            <a href="{{ route('admin.users.index') }}" {{-- Link para a rota de listagem --}}
               class="inline-block bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded text-sm transition-colors duration-200">
                Listar Usuários
            </a>
        </div>

        {{-- Card for Creating USP User --}}
        <div class="bg-gray-50 p-6 rounded-lg shadow hover:shadow-lg transition-shadow duration-200">
            <h3 class="text-lg font-semibold mb-3 text-blue-700"><i class="fas fa-user-plus mr-2"></i>Criar Usuário USP</h3>
            <p class="text-sm text-gray-600 mb-4">
                Crie um novo usuário buscando dados do Replicado USP usando o número USP (CodPes).
            </p>
            <a href="{{ route('admin.users.create.usp') }}"
               class="inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm transition-colors duration-200">
                Criar Usuário USP
            </a>
        </div>

        {{-- Card for Creating Manual User --}}
        <div class="bg-gray-50 p-6 rounded-lg shadow hover:shadow-lg transition-shadow duration-200">
            <h3 class="text-lg font-semibold mb-3 text-green-700"><i class="fas fa-user-edit mr-2"></i>Criar Usuário Manual</h3>
            <p class="text-sm text-gray-600 mb-4">
                Crie um novo usuário (USP ou externo) fornecendo manualmente todos os detalhes.
            </p>
            <a href="{{ route('admin.users.create.manual') }}"
               class="inline-block bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm transition-colors duration-200">
                Criar Usuário Manual
            </a>
        </div>

        {{-- Add more admin links/cards here as needed --}}

    </div>
@endsection