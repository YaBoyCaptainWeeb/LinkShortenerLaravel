<?php

use App\Http\Controllers\LinkRedirectController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect('/panel/links');
    }
    return view('welcome');
})->name('home');
Route::get('/panel', function () {
    if (auth()->check()) {
        return redirect('/panel/links');
    }
    return view('welcome');
})->name('home');

Route::get('/{code}', LinkRedirectController::class)
    ->where('code', '^(?!panel|links|livewire)[A-Za-z0-9]+$')
    ->name('link.redirect');
