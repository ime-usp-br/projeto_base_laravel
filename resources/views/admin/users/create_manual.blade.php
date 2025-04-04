@extends('layouts.app')

@section('title', 'Criar Usuário Manualmente')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-semibold mb-6">Criar Novo Usuário Manualmente</h1>

    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        <form method="POST" action="{{ route('admin.users.store.manual') }}">
            @csrf

            {{-- Name --}}
            <div class="mb-4">
                <label for="name" class="block text-gray-700 text-sm font-bold mb-2">
                    Nome Completo: <span class="text-red-500">*</span>
                </label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('name') border-red-500 @enderror">
                @error('name')
                    <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                @enderror
            </div>

            {{-- Email --}}
            <div class="mb-4">
                <label for="email" class="block text-gray-700 text-sm font-bold mb-2">
                    Email: <span class="text-red-500">*</span>
                </label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('email') border-red-500 @enderror">
                @error('email')
                    <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                @enderror
                <p class="text-gray-600 text-xs italic mt-2">
                    Para usuários externos, um email de verificação será enviado.
                 </p>
            </div>

            {{-- CodPes (Optional) --}}
            <div class="mb-4">
                <label for="codpes" class="block text-gray-700 text-sm font-bold mb-2">
                    Número USP (CodPes) (Opcional):
                </label>
                <input id="codpes" type="number" name="codpes" value="{{ old('codpes') }}"
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('codpes') border-red-500 @enderror">
                @error('codpes')
                    <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                @enderror
                 <p class="text-gray-600 text-xs italic mt-2">
                    Se preenchido, o usuário receberá a permissão 'usp_user'. Caso contrário, receberá 'external_user'.
                 </p>
            </div>

             {{-- Suggested Password Info --}}
             @isset($suggestedPassword)
             <div class="mb-4 p-3 bg-blue-100 border border-blue-300 rounded">
                 <p class="text-sm text-blue-800">Sugestão de senha segura:
                    <strong class="font-mono">{{ $suggestedPassword }}</strong>
                    <button type="button" onclick="copyToClipboard('{{ $suggestedPassword }}')" class="ml-2 text-xs bg-blue-500 text-white px-1 py-0.5 rounded hover:bg-blue-600">Copiar</button>
                 </p>
                 <p class="text-xs text-blue-700 mt-1">Use esta senha ou defina uma diferente abaixo.</p>
             </div>
             @endisset

            {{-- Password --}}
            <div class="mb-4">
                <label for="password" class="block text-gray-700 text-sm font-bold mb-2">
                    Senha: <span class="text-red-500">*</span>
                </label>
                <input id="password" type="password" name="password" required autocomplete="new-password"
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline @error('password') border-red-500 @enderror">
                {{-- Note: No old('password') for security --}}
                @error('password')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
            </div>

            {{-- Confirm Password --}}
            <div class="mb-6">
                <label for="password_confirmation" class="block text-gray-700 text-sm font-bold mb-2">
                    Confirmar Senha: <span class="text-red-500">*</span>
                </label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>


            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Criar Usuário
                </button>
                 <a href="{{ route('admin.dashboard') }}" {{-- Link back to dashboard or user list --}}
                   class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Simple JS for copy-to-clipboard --}}
<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('Senha sugerida copiada para a área de transferência!');
    }, function(err) {
        alert('Erro ao copiar senha: ', err);
    });
}
</script>

@endsection