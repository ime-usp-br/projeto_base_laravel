@extends('layouts.app') {{-- Or your specific admin layout if different --}}

@section('title', 'Criar Usuário por Nº USP')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-semibold mb-6">Criar Novo Usuário por Número USP</h1>

    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        <form method="POST" action="{{ route('admin.users.store.usp') }}">
            @csrf

            <div class="mb-4">
                <label for="codpes" class="block text-gray-700 text-sm font-bold mb-2">
                    Número USP (CodPes): <span class="text-red-500">*</span>
                </label>
                <input
                    id="codpes"
                    type="number" {{-- Use number, or text if codpes can have leading zeros/non-digits --}}
                    name="codpes"
                    value="{{ old('codpes') }}"
                    required
                    autofocus
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('codpes') border-red-500 @enderror"
                    placeholder="Digite o número USP do usuário"
                >
                @error('codpes')
                    <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                @enderror
                 <p class="text-gray-600 text-xs italic mt-2">
                    O sistema buscará os dados (nome, email principal) do Replicado USP.
                    A senha inicial será gerada aleatoriamente e informada após a criação (ou enviada por um canal seguro - ajuste conforme política).
                    O usuário será criado com a permissão 'usp_user'.
                 </p>
            </div>

            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Buscar e Criar Usuário
                </button>
                <a href="{{ route('admin.dashboard') }}" {{-- Link back to dashboard or user list --}}
                   class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@endsection