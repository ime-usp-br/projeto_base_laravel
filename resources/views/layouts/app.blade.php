{{-- resources/views/layouts/app.blade.php --}}
@extends('laravel-usp-theme::master')


@section('title')
  @parent
  {{ $title ?? '' }}
@endsection

@section('styles')
  @parent
  @vite('resources/css/app.css')
  <style>

  </style>
  @stack('styles')
@endsection


@section('flash')
    <div class="container mx-auto px-4 pt-4">
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Sucesso!</strong>
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif


        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Erro!</strong>
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif


        @if (session('warning'))
             <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Atenção!</strong>
                <span class="block sm:inline">{{ session('warning') }}</span>
            </div>
        @endif


         @if (session('info'))
              <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4" role="alert">
                 <strong class="font-bold">Info:</strong>
                 <span class="block sm:inline">{{ session('info') }}</span>
             </div>
         @endif


         @if ($errors->any())
             <div class="alert alert-danger">
                <ul>
                     @foreach ($errors->all() as $error)
                         <li>{{ $error }}</li>
                     @endforeach
                 </ul>
            </div>
         @endif
    </div>
@overwrite





@section('javascripts_bottom')
  @parent
  @vite('resources/js/app.js')


  @auth
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const logoutLink = document.getElementById('usp-theme-logout-link');
      if (logoutLink) {
        logoutLink.addEventListener('click', function (event) {
          event.preventDefault();


          let form = document.createElement('form');
          form.method = 'POST';
          form.action = '{{ route('logout') }}';
          form.style.display = 'none';


          let csrfInput = document.createElement('input');
          csrfInput.type = 'hidden';
          csrfInput.name = '_token';

          csrfInput.value = '{{ csrf_token() }}';
          form.appendChild(csrfInput);


          document.body.appendChild(form);
          form.submit();
        });
      }
    });
  </script>
  @endauth

  <script>


  </script>
  @stack('scripts')
@endsection