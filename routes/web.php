<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\DomainController;
use App\Http\Controllers\HostingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

Route::middleware('auth')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // ============ DASHBOARD ============
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ============ PROFILE ============
    Route::get('/profile',          [ProfileController::class, 'show'])          ->name('profile.show');
    Route::put('/profile',          [ProfileController::class, 'update'])        ->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.update-password');
    
    // ============ DOMAINS ============
    Route::get('/domains',               [DomainController::class, 'index'])  ->name('domains.index');
    Route::post('/domains',              [DomainController::class, 'store'])  ->name('domains.store');
    Route::put('/domains/{domain}',      [DomainController::class, 'update']) ->name('domains.update');
    Route::delete('/domains/{domain}',   [DomainController::class, 'destroy'])->name('domains.destroy');

    // ============ HOSTING ============
    Route::get('/hosting',               [HostingController::class, 'index'])  ->name('hosting.index');
    Route::post('/hosting',              [HostingController::class, 'store'])  ->name('hosting.store');
    Route::put('/hosting/{hosting}',     [HostingController::class, 'update']) ->name('hosting.update');
    Route::delete('/hosting/{hosting}',  [HostingController::class, 'destroy'])->name('hosting.destroy');

    // ============ ADMIN-ONLY ROUTES ============
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/users',                        [UserController::class, 'users'])->name('users.index');
        Route::post('/users',                       [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}',                 [UserController::class, 'update'])->name('users.update');
        Route::post('/users/{user}/toggle-active',  [UserController::class, 'toggleActive'])->name('users.toggle-active');
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
    });

});

Route::get('/', function () {
    return redirect()->route('dashboard');
});