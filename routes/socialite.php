<?php

use Illuminate\Support\Facades\Route;
use Uspdev\SenhaunicaSocialite\Http\Controllers\SenhaunicaController;
use Uspdev\SenhaunicaSocialite\Http\Controllers\UserController;

Route::get('/socialite/login', [SenhaunicaController::class, 'redirectToProvider'])
    ->name('senhaunica.login');

Route::get('/socialite/callback', [SenhaunicaController::class, 'handleProviderCallback'])
    ->name('senhaunica.callback');

Route::post('/socialite/logout', [SenhaunicaController::class, 'logout'])
     ->middleware('auth')
     ->name('senhaunica.logout');

Route::get('loginas', [UserController::class, 'loginAsForm'])->name('SenhaunicaLoginAsForm');
Route::post('loginas', [UserController::class, 'loginAs'])->name('SenhaunicaLoginAs');
Route::get('undologinas', [UserController::class, 'undoLoginAs'])->name('SenhaunicaUndoLoginAs');

if (config('senhaunica.userRoutes')) {
    Route::get(config('senhaunica.userRoutes') . '/find', [UserController::class, 'find'])->name('SenhaunicaFindUsers');
    Route::get(config('senhaunica.userRoutes') . '/{id}/jsonModalContent', [UserController::class, 'getJsonModalContent'])->name('SenhaunicaGetJsonModalContent');
    Route::post(config('senhaunica.userRoutes') . '/{id}/updatePermission', [UserController::class, 'updatePermission'])->name('SenhaunicaUpdatePermission');
    Route::resource(config('senhaunica.userRoutes'), UserController::class);
}