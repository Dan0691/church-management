<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/


// SPA Routes - Vue.js handles these
Route::get('/login', function () {
    return view('app');
})->name('login');

Route::get('/register', function () {
    return view('app');
})->name('register');

Route::get('/', function () {
    return view('app');
})->name('home');

Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');

