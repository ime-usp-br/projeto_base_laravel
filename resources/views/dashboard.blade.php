@extends('layouts.app') {{-- Use the USP theme layout --}}

@section('title', 'Dashboard') {{-- Set the title using theme's section --}}

@section('content') {{-- Wrap content in the correct section --}}
    {{-- Container for spacing, using Tailwind (no prefix needed now) --}}
    <div class="container mx-auto py-12 px-4 sm:px-6 lg:px-8">
        {{-- Original Breeze Header structure adapted --}}
        <header class="bg-white shadow mb-6"> 
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8"> 
                 <h2 class="font-semibold text-xl text-gray-800 leading-tight"> 
                     {{ __('Dashboard') }}
                 </h2>
            </div>
        </header>

        {{-- Original Breeze Content structure --}}
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8"> 
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg"> 
                <div class="p-6 text-gray-900"> 
                    {{ __("You're logged in!") }}
                </div>
            </div>
        </div>
    </div>
@endsection