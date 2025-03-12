<?php

use App\Http\Controllers\payperiod\ClosePayPeriod;
use App\Http\Controllers\currency\CurrencyList;
use App\Http\Controllers\currency\EditCurrency;
use App\Http\Controllers\Dashboard;
use App\Http\Controllers\Login;
use App\Http\Controllers\progressbar\ProgressBar;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/login', [Login::class, 'index'])->name('login');
Route::get('/logout', [Login::class, 'index'])->name('logout');
Route::post('/authenticate', [Login::class, 'login'])->name('authenticate');

Route::get('/dashboard', [Dashboard::class, 'index'])->name('dashboard');

Route::get('/currency', [CurrencyList::class, 'index'])->name('currency.index');

Route::get('/currency/add/{id?}', [EditCurrency::class, 'index'])->name('currency.add');
Route::post('/currency/save/{id?}', [EditCurrency::class, 'save'])->name('currency.save');
// Route::delete('/currency/delete/{id?}', [CurrencyList::class, 'delete'])->name('currency.delete');
Route::delete('/currency/delete/{id}', [CurrencyList::class, 'delete'])->name('currency.delete');




Route::get('/payroll_processing', [ClosePayPeriod::class, 'index'])->name('payroll_processing');
Route::get('/payroll_action', [ClosePayPeriod::class, 'action'])->name('payroll_action');
Route::get('/payroll_generate_pay_stubs', [ClosePayPeriod::class, 'generate_pay_stubs'])->name('generate_pay_stubs');


Route::get('/payroll/progress_bar', [ProgressBar::class, 'index'])->name('payroll.progress_bar');


