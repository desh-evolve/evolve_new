<?php

use App\Http\Controllers\currency\CurrencyList;
use App\Http\Controllers\Dashboard;
use App\Http\Controllers\Login;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/login', [Login::class, 'index'])->name('login');
Route::get('/logout', [Login::class, 'index'])->name('logout');
Route::post('/authenticate', [Login::class, 'login'])->name('authenticate');

Route::get('/dashboard', [Dashboard::class, 'index'])->name('dashboard');

Route::get('/currency', [CurrencyList::class, 'index'])->name('currency.index');