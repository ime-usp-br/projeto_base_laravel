{{-- resources/views/layouts/admin.blade.php --}}
@extends('layouts.app') {{-- Inherit from the main app layout --}}

{{-- You can override sections here if needed, e.g., add an admin-specific title suffix --}}
@section('title')
    @parent - Admin
@endsection

@section('content')
<div class="container mx-auto px-4 py-8">
    {{-- Admin Area Header (Optional, but good practice) --}}
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">Área Administrativa</h1>
        {{-- You could add breadcrumbs or other admin-specific nav here --}}
    </div>

    {{-- Main Content for Admin Pages --}}
    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        @yield('admin_content') {{-- Use a different yield name to avoid conflicts if nesting layouts --}}
    </div>
</div>
@endsection

{{-- Push any admin-specific scripts or styles if needed --}}
@push('scripts')
<script>
    // console.log('Admin layout script loaded');
</script>
@endpush

@push('styles')
<style>
    /* Admin-specific styles */
</style>
@endpush