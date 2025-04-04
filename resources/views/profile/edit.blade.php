@extends('layouts.app') {{-- Use USP Theme Layout --}}

@section('title', 'Perfil') {{-- Set Title --}}

@section('content') {{-- Wrap Content --}}
    {{-- Container for spacing, using Tailwind (no prefix needed now) --}}
    <div class="container mx-auto py-12 px-4 sm:px-6 lg:px-8">
        {{-- Original Breeze Header structure adapted --}}
         <header class="bg-white shadow mb-6"> 
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8"> 
                 <h2 class="font-semibold text-xl text-gray-800 leading-tight"> 
                     {{ __('Perfil') }}
                 </h2>
            </div>
        </header>

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6"> 
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg"> 
                <div class="max-w-xl"> 
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg"> 
                <div class="max-w-xl"> 
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg"> 
                <div class="max-w-xl"> 
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
@endsection