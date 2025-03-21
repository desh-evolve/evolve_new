<?php

use App\Http\Controllers\payperiod\ClosePayPeriod;
use App\Http\Controllers\currency\CurrencyList;
use App\Http\Controllers\currency\EditCurrency;
use App\Http\Controllers\Branch\BranchList;
use App\Http\Controllers\Branch\EditBranch;
use App\Http\Controllers\Branch\BranchBankAccountList;
use App\Http\Controllers\Branch\EditBankAccount;
use App\Http\Controllers\Company\EditCompanyNew;
use App\Http\Controllers\Dashboard;
use App\Http\Controllers\Login;
use App\Http\Controllers\progressbar\ProgressBar;
use App\Http\Controllers\users\UserGenericStatusList;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/login', [Login::class, 'index'])->name('login');
Route::get('/logout', [Login::class, 'index'])->name('logout');
Route::post('/authenticate', [Login::class, 'login'])->name('authenticate');

Route::get('/dashboard', [Dashboard::class, 'index'])->name('dashboard');

// ==================== currency =====================================================================================
Route::get('/currency', [CurrencyList::class, 'index'])->name('currency.index');

Route::get('/currency/add/{id?}', [EditCurrency::class, 'index'])->name('currency.add');
Route::post('/currency/save/{id?}', [EditCurrency::class, 'save'])->name('currency.save');
Route::delete('/currency/delete/{id}', [CurrencyList::class, 'delete'])->name('currency.delete');

// ==================== branch =====================================================================================
Route::get('/branch', [BranchList::class, 'index'])->name('branch.index');

Route::get('/branch/add/{id?}', [EditBranch::class, 'index'])->name('branch.add');

Route::post('/branch/save/{id?}', [EditBranch::class, 'save'])->name('branch.save');
Route::delete('/branch/delete/{id}', [BranchList::class, 'delete'])->name('branch.delete');

// ==================== branch bank account =====================================================================================
Route::get('/branch_bank/{id?}', [BranchBankAccountList::class, 'index'])->name('branch_bank.index');

Route::get('/branch_bank_account/add/{id?}', [EditBankAccount::class, 'index'])->name('branch_bank_account.add');
Route::get('/branch_bank/add/{id?}', [EditBankAccount::class, 'index'])->name('branch_bank.add');
Route::post('/branch_bank/save/{id?}', [EditBankAccount::class, 'save'])->name('branch_bank.save');
Route::delete('/branch_bank/delete/{id}', [EditBankAccount::class, 'delete'])->name('branch_bank.delete');




Route::get('/payroll/payroll_processing', [ClosePayPeriod::class, 'index'])->name('payroll.payroll_processing');
Route::get('/payroll/payroll_action', [ClosePayPeriod::class, 'action'])->name('payroll.payroll_action');
Route::get('/payroll/payroll_generate_pay_stubs', [ClosePayPeriod::class, 'generate_pay_stubs'])->name('payroll.generate_pay_stubs');


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



// ===============================================================================================================================
// Users Functions
// ===============================================================================================================================
Route::get('/users/user_generic_status_list', [UserGenericStatusList::class, 'index'])->name('users.user_generic_status_list');
// ===============================================================================================================================


// ===============================================================================================================================
// Compnay Information
// ===============================================================================================================================
Route::get('/company/company_information', [EditCompanyNew::class, 'index'])->name('company.index');
