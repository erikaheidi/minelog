<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\WorldController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/w/{world:slug}', [WorldController::class, 'show'])->name('worlds.public');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::livewire('worlds', 'pages::worlds.index')->name('worlds.index');
    Route::livewire('worlds/{world}', 'pages::worlds.show')->name('worlds.show');
});

require __DIR__.'/settings.php';
