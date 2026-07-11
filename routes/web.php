<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\WorldController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::view('/how-it-works', 'how-it-works')->name('how-it-works');
Route::get('/w/{world:slug}', [WorldController::class, 'show'])->name('worlds.public');
Route::get('/w/{world:slug}/map', [WorldController::class, 'map'])->name('worlds.public.map');

Route::middleware('guest')->group(function () {
    Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('google.redirect');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('google.callback');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::livewire('worlds', 'pages::worlds.index')->name('worlds.index');
    Route::livewire('worlds/{world}', 'pages::worlds.show')->name('worlds.show');
    Route::livewire('worlds/{world}/map', 'pages::worlds.map')->name('worlds.map');
});

require __DIR__.'/settings.php';
