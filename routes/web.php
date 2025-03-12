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
Route::delete('/currency/delete/{id}', [CurrencyList::class, 'delete'])->name('currency.delete');




Route::get('/payroll_processing', [ClosePayPeriod::class, 'index'])->name('payroll_processing');
Route::get('/payroll_action', [ClosePayPeriod::class, 'action'])->name('payroll_action');
Route::get('/payroll_generate_pay_stubs', [ClosePayPeriod::class, 'generate_pay_stubs'])->name('generate_pay_stubs');


// ===============================================================================================================================
// Progress Bar Functions
// ===============================================================================================================================
Route::get('/progress_bar', [ProgressBar::class, 'index'])->name('progress_bar');
Route::get('/progress_bar/recalculate_employee', [ProgressBar::class, 'recalculate_employee'])->name('progress_bar.recalculate_employee');
Route::get('/progress_bar/generate_paystubs', [ProgressBar::class, 'generate_paystubs'])->name('progress_bar.generate_paystubs');
Route::get('/progress_bar/generate_paymiddle', [ProgressBar::class, 'generate_paymiddle'])->name('progress_bar.generate_paymiddle');
Route::get('/progress_bar/recalculate_paystub_ytd', [ProgressBar::class, 'recalculate_paystub_ytd'])->name('progress_bar.recalculate_paystub_ytd');
Route::get('/progress_bar/add_mass_punch', [ProgressBar::class, 'add_mass_punch'])->name('progress_bar.add_mass_punch');
Route::get('/progress_bar/add_mass_schedule', [ProgressBar::class, 'add_mass_schedule'])->name('progress_bar.add_mass_schedule');
Route::get('/progress_bar/add_mass_schedule_npvc', [ProgressBar::class, 'add_mass_schedule_npvc'])->name('progress_bar.add_mass_schedule_npvc');
Route::get('/progress_bar/recalculate_accrual_policy', [ProgressBar::class, 'recalculate_accrual_policy'])->name('progress_bar.recalculate_accrual_policy');
Route::get('/progress_bar/process_late_leave', [ProgressBar::class, 'process_late_leave'])->name('progress_bar.process_late_leave');
Route::get('/progress_bar/generate_december_bonuses', [ProgressBar::class, 'generate_december_bonuses'])->name('progress_bar.generate_december_bonuses');
Route::get('/progress_bar/generate_attendance_bonuses', [ProgressBar::class, 'generate_attendance_bonuses'])->name('progress_bar.generate_attendance_bonuses');
// ===============================================================================================================================



