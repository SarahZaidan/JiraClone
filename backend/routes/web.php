<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;


Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('auth/{provider}', [AuthController::class, 'redirectToGoogle']);
Route::get('auth/{provider}/callback', [AuthController::class, 'handleGoogleCallback']);
