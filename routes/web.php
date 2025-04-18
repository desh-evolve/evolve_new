<?php

use App\Http\Controllers\accrual\EditUserAccrual;
use App\Http\Controllers\accrual\UserAccrualBalanceList;
use App\Http\Controllers\accrual\ViewUserAccrualList;
use App\Http\Controllers\company\CompanyDeductionList;
use App\Http\Controllers\payperiod\ClosePayPeriod;
use App\Http\Controllers\currency\CurrencyList;
use App\Http\Controllers\currency\EditCurrency;
use App\Http\Controllers\Branch\BranchList;
use App\Http\Controllers\Branch\EditBranch;
use App\Http\Controllers\Branch\BranchBankAccountList;
use App\Http\Controllers\Branch\EditBankAccount;
use App\Http\Controllers\company\EditCompany;
use App\Http\Controllers\company\EditCompanyDeduction;
use App\Http\Controllers\company\WageGroupList;
use App\Http\Controllers\company\EditWageGroup;
use App\Http\Controllers\department\DepartmentList;
use App\Http\Controllers\department\EditDepartment;
use App\Http\Controllers\department\EditDepartmentBranchUser;
use App\Http\Controllers\users\EditUserGroup;
use App\Http\Controllers\users\UserGroupList;
use App\Http\Controllers\users\EditUserTitle;
use App\Http\Controllers\users\UserTitleList;
use App\Http\Controllers\station\EditStation;
use App\Http\Controllers\station\StationList;
use App\Http\Controllers\permission\PermissionControlList;
use App\Http\Controllers\permission\EditPermissionControl;
use App\Http\Controllers\policy\RecurringHolidayList;
use App\Http\Controllers\policy\EditRecurringHoliday;

use App\Http\Controllers\Dashboard;
use App\Http\Controllers\Login;
use App\Http\Controllers\pay_stub\EditPayStubEntryAccount;
use App\Http\Controllers\pay_stub\EditPayStubEntryAccountLink;
use App\Http\Controllers\pay_stub\PayStubEntryAccountList;
use App\Http\Controllers\pay_stub\PayStubList;
use App\Http\Controllers\pay_stub_amendment\EditPayStubAmendment;
use App\Http\Controllers\pay_stub_amendment\EditRecurringPayStubAmendment;
use App\Http\Controllers\pay_stub_amendment\PayStubAmendmentList;
use App\Http\Controllers\pay_stub_amendment\RecurringPayStubAmendmentList;
use App\Http\Controllers\payperiod\EditPayPeriod;
use App\Http\Controllers\payperiod\EditPayPeriodSchedule;
use App\Http\Controllers\payperiod\PayPeriodList;
use App\Http\Controllers\payperiod\PayPeriodScheduleList;
use App\Http\Controllers\payperiod\ViewPayPeriod;
use App\Http\Controllers\policy\AbsencePolicyList;
use App\Http\Controllers\policy\AccrualPolicyList;
use App\Http\Controllers\policy\BreakPolicyList;
use App\Http\Controllers\policy\EditAbsencePolicy;
use App\Http\Controllers\policy\EditAccrualPolicy;
use App\Http\Controllers\policy\EditBreakPolicy;
use App\Http\Controllers\policy\EditExceptionPolicyControl;
use App\Http\Controllers\policy\EditHoliday;
use App\Http\Controllers\policy\EditHolidayPolicy;
use App\Http\Controllers\policy\EditMealPolicy;
use App\Http\Controllers\policy\EditOverTimePolicy;
use App\Http\Controllers\policy\EditPolicyGroup;
use App\Http\Controllers\policy\EditPremiumPolicy;
use App\Http\Controllers\policy\EditRoundIntervalPolicy;
use App\Http\Controllers\policy\EditSchedulePolicy;
use App\Http\Controllers\policy\ExceptionPolicyControlList;
use App\Http\Controllers\policy\HolidayList;
use App\Http\Controllers\policy\HolidayPolicyList;
use App\Http\Controllers\policy\MealPolicyList;
use App\Http\Controllers\policy\OverTimePolicyList;
use App\Http\Controllers\policy\PolicyGroupList;
use App\Http\Controllers\policy\PremiumPolicyList;
use App\Http\Controllers\policy\RoundIntervalPolicyList;
use App\Http\Controllers\policy\SchedulePolicyList;
use App\Http\Controllers\progressbar\ProgressBar;
use App\Http\Controllers\punch\AddMassPunch;
use App\Http\Controllers\punch\EditPunch;
use App\Http\Controllers\punch\PunchList;
use App\Http\Controllers\request\EditRequest;
use App\Http\Controllers\request\UserRequestList;
use App\Http\Controllers\users\EditUser;
use App\Http\Controllers\users\UserGenericStatusList;
use App\Http\Controllers\users\UserList;
use Illuminate\Support\Facades\Route;


use App\Http\Controllers\report\UserInformation;

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


// ==================== wage group =====================================================================================
Route::get('/user_group', [UserGroupList::class, 'index'])->name('user_group.index');

Route::get('/user_group/add/{id?}', [EditUserGroup::class, 'index'])->name('user_group.add');
Route::post('/user_group/save/{id?}', [EditUserGroup::class, 'submit'])->name('user_group.save');
Route::delete('/user_group/delete/{id}', [UserGroupList::class, 'delete'])->name('user_group.delete');

// ==================== user title =====================================================================================
Route::get('/user_title', [UserTitleList::class, 'index'])->name('user_title.index');

Route::get('/user_title/add/{id?}', [EditUserTitle::class, 'index'])->name('user_title.add');
Route::post('/user_title/save/{id?}', [EditUserTitle::class, 'submit'])->name('user_title.save');
Route::delete('/user_title/delete/{id}', [UserTitleList::class, 'delete'])->name('user_title.delete');

// ==================== station =====================================================================================
Route::get('/station', [StationList::class, 'index'])->name('station.index');

Route::get('/station/add/{id?}', [EditStation::class, 'index'])->name('station.add');
Route::post('/station/save/{id?}', [EditStation::class, 'submit'])->name('station.save');
Route::delete('/station/delete/{id}', [StationList::class, 'delete'])->name('station.delete');
// ==================== Permission Control =====================================================================================
Route::get('/permission_control', [PermissionControlList::class, 'index'])->name('permission_control.index');

Route::get('/permission_control/add/{id?}', [EditPermissionControl::class, 'index'])->name('permission_control.add');
Route::post('/permission_control/save/{id?}', [EditPermissionControl::class, 'submit'])->name('permission_control.save');
Route::delete('/permission_control/delete/{id}', [PermissionControlList::class, 'delete'])->name('permission_control.delete');
// ==================== Permission Control =====================================================================================
Route::get('/recurring_holidays', [RecurringHolidayList::class, 'index'])->name('recurring_holidays.index');

Route::get('/recurring_holidays/add/{id?}', [EditRecurringHoliday::class, 'index'])->name('recurring_holidays.add');
Route::post('/recurring_holidays/save/{id?}', [EditRecurringHoliday::class, 'submit'])->name('recurring_holidays.save');
Route::delete('/recurring_holidays/delete/{id}', [RecurringHolidayList::class, 'delete'])->name('recurring_holidays.delete');


// ===============================================================================================================================
// report 
// ===============================================================================================================================
Route::get('/employee_detail', [UserInformation::class, 'index'])->name('employee_detail.index');
Route::get('/employee_detail/report', [UserInformation::class, 'generate'])->name('employee_detail.report');


// ===============================================================================================================================
// Payroll 
// ===============================================================================================================================

Route::get('/payroll/payroll_processing', [ClosePayPeriod::class, 'index'])->name('payroll.payroll_processing');
Route::get('/payroll/payroll_action', [ClosePayPeriod::class, 'action'])->name('payroll.payroll_action');
Route::get('/payroll/payroll_generate_pay_stubs', [ClosePayPeriod::class, 'generate_pay_stubs'])->name('payroll.generate_pay_stubs');

Route::get('/payroll/pay_stub_amendment', [PayStubAmendmentList::class, 'index'])->name('payroll.pay_stub_amendment');
Route::get('/payroll/pay_stub_amendment/add/{id?}', [EditPayStubAmendment::class, 'index'])->name('payroll.pay_stub_amendment.add');
Route::post('/payroll/pay_stub_amendment/submit/{id?}', [EditPayStubAmendment::class, 'submit'])->name('payroll.pay_stub_amendment.submit');
Route::delete('/payroll/pay_stub_amendment/delete/{id}', [PayStubAmendmentList::class, 'delete'])->name('payroll.pay_stub_amendment.delete');

Route::get('/payroll/recurring_pay_stub_amendment', [RecurringPayStubAmendmentList::class, 'index'])->name('payroll.recurring_pay_stub_amendment');
Route::get('/payroll/recurring_pay_stub_amendment/add/{id?}', [EditRecurringPayStubAmendment::class, 'index'])->name('payroll.recurring_pay_stub_amendment.add');
Route::post('/payroll/recurring_pay_stub_amendment/submit/{id?}', [EditRecurringPayStubAmendment::class, 'submit'])->name('payroll.recurring_pay_stub_amendment.submit');
Route::get('/payroll/recurring_pay_stub_amendment/recalculate/{id}', [EditRecurringPayStubAmendment::class, 'recalculate'])->name('payroll.recurring_pay_stub_amendment.recalculate');
Route::delete('/payroll/recurring_pay_stub_amendment/delete/{id}', [RecurringPayStubAmendmentList::class, 'delete'])->name('payroll.recurring_pay_stub_amendment.delete');

Route::get('/payroll/pay_period_schedules', [PayPeriodScheduleList::class, 'index'])->name('payroll.pay_period_schedules');
Route::get('/payroll/pay_period_schedules/add/{id?}', [EditPayPeriodSchedule::class, 'index'])->name('payroll.pay_period_schedules.add');
Route::post('/payroll/pay_period_schedules/submit/{id?}', [EditPayPeriodSchedule::class, 'submit'])->name('payroll.pay_period_schedules.submit');
Route::delete('/payroll/pay_period_schedules/delete/{id}', [PayPeriodScheduleList::class, 'delete'])->name('payroll.pay_period_schedules.delete');

Route::get('/payroll/pay_periods/{pay_period_schedule_id}', [PayPeriodList::class, 'index'])->name('payroll.pay_periods');
Route::get('/payroll/pay_periods/add/{pay_period_schedule_id}/{id?}', [EditPayPeriod::class, 'index'])->name('payroll.pay_periods.add');
Route::post('/payroll/pay_periods/submit/{pay_period_schedule_id}/{id?}', [EditPayPeriod::class, 'submit'])->name('payroll.pay_periods.submit');
Route::delete('/payroll/pay_periods/delete/{id}', [PayPeriodList::class, 'delete'])->name('payroll.pay_periods.delete');

Route::get('/payroll/pay_periods/view/{pay_period_id}', [ViewPayPeriod::class, 'index'])->name('payroll.pay_periods.view');
Route::post('/payroll/pay_periods_view/submit', [ViewPayPeriod::class, 'submit'])->name('payroll.pay_periods_view.submit');
Route::get('/payroll/pay_periods_view/generate_paystubs/{pay_period_id}', [ViewPayPeriod::class, 'generate_paystubs'])->name('payroll.pay_periods_view.generate_paystubs');
Route::get('/payroll/pay_periods_view/import/{pay_period_id}', [ViewPayPeriod::class, 'import'])->name('payroll.pay_periods_view.import');
Route::get('/payroll/pay_periods_view/delete_data/{pay_period_id}', [ViewPayPeriod::class, 'delete_data'])->name('payroll.pay_periods_view.delete_data');

Route::get('/payroll/paystub_accounts', [PayStubEntryAccountList::class, 'index'])->name('payroll.paystub_accounts');
Route::get('/payroll/paystub_accounts/add/{id?}', [EditPayStubEntryAccount::class, 'index'])->name('payroll.paystub_accounts.add');
Route::post('/payroll/paystub_accounts/submit/{id?}', [EditPayStubEntryAccount::class, 'submit'])->name('payroll.paystub_accounts.submit');
Route::delete('/payroll/paystub_accounts/delete/{id}', [PayStubEntryAccountList::class, 'delete'])->name('payroll.paystub_accounts.delete');

Route::get('/payroll/paystub_account_link', [EditPayStubEntryAccountLink::class, 'index'])->name('payroll.paystub_account_link');
Route::post('/payroll/paystub_account_link/submit', [EditPayStubEntryAccountLink::class, 'submit'])->name('payroll.paystub_account_link.submit');

Route::get('/payroll/company_deductions', [CompanyDeductionList::class, 'index'])->name('payroll.company_deductions');
Route::get('/payroll/company_deductions/add/{id?}', [EditCompanyDeduction::class, 'index'])->name('payroll.company_deductions.add');
Route::post('/payroll/company_deductions/submit/{id?}', [EditCompanyDeduction::class, 'submit'])->name('payroll.company_deductions.submit');
Route::delete('/payroll/company_deductions/delete/{id}', [CompanyDeductionList::class, 'delete'])->name('payroll.company_deductions.delete');

// ===============================================================================================================================
// Progress Bar Functions
// ===============================================================================================================================
Route::get('/progress_bar_control', [ProgressBar::class, 'index'])->name('progress_bar_control');
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

Route::get('/policy/holidays/{id}', [HolidayList::class, 'index'])->name('policy.holidays');
Route::get('/policy/holidays/add/{holiday_policy_id}/{id?}', [EditHoliday::class, 'index'])->name('policy.holidays.add');
Route::post('/policy/holidays/submit/{id?}', [EditHoliday::class, 'submit'])->name('policy.holidays.submit');
Route::delete('/policy/holidays/delete/{id}', [HolidayList::class, 'delete'])->name('policy.holidays.delete');


// ===============================================================================================================================
// user functions
// ===============================================================================================================================

Route::get('/admin/userlist', [UserList::class, 'index'])->name('admin.userlist');
Route::get('/admin/userlist/add/{id?}', [EditUser::class, 'index'])->name('admin.userlist.add');
Route::post('/admin/userlist/submit/{id?}', [EditUser::class, 'submit'])->name('admin.userlist.submit');
Route::delete('/admin/userlist/delete/{id}', [UserList::class, 'delete'])->name('admin.userlist.delete');

// ===============================================================================================================================
// attendance functions
// ===============================================================================================================================

Route::get('/attendance/punchlist', [PunchList::class, 'index'])->name('attendance.punchlist');
Route::get('/attendance/punch/add', [EditPunch::class, 'index'])->name('attendance.punch.add');
Route::post('/attendance/punch/submit/{id?}', [EditPunch::class, 'submit'])->name('attendance.punch.submit');
Route::delete('/attendance/punch/delete/{id}', [PunchList::class, 'delete'])->name('attendance.punch.delete');
Route::delete('/attendance/punch_single/delete/{id}', [EditPunch::class, 'delete'])->name('attendance.punch_single.delete');

Route::get('/attendance/masspunch/add', [AddMassPunch::class, 'index'])->name('attendance.masspunch.add');
Route::post('/attendance/masspunch/submit', [AddMassPunch::class, 'submit'])->name('attendance.masspunch.submit');

Route::get('/attendance/requests', [UserRequestList::class, 'index'])->name('attendance.requests');
Route::get('/attendance/request/add/{id?}', [EditRequest::class, 'index'])->name('attendance.request.add');
Route::post('/attendance/request/submit/{id?}', [EditRequest::class, 'submit'])->name('attendance.request.submit');
Route::delete('/attendance/request/delete/{id}', [UserRequestList::class, 'delete'])->name('attendance.request.delete');

Route::get('/attendance/accruals/{filter_user_id?}', [UserAccrualBalanceList::class, 'index'])->name('attendance.accruals');
Route::get('/attendance/user_accruals/{user_id}/{ap_id}', [ViewUserAccrualList::class, 'index'])->name('attendance.user_accruals');
Route::get('/attendance/user_accruals/add/{id?}', [EditUserAccrual::class, 'index'])->name('attendance.user_accruals.add');
Route::post('/attendance/user_accruals/submit/{id?}', [EditUserAccrual::class, 'submit'])->name('attendance.user_accruals.submit');
Route::delete('/attendance/user_accruals/delete/{id}', [ViewUserAccrualList::class, 'delete'])->name('attendance.user_accruals.delete');

Route::get('/attendance/paystubs/', [PayStubList::class, 'index'])->name('attendance.paystubs');


// ===============================================================================================================================
