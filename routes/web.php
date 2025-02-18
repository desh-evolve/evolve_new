<?php

use App\Http\Controllers\currency\CurrencyList;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/currency', [CurrencyList::class, 'index'])->name('currency.index');