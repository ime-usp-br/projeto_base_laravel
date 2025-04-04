{{-- resources/views/admin/users/index.blade.php --}}
@extends('layouts.admin') {{-- Use the admin layout --}}

@section('admin_content')
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold">Listar Usuários</h2>
        <div>
             <a href="{{ route('admin.users.create.usp') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm mr-2">
                 + Criar Usuário USP
             </a>
             <a href="{{ route('admin.users.create.manual') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm">
                 + Criar Usuário Manual
             </a>
        </div>
    </div>

    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full leading-normal">
            <thead>
                <tr>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        ID
                    </th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Nome
                    </th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Email
                    </th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Nº USP
                    </th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Papéis
                    </th>
                     <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Verificado?
                    </th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Criado em
                    </th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Ações
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                            {{ $user->id }}
                        </td>
                        <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                            {{ $user->name }}
                        </td>
                        <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                            {{ $user->email }}
                        </td>
                        <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                            {{ $user->codpes ?? 'N/A' }}
                        </td>
                        <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                            @foreach($user->getRoleNames() as $roleName)
                                <span class="inline-block bg-gray-200 rounded-full px-3 py-1 text-xs font-semibold text-gray-700 mr-2 mb-1">
                                    {{ $roleName }}
                                </span>
                            @endforeach
                        </td>
                         <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                             @if($user->hasVerifiedEmail())
                                 <span class="text-green-600 font-semibold">Sim</span>
                                 ({{ $user->email_verified_at->format('d/m/Y H:i') }})
                             @else
                                 <span class="text-red-600 font-semibold">Não</span>
                             @endif
                         </td>
                        <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                            {{ $user->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                            {{-- Placeholder for future actions --}}
                            <a href="#" class="text-indigo-600 hover:text-indigo-900 text-xs mr-2">Editar</a>
                            <a href="#" class="text-red-600 hover:text-red-900 text-xs">Excluir</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center text-gray-500">
                            Nenhum usuário encontrado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Pagination Links --}}
        <div class="px-5 py-5 bg-white border-t flex flex-col xs:flex-row items-center xs:justify-between">
            {{ $users->links() }}
        </div>
    </div>
@endsection