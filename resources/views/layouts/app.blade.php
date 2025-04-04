      
{{-- Layout for Authenticated Users --}}
@extends('laravel-usp-theme::master')

{{-- Blocos do laravel-usp-theme (Re-enable as needed) --}}
{{-- @include('laravel-usp-theme::blocos.sticky') --}}
{{-- @include('laravel-usp-theme::blocos.spinner') --}}
{{-- @include('laravel-usp-theme::blocos.datatable-simples') --}}
{{-- Fim de blocos do laravel-usp-theme --}}


{{-- Setting Title --}}
@section('title')
  @parent {{-- Includes default title from theme config --}}
  {{ $title ?? '' }} {{-- Allow child views to override/append title --}}
@endsection

{{-- Custom Styles --}}
@section('styles')
  @parent {{-- Include theme's base styles --}}
  {{-- Link Vite CSS Output (includes prefixed Tailwind) --}}
  @vite('resources/css/app.css')
  <style>
    /* Your application-specific global styles */
  </style>
  {{-- Allow child views to push additional styles --}}
  @stack('styles')
@endsection

{{-- Main Content Area (Yielded by laravel-usp-theme::master) --}}
{{-- Child views use @section('content') ... @endsection --}}


{{-- Custom Scripts --}}
@section('javascripts_bottom')
  @parent {{-- Include theme's base scripts --}}
  {{-- Link Vite JS Output (loads Alpine, Axios, etc.) --}}
  @vite('resources/js/app.js')
  <script>
    // Your application-specific global scripts
    // console.log('USP Theme Authenticated Layout JS Loaded');
  </script>
  {{-- Allow child views to push additional scripts --}}
  @stack('scripts')
@endsection