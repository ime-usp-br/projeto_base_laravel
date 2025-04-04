{{-- resources/views/layouts/app.blade.php --}}
@extends('laravel-usp-theme::master')

{{-- Blocos do laravel-usp-theme --}}
{{-- @include('laravel-usp-theme::blocos.sticky') --}}
{{-- @include('laravel-usp-theme::blocos.spinner') --}}
{{-- @include('laravel-usp-theme::blocos.datatable-simples') --}}
{{-- Fim de blocos do laravel-usp-theme --}}

@section('title')
  @parent {{-- Includes default title from theme config --}}
  {{ $title ?? '' }} {{-- Allow child views to override/append title --}}
@endsection

@section('styles')
  @parent {{-- Include theme's base styles --}}
  @vite('resources/css/app.css') {{-- Link Vite CSS Output --}}
  <style>
    /* Your application-specific global styles */
  </style>
  @stack('styles')
@endsection

{{-- Added Section for Flash Messages --}}
@section('content_header')
    @if (session('success') || session('error') || session('warning') || session('info') || $errors->any())
    <div class="container mx-auto px-4 pt-4"> {{-- Consistent padding --}}
        {{-- Success Message --}}
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Sucesso!</strong>
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        {{-- Error Message --}}
        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Erro!</strong>
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        {{-- Warning Message --}}
        @if (session('warning'))
             <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Atenção!</strong>
                <span class="block sm:inline">{{ session('warning') }}</span>
            </div>
        @endif

         {{-- Info Message --}}
         @if (session('info'))
              <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4" role="alert">
                 <strong class="font-bold">Info:</strong>
                 <span class="block sm:inline">{{ session('info') }}</span>
             </div>
         @endif

         {{-- Validation Errors --}}
         @if ($errors->any())
             <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                 <strong class="font-bold">Erro de Validação!</strong>
                 <ul>
                     @foreach ($errors->all() as $error)
                         <li>{{ $error }}</li>
                     @endforeach
                 </ul>
             </div>
         @endif
    </div>
    @endif
@endsection


{{-- Main Content Area (Yielded by laravel-usp-theme::master) --}}
{{-- Child views use @section('content') ... @endsection --}}


@section('javascripts_bottom')
  @parent {{-- Include theme's base scripts --}}
  @vite('resources/js/app.js') {{-- Link Vite JS Output --}}

  {{-- JavaScript for Logout Link --}}
  @auth
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const logoutLink = document.getElementById('usp-theme-logout-link');
      if (logoutLink) {
        logoutLink.addEventListener('click', function (event) {
          event.preventDefault(); // Prevent default link behavior

          // Create a hidden form
          let form = document.createElement('form');
          form.method = 'POST';
          form.action = '{{ route('logout') }}';
          form.style.display = 'none'; // Make it invisible

          // Add CSRF token
          let csrfInput = document.createElement('input');
          csrfInput.type = 'hidden';
          csrfInput.name = '_token';
          csrfInput.value = '{{ csrf_token() }}'; // Get CSRF token from Blade
          form.appendChild(csrfInput);

          // Append form to body and submit
          document.body.appendChild(form);
          form.submit();
        });
      }
    });
  </script>
  @endauth

  <script>
    // Your application-specific global scripts
    // console.log('USP Theme Authenticated Layout JS Loaded');
  </script>
  @stack('scripts')
@endsection