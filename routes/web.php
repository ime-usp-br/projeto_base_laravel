<?php
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\UspLocalPasswordController;
use Uspdev\SenhaunicaSocialite\Http\Controllers\SenhaunicaController;

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users/create/usp', [AdminUserController::class, 'createUsp'])->name('users.create.usp');
    Route::post('/users/create/usp', [AdminUserController::class, 'storeUsp'])->name('users.store.usp');
    Route::get('/users/create/manual', [AdminUserController::class, 'createManual'])->name('users.create.manual');
    Route::post('/users/create/manual', [AdminUserController::class, 'storeManual'])->name('users.store.manual');
});

Route::get('/request-local-password', [UspLocalPasswordController::class, 'showRequestForm'])->name('local-password.request');
Route::post('/request-local-password', [UspLocalPasswordController::class, 'sendLink']);
Route::get('/set-local-password', [UspLocalPasswordController::class, 'showSetForm'])->middleware('signed')->name('local-password.set');
Route::post('/set-local-password', [UspLocalPasswordController::class, 'setPassword']);

Route::get('/', function () {
    return view('welcome');
});

Route::get('/callback', [SenhaunicaController::class, 'handleProviderCallback'])
    ->middleware('web')
    ->name('senhaunica.callback.proxy');

Route::view('/confirm-registration', 'auth.confirm-notice')->name('auth.confirm-notice');


require __DIR__.'/auth.php';