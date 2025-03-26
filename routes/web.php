<?php

use App\Http\Controllers\payperiod\ClosePayPeriod;
use App\Http\Controllers\currency\CurrencyList;
use App\Http\Controllers\currency\EditCurrency;
use App\Http\Controllers\Branch\BranchList;
use App\Http\Controllers\Branch\EditBranch;
use App\Http\Controllers\Branch\BranchBankAccountList;
use App\Http\Controllers\Branch\EditBankAccount;
use App\Http\Controllers\company\EditCompany;
use App\Http\Controllers\Company\EditCompanyNew;
use App\Http\Controllers\company\WageGroupList;
use App\Http\Controllers\company\EditWageGroup;
use App\Http\Controllers\department\DepartmentList;
use App\Http\Controllers\department\EditDepartment;
use App\Http\Controllers\department\EditDepartmentBranchUser;

use App\Http\Controllers\Dashboard;
use App\Http\Controllers\Login;
use App\Http\Controllers\policy\AbsencePolicyList;
use App\Http\Controllers\policy\AccrualPolicyList;
use App\Http\Controllers\policy\BreakPolicyList;
use App\Http\Controllers\policy\EditAbsencePolicy;
use App\Http\Controllers\policy\EditAccrualPolicy;
use App\Http\Controllers\policy\EditBreakPolicy;
use App\Http\Controllers\policy\EditExceptionPolicyControl;
use App\Http\Controllers\policy\EditHolidayPolicy;
use App\Http\Controllers\policy\EditMealPolicy;
use App\Http\Controllers\policy\EditOverTimePolicy;
use App\Http\Controllers\policy\EditPolicyGroup;
use App\Http\Controllers\policy\EditPremiumPolicy;
use App\Http\Controllers\policy\EditRoundIntervalPolicy;
use App\Http\Controllers\policy\EditSchedulePolicy;
use App\Http\Controllers\policy\ExceptionPolicyControlList;
use App\Http\Controllers\policy\HolidayPolicyList;
use App\Http\Controllers\policy\MealPolicyList;
use App\Http\Controllers\policy\OverTimePolicyList;
use App\Http\Controllers\policy\PolicyGroupList;
use App\Http\Controllers\policy\PremiumPolicyList;
use App\Http\Controllers\policy\RoundIntervalPolicyList;
use App\Http\Controllers\policy\SchedulePolicyList;
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

// ==================== wage group =====================================================================================
Route::get('/wage_group', [WageGroupList::class, 'index'])->name('wage_group.index');

Route::get('/wage_group/add/{id?}', [EditWageGroup::class, 'index'])->name('wage_group.add');
Route::post('/wage_group/save/{id?}', [EditWageGroup::class, 'save'])->name('wage_group.save');
Route::delete('/wage_group/delete/{id}', [WageGroupList::class, 'delete'])->name('wage_group.delete');

// ==================== Department =====================================================================================
Route::get('/department', [DepartmentList::class, 'index'])->name('department.index');

Route::get('/department/add/{id?}', [EditDepartment::class, 'index'])->name('department.add');
Route::post('/department/save/{id?}', [EditDepartment::class, 'submit'])->name('department.save');
Route::delete('/department/delete/{id}', [DepartmentList::class, 'delete'])->name('department.delete');

Route::get('/department_branch_user/{id?}', [EditDepartmentBranchUser::class, 'index'])->name('department_branch_user.index');
Route::post('/department_branch_user/save/{id?}', [EditDepartmentBranchUser::class, 'submit'])->name('department_branch_user.save');


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
Route::get('/company/company_information', [EditCompany::class, 'index'])->name('company.index');


// ===============================================================================================================================
// Policies 
// ===============================================================================================================================

Route::get('/policy/policy_groups', [PolicyGroupList::class, 'index'])->name('policy.policy_groups');
Route::get('/policy/policy_groups/add/{id?}', [EditPolicyGroup::class, 'index'])->name('policy.policy_groups.add');
Route::post('/policy/policy_groups/submit/{id?}', [EditPolicyGroup::class, 'submit'])->name('policy.policy_groups.submit');
Route::delete('/policy/policy_groups/delete/{id}', [PolicyGroupList::class, 'delete'])->name('policy.policy_groups.delete');

Route::get('/policy/absence_policies', [AbsencePolicyList::class, 'index'])->name('policy.absence_policies');
Route::get('/policy/absence_policies/add/{id?}', [EditAbsencePolicy::class, 'index'])->name('policy.absence_policies.add');
Route::post('/policy/absence_policies/submit/{id?}', [EditAbsencePolicy::class, 'submit'])->name('policy.absence_policies.submit');
Route::delete('/policy/absence_policies/delete/{id}', [AbsencePolicyList::class, 'delete'])->name('policy.absence_policies.delete');

Route::get('/policy/accrual_policies', [AccrualPolicyList::class, 'index'])->name('policy.accrual_policies');
Route::get('/policy/accrual_policies/add/{id?}', [EditAccrualPolicy::class, 'index'])->name('policy.accrual_policies.add');
Route::post('/policy/accrual_policies/submit/{id?}', [EditAccrualPolicy::class, 'submit'])->name('policy.accrual_policies.submit');
Route::delete('/policy/accrual_policies/delete/{id}', [AccrualPolicyList::class, 'delete'])->name('policy.accrual_policies.delete');

Route::get('/policy/schedule_policies', [SchedulePolicyList::class, 'index'])->name('policy.schedule_policies');
Route::get('/policy/schedule_policies/add/{id?}', [EditSchedulePolicy::class, 'index'])->name('policy.schedule_policies.add');
Route::post('/policy/schedule_policies/submit/{id?}', [EditSchedulePolicy::class, 'submit'])->name('policy.schedule_policies.submit');
Route::delete('/policy/schedule_policies/delete/{id}', [SchedulePolicyList::class, 'delete'])->name('policy.schedule_policies.delete');

Route::get('/policy/rounding_policies', [RoundIntervalPolicyList::class, 'index'])->name('policy.rounding_policies');
Route::get('/policy/rounding_policies/add/{id?}', [EditRoundIntervalPolicy::class, 'index'])->name('policy.rounding_policies.add');
Route::post('/policy/rounding_policies/submit/{id?}', [EditRoundIntervalPolicy::class, 'submit'])->name('policy.rounding_policies.submit');
Route::delete('/policy/rounding_policies/delete/{id}', [RoundIntervalPolicyList::class, 'delete'])->name('policy.rounding_policies.delete');

Route::get('/policy/meal_policies', [MealPolicyList::class, 'index'])->name('policy.meal_policies');
Route::get('/policy/meal_policies/add/{id?}', [EditMealPolicy::class, 'index'])->name('policy.meal_policies.add');
Route::post('/policy/meal_policies/submit/{id?}', [EditMealPolicy::class, 'submit'])->name('policy.meal_policies.submit');
Route::delete('/policy/meal_policies/delete/{id}', [MealPolicyList::class, 'delete'])->name('policy.meal_policies.delete');

Route::get('/policy/break_policies', [BreakPolicyList::class, 'index'])->name('policy.break_policies');
Route::get('/policy/break_policies/add/{id?}', [EditBreakPolicy::class, 'index'])->name('policy.break_policies.add');
Route::post('/policy/break_policies/submit/{id?}', [EditBreakPolicy::class, 'submit'])->name('policy.break_policies.submit');
Route::delete('/policy/break_policies/delete/{id}', [BreakPolicyList::class, 'delete'])->name('policy.break_policies.delete');

Route::get('/policy/overtime_policies', [OverTimePolicyList::class, 'index'])->name('policy.overtime_policies');
Route::get('/policy/overtime_policies/add/{id?}', [EditOverTimePolicy::class, 'index'])->name('policy.overtime_policies.add');
Route::post('/policy/overtime_policies/submit/{id?}', [EditOverTimePolicy::class, 'submit'])->name('policy.overtime_policies.submit');
Route::delete('/policy/overtime_policies/delete/{id}', [OverTimePolicyList::class, 'delete'])->name('policy.overtime_policies.delete');

Route::get('/policy/premium_policies', [PremiumPolicyList::class, 'index'])->name('policy.premium_policies');
Route::get('/policy/premium_policies/add/{id?}', [EditPremiumPolicy::class, 'index'])->name('policy.premium_policies.add');
Route::post('/policy/premium_policies/submit/{id?}', [EditPremiumPolicy::class, 'submit'])->name('policy.premium_policies.submit');
Route::delete('/policy/premium_policies/delete/{id}', [PremiumPolicyList::class, 'delete'])->name('policy.premium_policies.delete');

Route::get('/policy/exception_policies', [ExceptionPolicyControlList::class, 'index'])->name('policy.exception_policies');
Route::get('/policy/exception_policies/add/{id?}', [EditExceptionPolicyControl::class, 'index'])->name('policy.exception_policies.add');
Route::post('/policy/exception_policies/submit/{id?}', [EditExceptionPolicyControl::class, 'submit'])->name('policy.exception_policies.submit');
Route::delete('/policy/exception_policies/delete/{id}', [ExceptionPolicyControlList::class, 'delete'])->name('policy.exception_policies.delete');

Route::get('/policy/holiday_policies', [HolidayPolicyList::class, 'index'])->name('policy.holiday_policies');
Route::get('/policy/holiday_policies/add/{id?}', [EditHolidayPolicy::class, 'index'])->name('policy.holiday_policies.add');
Route::post('/policy/holiday_policies/submit/{id?}', [EditHolidayPolicy::class, 'submit'])->name('policy.holiday_policies.submit');
Route::delete('/policy/holiday_policies/delete/{id}', [HolidayPolicyList::class, 'delete'])->name('policy.holiday_policies.delete');


// ===============================================================================================================================
