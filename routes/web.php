<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController as AdminUserController; // Alias kept for clarity
use App\Http\Controllers\Auth\UspLocalPasswordController;
use Uspdev\SenhaunicaSocialite\Http\Controllers\SenhaunicaController; // Keep for callback route

// Public Routes
Route::get('/', function () {
    return view('welcome');
})->name('welcome'); // Add name for convenience

// Authentication Required Routes (General Users)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard'); // Ensure dashboard is named

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin Routes (Requires Auth + Admin Role)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Admin Dashboard
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');
    
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index'); // <-- Add this route
    Route::get('/users/create/usp', [AdminUserController::class, 'createUsp'])->name('users.create.usp');
    Route::post('/users/create/usp', [AdminUserController::class, 'storeUsp'])->name('users.store.usp');
    Route::get('/users/create/manual', [AdminUserController::class, 'createManual'])->name('users.create.manual');
    Route::post('/users/create/manual', [AdminUserController::class, 'storeManual'])->name('users.store.manual');

    // User Management Routes (already prefixed and named correctly)
    Route::get('/users/create/usp', [AdminUserController::class, 'createUsp'])->name('users.create.usp');
    Route::post('/users/create/usp', [AdminUserController::class, 'storeUsp'])->name('users.store.usp');
    Route::get('/users/create/manual', [AdminUserController::class, 'createManual'])->name('users.create.manual');
    Route::post('/users/create/manual', [AdminUserController::class, 'storeManual'])->name('users.store.manual');

    // Add other admin routes here
});

// Local Password Flow (Should be accessible to guests, potentially)
Route::get('/request-local-password', [UspLocalPasswordController::class, 'showRequestForm'])->name('local-password.request');
Route::post('/request-local-password', [UspLocalPasswordController::class, 'sendLink']); // Keep POST route name implicit or add .send
Route::get('/set-local-password', [UspLocalPasswordController::class, 'showSetForm'])->middleware('signed')->name('local-password.set');
Route::post('/set-local-password', [UspLocalPasswordController::class, 'setPassword']); // Keep POST route name implicit or add .update

// Senha Única Callback Route (Must remain accessible, likely guest or handled by socialite)
Route::get('/callback', [SenhaunicaController::class, 'handleProviderCallback'])
    ->middleware('web') // Ensure session middleware is applied if not global
    ->name('senhaunica.callback.proxy'); // Name remains from diff

// Email Verification Notice Route (Used after registration)
Route::view('/confirm-registration', 'auth.confirm-notice')->middleware('auth')->name('auth.confirm-notice');

// Include Breeze Auth Routes (Login, Register, Password Reset, Email Verification)
require __DIR__.'/auth.php';