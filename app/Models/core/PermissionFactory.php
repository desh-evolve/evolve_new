<?php

namespace App\Models\Core;

class PermissionFactory extends Factory {
	protected $table = 'permission';
	protected $pk_sequence_name = 'permission_id_seq'; //PK Sequence name

	protected $permission_control_obj = NULL;
	protected $company_id = NULL;

	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'preset':
				$retval = array(
										//-1 => ('--'),
										10 => ('Regular Employee'),
										18 => ('Supervisor (Subordinates Only)'),
										20 => ('Supervisor (All Employees)'),
										30 => ('Payroll Administrator'),
										40 => ('Administrator')
									);
				break;
			case 'preset_level':
				$retval = array(
										10 => 1,
										18 => 10,
										20 => 15,
										30 => 20,
										40 => 25,
									);
				break;
			case 'section_group':
				$retval = array(
											0 => ('-- Please Choose --'),
											'all' => ('-- All --'),
											'company' => ('Company'),
											'user' => ('Employee'),
											'schedule' => ('Schedule'),
											'attendance' => ('Attendance'),
											'job' => ('Job Tracking'),
											'invoice' => ('Invoicing'),
											'payroll' => ('Payroll'),
											'policy' => ('Policies'),
											'report' => ('Reports'),
											);
				break;
			case 'section_group_map':
				$retval = array(
										'company' => array(
															'system',
															'company',
															'currency',
															'branch',
															'department',
															'station',
															'hierarchy',
															'authorization',
															'message',
															'other_field',
															'document',
															'help',
															'permission',
															'pay_period_schedule',
															),
										'user' 	=> array(
															'user',
															'user_preference',
															'user_tax_deduction',
														),
										'schedule' 	=> array(
															'schedule',
															'recurring_schedule',
															'recurring_schedule_template',
														),
										'attendance' 	=> array(
															'punch',
															'absence',
															'accrual',
															'request',
														),
										'job' 	=> array(
															'job',
															'job_item',
															'job_report',
														),
										'invoice' 	=> array(
															'invoice_config',
															'client',
															'client_payment',
															'product',
															'tax_policy',
															'area_policy',
															'shipping_policy',
															'payment_gateway',
															'transaction',
															'invoice',
															'invoice_report'
														),
										'policy' 	=> array(
															'policy_group',
															'schedule_policy',
															'meal_policy',
															'break_policy',
															'over_time_policy',
															'premium_policy',
															'accrual_policy',
															'absence_policy',
															'round_policy',
															'exception_policy',
															'holiday_policy',
														),
										'payroll' 	=> array(
															'pay_stub_account',
															'pay_stub',
															'pay_stub_amendment',
															'wage',
															'roe',
															'company_tax_deduction',
														),
										'report' 	=> array(
															'report',
														),

										);
				break;

			case 'section':
				$retval = array(
										'system' => ('System'),
										'company' => ('Company'),
										'currency' => ('Currency'),
										'branch' => ('Branch'),
										'department' => ('Department'),
										'station' => ('Station'),
										'hierarchy' => ('Hierarchy'),
										'authorization' => ('Authorization'),
										'other_field' => ('Other Fields'),
										'document' => ('Documents'),
										'message' => ('Message'),
										'help' => ('Help'),
										'permission' => ('Permissions'),

										'user' => ('Employees'),
										'user_preference' => ('Employee Preferences'),
										'user_tax_deduction' => ('Employee Tax / Deductions'),

										'schedule' => ('Schedule'),
										'recurring_schedule' => ('Recurring Schedule'),
										'recurring_schedule_template' => ('Recurring Schedule Template'),

										'request' => ('Requests'),
										'accrual' => ('Accruals'),
										'leaves' => ('Leaves'),
										'punch' => ('Punch'),
										'absence' => ('Absence'),

										'job' => ('Jobs'),
										'job_item' => ('Job Tasks'),
										'job_report' => ('Job Reports'),

										'invoice_config' => ('Invoice Settings'),
										'client' => ('Invoice Clients'),
										'client_payment' => ('Client Payment Methods'),
										'product' => ('Products'),
										'tax_policy' => ('Tax Policies'),
										'shipping_policy' => ('Shipping Policies'),
										'area_policy' => ('Area Policies'),
										'payment_gateway' => ('Payment Gateway'),
										'transaction' => ('Invoice Transactions'),
										'invoice' => ('Invoices'),
										'invoice_report' => ('Invoice Reports'),

										'policy_group' => ('Policy Group'),
										'schedule_policy' => ('Schedule Policies'),
										'meal_policy' => ('Meal Policies'),
										'break_policy' => ('Break Policies'),
										'over_time_policy' => ('Overtime Policies'),
										'premium_policy' => ('Premium Policies'),
										'accrual_policy' => ('Accrual Policies'),
										'absence_policy' => ('Absence Policies'),
										'round_policy' => ('Rounding Policies'),
										'exception_policy' => ('Exception Policies'),
										'holiday_policy' => ('Holiday Policies'),

										'pay_stub_account' => ('Pay Stub Accounts'),
										'pay_stub' => ('Employee Pay Stubs'),
										'pay_stub_amendment' => ('Employee Pay Stub Amendments'),
										'wage' => ('Wages'),
										'pay_period_schedule' => ('Pay Period Schedule'),
										'roe' => ('Record of Employment'),
										'company_tax_deduction' => ('Company Tax / Deductions'),

										'report' => ('Reports'),
									);
				break;
			case 'name':
				$retval = array(
											'system' => array(
																'login' => ('Login Enabled'),
															),
											'company' => 	array(
																'enabled' => ('Enabled'),
																'view_own' => ('View Own'),
																'view' => ('View'),
																'add' => ('Add'),
																'edit_own' => ('Edit Own'),
																'edit' => ('Edit'),
																'delete_own' => ('Delete Own'),
																'delete' => ('Delete'),
																//'undelete' => ('Un-Delete'),
																'edit_own_bank' => ('Edit Own Banking Information'),
																'login_other_user' => ('Login as Other Employee')
															),
											'user' => 	array(
																'enabled' => ('Enabled'),
																'view_own' => ('View Own'),
																'view_child' => ('View Subordinate'),
																'view' => ('View'),
																'add' => ('Add'),
																'edit_own' => ('Edit Own'),
																'edit_child' => ('Edit Subordinate'),
																'edit' => ('Edit'),
																'edit_advanced' => ('Edit Advanced'),
																'edit_own_bank' => ('Edit Own Bank Info'),
																'edit_child_bank' => ('Edit Subordinate Bank Info'),
																'edit_bank' => ('Edit Bank Info'),
																'edit_permission_group' => ('Edit Permission Group'),
																'edit_pay_period_schedule' => ('Edit Pay Period Schedule'),
																'edit_policy_group' => ('Edit Policy Group'),
																'edit_hierarchy' => ('Edit Hierarchy'),
																'edit_own_password' => ('Edit Own Password'),
																'edit_own_phone_password' => ('Edit Own Quick Punch Password'),
																'enroll' => ('Enroll Employees'),
																'enroll_child' => ('Enroll Subordinate'),
																'timeclock_admin' => ('TimeClock Administrator'),
																'delete_own' => ('Delete Own'),
																'delete_child' => ('Delete Subordinate'),
																'delete' => ('Delete'),
																'view_sin' => ('View SIN/SSN'),
																'medical_aid_scheme' => ('Medical Aid Scheme'),//FL ADDED FOR NATONAL PVC 20160627
																'personal_date_update_form' => ('Personal Date Update Form'),//FL ADDED FOR NATONAL PVC 20160627
																'employee_excel_upload' => ('Employee List Excel Upload'),//FL ADDED FOR NATONAL PVC 20160627
																//'undelete' => ('Un-Delete')
															),
											'user_preference' => 	array(
																'enabled' => ('Enabled'),
																'view_own' => ('View Own'),
																'view_child' => ('View Subordinate'),
																'view' => ('View'),
																'add' => ('Add'),
																'edit_own' => ('Edit Own'),
																'edit_child' => ('Edit Subordinate'),
																'edit' => ('Edit'),
																'delete_own' => ('Delete Own'),
																'delete_child' => ('Delete Subordinate'),
																'delete' => ('Delete'),
																//'undelete' => ('Un-Delete')
															),
											'user_tax_deduction' => 	array(
																'enabled' => ('Enabled'),
																'view_own' => ('View Own'),
																'view_child' => ('View Subordinate'),
																'view' => ('View'),
																'add' => ('Add'),
																'edit_own' => ('Edit Own'),
																'edit_child' => ('Edit Subordinate'),
																'edit' => ('Edit'),
																'delete_own' => ('Delete Own'),
																'delete_child' => ('Delete Subordinate'),
																'delete' => ('Delete'),
																//'undelete' => ('Un-Delete')
															),
											'roe' => 	array(
																'enabled' => ('Enabled'),
																'view_own' => ('View Own'),
																'view_child' => ('View Subordinate'),
																'view' => ('View'),
																'add' => ('Add'),
																'edit_own' => ('Edit Own'),
																'edit_child' => ('Edit Subordinate'),
																'edit' => ('Edit'),
																'delete_own' => ('Delete Own'),
																'delete_child' => ('Delete Subordinate'),
																'delete' => ('Delete'),
																//'undelete' => ('Un-Delete')
															),
											'company_tax_deduction' => 	array(
																'enabled' => ('Enabled'),
																'view_own' => ('View Own'),
																'view' => ('View'),
																'add' => ('Add'),
																'edit_own' => ('Edit Own'),
																'edit' => ('Edit'),
																'delete_own' => ('Delete Own'),
																'delete' => ('Delete'),
																//'undelete' => ('Un-Delete')
															),
											'pay_stub_account' => 	array(
																'enabled' => ('Enabled'),
																'view_own' => ('View Own'),
																'view' => ('View'),
																'add' => ('Add'),
																'edit_own' => ('Edit Own'),
																'edit' => ('Edit'),
																'delete_own' => ('Delete Own'),
																'delete' => ('Delete'),
																//'undelete' => ('Un-Delete')
															),
											'pay_stub' => 	array(
																'enabled' => ('Enabled'),
																'view_own' => ('View Own'),
																'view_child' => ('View Subordinate'),
																'view' => ('View'),
																'add' => ('Add'),
																'edit_own' => ('Edit Own'),
																'edit_child' => ('Edit Subordinate'),
																'edit' => ('Edit'),
																'delete_own' => ('Delete Own'),
																'delete_child' => ('Delete Subordinate'),
																'delete' => ('Delete'),
																//'undelete' => ('Un-Delete')
															),
											'pay_stub_amendment' => 	array(
																'enabled' => ('Enabled'),
																'view_own' => ('View Own'),
																'view_child' => ('View Subordinate'),
																'view' => ('View'),
																'add' => ('Add'),
																'edit_own' => ('Edit Own'),
																'edit_child' => ('Edit Subordinate'),
																'edit' => ('Edit'),
																'delete_own' => ('Delete Own'),
																'delete_child' => ('Delete Subordinate'),
																'delete' => ('Delete'),
																//'undelete' => ('Un-Delete')
															),
											'wage' => 	array(
																'enabled' => ('Enabled'),
																'view_own' => ('View Own'),
																'view_child' => ('View Subordinate'),
																'view' => ('View'),
																'add' => ('Add'),
																'edit_own' => ('Edit Own'),
																'edit_child' => ('Edit Subordinate'),
																'edit' => ('Edit'),
																'delete_own' => ('Delete Own'),
																'delete_child' => ('Delete Subordinate'),
																'delete' => ('Delete'),
																//'undelete' => ('Un-Delete')
															),
											'currency' => 	array(
																'enabled' => ('Enabled'),
																'view_own' => ('View Own'),
																'view' => ('View'),
																'add' => ('Add'),
																'edit_own' => ('Edit Own'),
																'edit' => ('Edit'),
																'delete_own' => ('Delete Own'),
																'delete' => ('Delete'),
																//'undelete' => ('Un-Delete')
															),
											'branch' => 	array(
																'enabled' => ('Enabled'),
																'view_own' => ('View Own'),
																'view' => ('View'),
																'add' => ('Add'),
																'edit_own' => ('Edit Own'),
																'edit' => ('Edit'),
																'delete_own' => ('Delete Own'),
																'delete' => ('Delete'),
																//'undelete' => ('Un-Delete')
															),
											'department' => 	array(
																'enabled' => ('Enabled'),
																'view_own' => ('View Own'),
																'view' => ('View'),
																'add' => ('Add'),
																'edit_own' => ('Edit Own'),
																'edit' => ('Edit'),
																'delete_own' => ('Delete Own'),
																'delete' => ('Delete'),
																//'undelete' => ('Un-Delete'),
																'assign' => ('Assign Employees')

															),
											'station' => 	array(
																'enabled' => ('Enabled'),
																'view_own' => ('View Own'),
																'view' => ('View'),
																'add' => ('Add'),
																'edit_own' => ('Edit Own'),
																'edit' => ('Edit'),
																'delete_own' => ('Delete Own'),
																'delete' => ('Delete'),
																//'undelete' => ('Un-Delete'),
																'assign' => ('Assign Employees')
															),
											'pay_period_schedule' => 	array(
																'enabled' => ('Enabled'),
																'view_own' => ('View Own'),
																'view' => ('View'),
																'add' => ('Add'),
																'edit_own' => ('Edit Own'),
																'edit' => ('Edit'),
																'delete_own' => ('Delete Own'),
																'delete' => ('Delete'),
																//'undelete' => ('Un-Delete'),
																'assign' => ('Assign Employees')
															),
											'schedule' => 	array(
																'enabled' => ('Enabled'),
																'view_own' => ('View Own'),
																'view_child' => ('View Subordinate'),
																'view' => ('View'),
																'add' => ('Add'),
																'edit_own' => ('Edit Own'),
																'edit_child' => ('Edit Subordinate'),
																'edit' => ('Edit'),
																'delete_own' => ('Delete Own'),
																'delete_child' => ('Delete Subordinate'),
																'delete' => ('Delete'),
																//'undelete' => ('Un-Delete'),
																'edit_branch' => ('Edit Branch Field'),
																'edit_department' => ('Edit Department Field'),
																'edit_job' => ('Edit Job Field'),
																'edit_job_item' => ('Edit Task Field'),
																'add_rosters' => ('Add Rosters'),//FL ADDED
															),
											'other_field' => 	array(
																'enabled' => ('Enabled'),
																'view_own' => ('View Own'),
																'view' => ('View'),
																'add' => ('Add'),
																'edit_own' => ('Edit Own'),
																'edit' => ('Edit'),
																'delete_own' => ('Delete Own'),
																'delete' => ('Delete'),
																//'undelete' => ('Un-Delete'),
															),
											'document' => 	array(
																'enabled' => ('Enabled'),
																'view_own' => ('View Own'),
																'view' => ('View'),
																'view_private' => ('View Private'),
																'add' => ('Add'),
																'edit_own' => ('Edit Own'),
																'edit' => ('Edit'),
																'edit_private' => ('Edit Private'),
																'delete_own' => ('Delete Own'),
																'delete' => ('Delete'),
																'delete_private' => ('Delete Private'),
																//'undelete' => ('Un-Delete'),
															),
											'accrual' => 	array(
																'enabled' => ('Enabled'),
																'view_own' => ('View Own'),
																'view_child' => ('View Subordinate'),
																'view' => ('View'),
																'add' => ('Add'),
																'edit_own' => ('Edit Own'),
																'edit_child' => ('Edit Subordinate'),
																'edit' => ('Edit'),
																'delete_own' => ('Delete Own'),
																'delete_child' => ('Delete Subordinate'),
																'delete' => ('Delete'),
																//'undelete' => ('Un-Delete')
															),
											'leaves' => 	array( //FL ADDED FOR LEAVE MANAGEMENT
																'enabled' => ('Enabled'),
																'view_own' => ('View Own'),
																'view_child' => ('View Subordinate'),
																'view' => ('View'),
																'add' => ('Add'),
																'edit_own' => ('Edit Own'),
																'edit_child' => ('Edit Subordinate'),
																'edit' => ('Edit'),
																'delete_own' => ('Delete Own'),
																'delete_child' => ('Delete Subordinate'),
																'delete' => ('Delete'),
																//'undelete' => ('Un-Delete')
															),
											'policy_group' => 	array(
																'enabled' => ('Enabled'),
																'view_own' => ('View Own'),
																'view' => ('View'),
																'add' => ('Add'),
																'edit_own' => ('Edit Own'),
																'edit' => ('Edit'),
																'delete_own' => ('Delete Own'),
																'delete' => ('Delete'),
																//'undelete' => ('Un-Delete')
															),
											'schedule_policy' => 	array(
																'enabled' => ('Enabled'),
																'view_own' => ('View Own'),
																'view' => ('View'),
																'add' => ('Add'),
																'edit_own' => ('Edit Own'),
																'edit' => ('Edit'),
																'delete_own' => ('Delete Own'),
																'delete' => ('Delete'),
																//'undelete' => ('Un-Delete')
															),
											'meal_policy' => 	array(
																'enabled' => ('Enabled'),
																'view_own' => ('View Own'),
																'view' => ('View'),
																'add' => ('Add'),
																'edit_own' => ('Edit Own'),
																'edit' => ('Edit'),
																'delete_own' => ('Delete Own'),
																'delete' => ('Delete'),
																//'undelete' => ('Un-Delete')
															),
											'break_policy' => 	array(
																'enabled' => ('Enabled'),
																'view_own' => ('View Own'),
																'view' => ('View'),
																'add' => ('Add'),
																'edit_own' => ('Edit Own'),
																'edit' => ('Edit'),
																'delete_own' => ('Delete Own'),
																'delete' => ('Delete'),
																//'undelete' => ('Un-Delete')
															),
											'absence_policy' => 	array(
																'enabled' => ('Enabled'),
																'view_own' => ('View Own'),
																'view' => ('View'),
																'add' => ('Add'),
																'edit_own' => ('Edit Own'),
																'edit' => ('Edit'),
																'delete_own' => ('Delete Own'),
																'delete' => ('Delete'),
																//'undelete' => ('Un-Delete')
															),
											'accrual_policy' => 	array(
																'enabled' => ('Enabled'),
																'view_own' => ('View Own'),
																'view' => ('View'),
																'add' => ('Add'),
																'edit_own' => ('Edit Own'),
																'edit' => ('Edit'),
																'delete_own' => ('Delete Own'),
																'delete' => ('Delete'),
																//'undelete' => ('Un-Delete')
															),
											'over_time_policy' => 	array(
																'enabled' => ('Enabled'),
																'view_own' => ('View Own'),
																'view' => ('View'),
																'add' => ('Add'),
																'edit_own' => ('Edit Own'),
																'edit' => ('Edit'),
																'delete_own' => ('Delete Own'),
																'delete' => ('Delete'),
																//'undelete' => ('Un-Delete')
															),
											'premium_policy' => 	array(
																'enabled' => ('Enabled'),
																'view_own' => ('View Own'),
																'view' => ('View'),
																'add' => ('Add'),
																'edit_own' => ('Edit Own'),
																'edit' => ('Edit'),
																'delete_own' => ('Delete Own'),
																'delete' => ('Delete'),
																//'undelete' => ('Un-Delete')
															),
											'round_policy' => 	array(
																'enabled' => ('Enabled'),
																'view' => ('View'),
																'view_own' => ('View Own'),
																'add' => ('Add'),
																'edit_own' => ('Edit Own'),
																'edit' => ('Edit'),
																'delete_own' => ('Delete Own'),
																'delete' => ('Delete'),
																//'undelete' => ('Un-Delete')
															),
											'exception_policy' => 	array(
																'enabled' => ('Enabled'),
																'view_own' => ('View Own'),
																'view' => ('View'),
																'add' => ('Add'),
																'edit_own' => ('Edit Own'),
																'edit' => ('Edit'),
																'delete_own' => ('Delete Own'),
																'delete' => ('Delete'),
																//'undelete' => ('Un-Delete')
															),
											'holiday_policy' => 	array(
																'enabled' => ('Enabled'),
																'view_own' => ('View Own'),
																'view' => ('View'),
																'add' => ('Add'),
																'edit_own' => ('Edit Own'),
																'edit' => ('Edit'),
																'delete_own' => ('Delete Own'),
																'delete' => ('Delete'),
																//'undelete' => ('Un-Delete')
															),

											'recurring_schedule_template' => 	array(
																'enabled' => ('Enabled'),
																'view_own' => ('View Own'),
																'view' => ('View'),
																'add' => ('Add'),
																'edit_own' => ('Edit Own'),
																'edit' => ('Edit'),
																'delete_own' => ('Delete Own'),
																'delete' => ('Delete'),
																//'undelete' => ('Un-Delete')
															),
											'recurring_schedule' => 	array(
																'enabled' => ('Enabled'),
																'view_own' => ('View Own'),
																'view_child' => ('View Subordinate'),
																'view' => ('View'),
																'add' => ('Add'),
																'edit_own' => ('Edit Own'),
																'edit_child' => ('Edit Subordinate'),
																'edit' => ('Edit'),
																'delete_own' => ('Delete Own'),
																'delete_child' => ('Delete Subordinate'),
																'delete' => ('Delete'),
																//'undelete' => ('Un-Delete')
															),
											'request' => 	array(
																'enabled' => ('Enabled'),
																'view_own' => ('View Own'),
																'view_child' => ('View Subordinate'),
																'view' => ('View'),
																'add' => ('Add'),
																'edit_own' => ('Edit Own'),
																'edit_child' => ('Edit Subordinate'),
																'edit' => ('Edit'),
																'delete_own' => ('Delete Own'),
																'delete_child' => ('Delete Subordinate'),
																'delete' => ('Delete'),
																//'undelete' => ('Un-Delete'),
																'authorize' => ('Authorize')
															),
											'punch' => 	array(
																'enabled' => ('Enabled'),
																'view_own' => ('View Own'),
																'view_child' => ('View Subordinate'),
																'view' => ('View'),
																'add' => ('Add'),
																'edit_own' => ('Edit Own'),
																'edit_child' => ('Edit Subordinate'),
																'edit' => ('Edit'),
																'delete_own' => ('Delete Own'),
																'delete_child' => ('Delete Subordinate'),
																'delete' => ('Delete'),
																//'undelete' => ('Un-Delete'),
																'verify_time_sheet' => ('Verify TimeSheet'),
																'authorize' => ('Authorize TimeSheet'),
																'punch_in_out' => ('Punch In/Out'),
																'edit_transfer' => ('Edit Transfer Field'),
																'default_transfer' => ('Default Transfer On'),
																'edit_branch' => ('Edit Branch Field'),
																'edit_department' => ('Edit Department Field'),
																'edit_job' => ('Edit Job Field'),
																'edit_job_item' => ('Edit Task Field'),
																'edit_quantity' => ('Edit Quantity Field'),
																'edit_bad_quantity' => ('Edit Bad Quantity Field'),
																'edit_note' => ('Edit Note Field'),
																'edit_other_id1' => ('Edit Other ID1 Field'),
																'edit_other_id2' => ('Edit Other ID2 Field'),
																'edit_other_id3' => ('Edit Other ID3 Field'),
																'edit_other_id4' => ('Edit Other ID4 Field'),
																'edit_other_id5' => ('Edit Other ID5 Field'),
															),
											'absence' => 	array(
																'enabled' => ('Enabled'),
																'view_own' => ('View Own'),
																'view_child' => ('View Subordinate'),
																'view' => ('View'),
																'add' => ('Add'),
																'edit_own' => ('Edit Own'),
																'edit_child' => ('Edit Subordinate'),
																'edit' => ('Edit'),
																'delete_own' => ('Delete Own'),
																'delete_child' => ('Delete Subordinate'),
																'delete' => ('Delete'),
																//'undelete' => ('Un-Delete'),
																'edit_branch' => ('Edit Branch Field'),
																'edit_department' => ('Edit Department Field'),
																'edit_job' => ('Edit Job Field'),
																'edit_job_item' => ('Edit Task Field'),
															),
											'hierarchy' => 	array(
																'enabled' => ('Enabled'),
																'view_own' => ('View Own'),
																'view' => ('View'),
																'add' => ('Add'),
																'edit_own' => ('Edit Own'),
																'edit' => ('Edit'),
																'delete_own' => ('Delete Own'),
																'delete' => ('Delete'),
																//'undelete' => ('Un-Delete')
															),
											'authorization' => 	array(
																'enabled' => ('Enabled'),
																'view' => ('View')
															),
											'message' => 	array(
																'enabled' => ('Enabled'),
																'view_own' => ('View Own'),
																'view' => ('View'),
																'add' => ('Add'),
																'add_advanced' => ('Add Advanced'),
																'edit_own' => ('Edit Own'),
																'edit' => ('Edit'),
																'delete_own' => ('Delete Own'),
																'delete' => ('Delete'),
																'send_to_any' => ('Send to Any Employee'),
																'send_to_child' => ('Send to Subordinate')
																//'undelete' => ('Un-Delete')
															),
											'help' => 	array(
																'enabled' => ('Enabled'),
																'view_own' => ('View Own'),
																'view' => ('View'),
																'add' => ('Add'),
																'edit_own' => ('Edit Own'),
																'edit' => ('Edit'),
																'delete_own' => ('Delete Own'),
																'delete' => ('Delete'),
																//'undelete' => ('Un-Delete')
															),
											'report' => 		array(
																'enabled' => ('Enabled'),
																'view_active_shift' => ('View Whos In Summary'),
																'view_user_information' => ('View Employee Information'),
																'view_user_detail' => ('View Employee Detail'),
																'view_pay_stub_summary' => ('Pay Stub Summary'),
																'view_payroll_export' => ('Payroll Export'),
																'view_wages_payable_summary' => ('Wages Payable Summary'),
																'view_system_log' => ('Audit Trail'),
																//'view_employee_pay_stub_summary' => ('Employee Pay Stub Summary'),
																//'view_shift_amendment_summary' => ('Shift Amendment Summary'),
																'view_timesheet_summary' => ('Timesheet Summary'),
																'view_leave_summary' => ('Leaves Summary'),
																'view_accrual_balance_summary' => ('Accrual Balance Summary'),
																'view_leave_summary' => ('Leave Balance Summary'),//FL ADDED FOR NATONAL PVC 20160816
																'view_schedule_summary' => ('Schedule Summary'),
                                                                'cform_reports' => ('C Forms Reports'),//FL ADDED 20160201H
                                                                'mail_pay_slip' => ('Mail Payslips'),//FL ADDED 20160201H
                                                               'tot_org_summary' => ('Total Organization Summary'),//FL ADDED 20160201H
                                                               'cform_reports_new' => ('C Forms Reports New'),//FL ADDED 20160201H
																'view_punch_summary' => ('Punch Summary'),
																'view_remittance_summary' => ('Remittance Summary'),
																//'view_branch_summary' => ('Branch Summary'),
																'view_employee_summary' => ('Employee Summary'),
																'view_t4_summary' => ('T4 Summary'),
																'view_generic_tax_summary' => ('Generic Tax Summary'),
																'view_form941' => ('Form 941'),
																'view_form940' => ('Form 940'),
																'view_form940ez' => ('Form 940-EZ'),
																'view_form1099misc' => ('Form 1099-Misc'),
																'view_formW2' => ('Form W2 / W3'),
																'view_user_barcode' => ('Employee Barcodes'),
																'view_general_ledger_summary' => ('General Ledger Summary'),
																
															),
											'job' => 	array(
																'enabled' => ('Enabled'),
																'view_own' => ('View Own'),
																'view' => ('View'),
																'add' => ('Add'),
																'edit_own' => ('Edit Own'),
																'edit' => ('Edit'),
																'delete_own' => ('Delete Own'),
																'delete' => ('Delete'),
																//'undelete' => ('Un-Delete')
															),
											'job_item' => 	array(
																'enabled' => ('Enabled'),
																'view_own' => ('View Own'),
																'view' => ('View'),
																'add' => ('Add'),
																'edit_own' => ('Edit Own'),
																'edit' => ('Edit'),
																'delete_own' => ('Delete Own'),
																'delete' => ('Delete'),
																//'undelete' => ('Un-Delete')
															),
											'job_report' => 		array(
																'enabled' => ('Enabled'),
																'view_job_summary' => ('View Job Summary'),
																'view_job_analysis' => ('View Job Analysis'),
																'view_job_payroll_analysis' => ('View Job Payroll Analysis'),
																'view_job_barcode' => ('View Job Barcode')
															),
											'invoice_config' => 	array(
																'enabled' => ('Enabled'),
																'add' => ('Add'),
																'edit' => ('Edit'),
																'delete' => ('Delete'),
																//'undelete' => ('Un-Delete')
															),
											'client' => 	array(
																'enabled' => ('Enabled'),
																'view_own' => ('View Own'),
																'view' => ('View'),
																'add' => ('Add'),
																'edit_own' => ('Edit Own'),
																'edit' => ('Edit'),
																'delete_own' => ('Delete Own'),
																'delete' => ('Delete'),
																//'undelete' => ('Un-Delete')
															),
											'client_payment' => 	array(
																'enabled' => ('Enabled'),
																'view_own' => ('View Own'),
																'view' => ('View'),
																'add' => ('Add'),
																'edit_own' => ('Edit Own'),
																'edit' => ('Edit'),
																'delete_own' => ('Delete Own'),
																'delete' => ('Delete'),
																'view_credit_card' => ('View Credit Card #'),
																//'undelete' => ('Un-Delete')
															),
											'product' => 	array(
																'enabled' => ('Enabled'),
																'view_own' => ('View Own'),
																'view' => ('View'),
																'add' => ('Add'),
																'edit_own' => ('Edit Own'),
																'edit' => ('Edit'),
																'delete_own' => ('Delete Own'),
																'delete' => ('Delete'),
																//'undelete' => ('Un-Delete')
															),
											'tax_policy' => 	array(
																'enabled' => ('Enabled'),
																'view_own' => ('View Own'),
																'view' => ('View'),
																'add' => ('Add'),
																'edit_own' => ('Edit Own'),
																'edit' => ('Edit'),
																'delete_own' => ('Delete Own'),
																'delete' => ('Delete'),
																//'undelete' => ('Un-Delete')
															),
											'shipping_policy' => 	array(
																'enabled' => ('Enabled'),
																'view_own' => ('View Own'),
																'view' => ('View'),
																'add' => ('Add'),
																'edit_own' => ('Edit Own'),
																'edit' => ('Edit'),
																'delete_own' => ('Delete Own'),
																'delete' => ('Delete'),
																//'undelete' => ('Un-Delete')
															),
											'area_policy' => 	array(
																'enabled' => ('Enabled'),
																'view_own' => ('View Own'),
																'view' => ('View'),
																'add' => ('Add'),
																'edit_own' => ('Edit Own'),
																'edit' => ('Edit'),
																'delete_own' => ('Delete Own'),
																'delete' => ('Delete'),
																//'undelete' => ('Un-Delete')
															),
											'payment_gateway' => 	array(
																'enabled' => ('Enabled'),
																'view_own' => ('View Own'),
																'view' => ('View'),
																'add' => ('Add'),
																'edit_own' => ('Edit Own'),
																'edit' => ('Edit'),
																'delete_own' => ('Delete Own'),
																'delete' => ('Delete'),
																//'undelete' => ('Un-Delete')
															),
											'transaction' => 	array(
																'enabled' => ('Enabled'),
																'view_own' => ('View Own'),
																'view' => ('View'),
																'add' => ('Add'),
																'edit_own' => ('Edit Own'),
																'edit' => ('Edit'),
																'delete_own' => ('Delete Own'),
																'delete' => ('Delete'),
																//'undelete' => ('Un-Delete')
															),
											'invoice' => 	array(
																'enabled' => ('Enabled'),
																'view_own' => ('View Own'),
																'view' => ('View'),
																'add' => ('Add'),
																'edit_own' => ('Edit Own'),
																'edit' => ('Edit'),
																'delete_own' => ('Delete Own'),
																'delete' => ('Delete'),
																//'undelete' => ('Un-Delete')
															),
											'invoice_report' => 		array(
																'enabled' => ('Enabled'),
																'view_transaction_summary' => ('View Transaction Summary'),
															),
											'permission' => 	array(
																'enabled' => ('Enabled'),
																'view_own' => ('View Own'),
																'view_child' => ('View Subordinate'),
																'view' => ('View'),
																'add' => ('Add'),
																'edit_own' => ('Edit Own'),
																'edit_child' => ('Edit Subordinate'),
																'edit' => ('Edit'),
																'delete_own' => ('Delete Own'),
																'delete_child' => ('Delete Subordinate'),
																'delete' => ('Delete'),
																//'undelete' => ('Un-Delete')
															)
									);
				break;

		}

		return $retval;
	}

	function setCompany( $id ) {
		$this->company_id = $id;
		return TRUE;
	}
	function getCompany() {
		if ( $this->company_id != '' ) {
			return $this->company_id;
		} else {
			$company_id = $this->getPermissionControlObject()->getCompany();

			return $company_id;
		}
	}

	function getPermissionControlObject() {
		if ( is_object($this->permission_control_obj) ) {
			return $this->permission_control_obj;
		} else {

			$pclf = new PermissionControlListFactory();
			$pclf->getById( $this->getPermissionControl() );

			if ( $pclf->getRecordCount() == 1 ) {
				$this->permission_control_obj = $pclf->getCurrent();

				return $this->permission_control_obj;
			}

			return FALSE;
		}
	}

	function getPermissionControl() {
		if ( isset($this->data['permission_control_id']) ) {
			return $this->data['permission_control_id'];
		}

		return FALSE;
	}
	function setPermissionControl($id) {
		$id = trim($id);

		$pclf = new PermissionControlListFactory();

		if ( $id != 0
				OR
				$this->Validator->isResultSetWithRows(	'permission_control',
													$pclf->getByID($id),
													('Permission Group is invalid')
													) ) {

			$this->data['permission_control_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getSection() {
		if ( isset($this->data['section']) ) {
			return $this->data['section'];
		}

		return FALSE;
	}
	function setSection($section) {
		$section = trim($section);

		if ( $this->Validator->inArrayKey(	'section',
											$section,
											('Incorrect section'),
											$this->getOptions('section')) ) {

			$this->data['section'] = $section;

			return FALSE;
		}

		return FALSE;
	}

	function getName() {
		if ( isset($this->data['name']) ) {
			return $this->data['name'];
		}

		return FALSE;
	}
	function setName($name) {
		$name = trim($name);

		if ( $this->Validator->inArrayKey(	'name',
											$name,
											('Incorrect permission name'),
											$this->getOptions('name', $this->getSection() ) ) ) {

			$this->data['name'] = $name;

			return FALSE;
		}

		return FALSE;
	}

	function getValue() {
		if ( isset($this->data['value']) AND $this->data['value'] == 1 ) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	function setValue($value) {
		$value = trim($value);

		//Debug::Arr($value, 'Value: ', __FILE__, __LINE__, __METHOD__,10);

		if 	(	$this->Validator->isLength(		'value',
												$value,
												('Value is invalid'),
												1,
												255) ) {

			$this->data['value'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getPresetPermissions( $preset, $preset_flags ) {
		$key = Option::getByValue($preset, $this->getOptions('preset') );
		if ($key !== FALSE) {
			$preset = $key;
		}

		if ( getTTProductEdition() != TT_PRODUCT_PROFESSIONAL ) {
			$preset_flags = array();
		}

		Debug::Text('Preset: '. $preset, __FILE__, __LINE__, __METHOD__,10);
		Debug::Arr($preset_flags, 'Preset Flags... ', __FILE__, __LINE__, __METHOD__,10);

		if ( !isset($preset) OR $preset == '' OR $preset == -1 ) {
			Debug::Text('No Preset set... Skipping!', __FILE__, __LINE__, __METHOD__,10);
			return FALSE;
		}

		$preset_permissions_40 = array();
		$preset_permissions_30 = array();
		$preset_permissions_20 = array();
		$preset_permissions_18 = array();
		$preset_permissions_10 = array();
		switch( $preset ) {
			case 40:
				//Can do everything
				$preset_permissions_40 = array(
											'user' => 	array(
																'timeclock_admin' => TRUE,
															),
											'policy_group' => 	array(
																'enabled' => TRUE,
																'view' => TRUE,
																'add' => TRUE,
																'edit' => TRUE,
																'delete' => TRUE,
															),
											'schedule_policy' => 	array(
																'enabled' => TRUE,
																'view' => TRUE,
																'add' => TRUE,
																'edit' => TRUE,
																'delete' => TRUE,
															),
											'meal_policy' => 	array(
																'enabled' => TRUE,
																'view' => TRUE,
																'add' => TRUE,
																'edit' => TRUE,
																'delete' => TRUE,
															),
											'break_policy' => 	array(
																'enabled' => TRUE,
																'view' => TRUE,
																'add' => TRUE,
																'edit' => TRUE,
																'delete' => TRUE,
															),
											'over_time_policy' => 	array(
																'enabled' => TRUE,
																'view' => TRUE,
																'add' => TRUE,
																'edit' => TRUE,
																'delete' => TRUE,
															),
											'premium_policy' => 	array(
																'enabled' => TRUE,
																'view' => TRUE,
																'add' => TRUE,
																'edit' => TRUE,
																'delete' => TRUE,
															),
											'accrual_policy' => 	array(
																'enabled' => TRUE,
																'view' => TRUE,
																'add' => TRUE,
																'edit' => TRUE,
																'delete' => TRUE,
															),
											'absence_policy' => 	array(
																'enabled' => TRUE,
																'view' => TRUE,
																'add' => TRUE,
																'edit' => TRUE,
																'delete' => TRUE,
															),
											'round_policy' => 	array(
																'enabled' => TRUE,
																'view' => TRUE,
																'add' => TRUE,
																'edit' => TRUE,
																'delete' => TRUE,
															),
											'exception_policy' => 	array(
																'enabled' => TRUE,
																'view' => TRUE,
																'add' => TRUE,
																'edit' => TRUE,
																'delete' => TRUE,
															),
											'holiday_policy' => 	array(
																'enabled' => TRUE,
																'view' => TRUE,
																'add' => TRUE,
																'edit' => TRUE,
																'delete' => TRUE,
															),
											'currency' => 	array(
																'enabled' => TRUE,
																'view' => TRUE,
																'add' => TRUE,
																'edit' => TRUE,
																'delete' => TRUE,
															),
											'branch' => 	array(
																'enabled' => TRUE,
																'view' => TRUE,
																'add' => TRUE,
																'edit' => TRUE,
																'delete' => TRUE,
															),
											'department' => 	array(
																'enabled' => TRUE,
																'view' => TRUE,
																'add' => TRUE,
																'edit' => TRUE,
																'delete' => TRUE,
																'assign' => TRUE
															),
											'station' => 	array(
																'enabled' => TRUE,
																'view' => TRUE,
																'add' => TRUE,
																'edit' => TRUE,
																'delete' => TRUE,
																'assign' => TRUE
															),
											'report' => 		array(
																//'view_shift_actual_time' => TRUE,
															),
											'hierarchy' => 	array(
																'enabled' => TRUE,
																'view' => TRUE,
																'add' => TRUE,
																'edit' => TRUE,
																'delete' => TRUE,
															),
											'round_policy' => 	array(
																'enabled' => TRUE,
																'view' => TRUE,
																'add' => TRUE,
																'edit' => TRUE,
																'delete' => TRUE,
															),
											'other_field' => 	array(
																'enabled' => TRUE,
																'view' => TRUE,
																'add' => TRUE,
																'edit' => TRUE,
																'delete' => TRUE,
															),
											'currency' => 	array(
																'enabled' => TRUE,
																'view' => TRUE,
																'add' => TRUE,
																'edit' => TRUE,
																'delete' => TRUE,
															),
											'permission' => 	array(
																'enabled' => TRUE,
																'view' => TRUE,
																'add' => TRUE,
																'edit' => TRUE,
																'delete' => TRUE,
															)
											);
				if ( isset($preset_flags['invoice']) AND $preset_flags['invoice'] == 1 ) {
					Debug::Text('Applying Invoice Permissions for Admin Preset', __FILE__, __LINE__, __METHOD__,10);
					$invoice_preset_permissions_40 = array(
											'invoice_config' => 	array(
																'enabled' => TRUE,
																'add' => TRUE,
																'edit' => TRUE,
																'delete' => TRUE,
															),
													);
					$preset_permissions_40 = array_merge_recursive( $preset_permissions_40, $invoice_preset_permissions_40);
					unset($invoice_preset_permissions_40);
				} else {
					Debug::Text('NOT Applying Invoice Permissions for Admin Preset', __FILE__, __LINE__, __METHOD__,10);
				}
			case 30:
				//Payroll Admin, can do wages, taxes, etc...
				$preset_permissions_30 = array(
											'company' => 	array(
																'enabled' => TRUE,
																'view_own' => TRUE,
																'edit_own' => TRUE,
																'edit_own_bank' => TRUE
															),
											'user' => 	array(
																'add' => TRUE,
																'edit_bank' => TRUE,
																'view_sin' => TRUE,
															),
											'user_tax_deduction' => 	array(
																'enabled' => TRUE,
																'view' => TRUE,
																'add' => TRUE,
																'edit' => TRUE,
																'delete' => TRUE,
															),
											'roe' => 		array(
																'enabled' => TRUE,
																'view' => TRUE,
																'add' => TRUE,
																'edit' => TRUE,
																'delete' => TRUE,
															),
											'company_tax_deduction' => 	array(
																'enabled' => TRUE,
																'view' => TRUE,
																'add' => TRUE,
																'edit' => TRUE,
																'delete' => TRUE,
															),
											'pay_stub_account' => 	array(
																'enabled' => TRUE,
																'view' => TRUE,
																'add' => TRUE,
																'edit' => TRUE,
																'delete' => TRUE
															),
											'pay_stub' => 	array(
																'view' => TRUE,
																'add' => TRUE,
																'edit' => TRUE,
																'delete' => TRUE
															),
											'pay_stub_amendment' => 	array(
																'enabled' => TRUE,
																'view' => TRUE,
																'add' => TRUE,
																'edit' => TRUE,
																'delete' => TRUE
															),
											'wage' => 		array(
																'enabled' => TRUE,
																'view' => TRUE,
																'add' => TRUE,
																'edit' => TRUE,
																'delete' => TRUE
															),
											'pay_period_schedule' => 	array(
																'enabled' => TRUE,
																'view' => TRUE,
																'add' => TRUE,
																'edit' => TRUE,
																'delete' => TRUE,
																'assign' => TRUE
															),
											'report' => 		array(
																'view_pay_stub_summary' => TRUE,
																'view_payroll_export' => TRUE,
																//'view_employee_pay_stub_summary' => TRUE,
																'view_remittance_summary' => TRUE,
																'view_system_log' => TRUE,
																'view_employee_summary' => TRUE,
																'view_wages_payable_summary' => TRUE,
																'view_t4_summary' => TRUE,
																'view_generic_tax_summary' => TRUE,
																'view_form941' => TRUE,
																'view_form940' => TRUE,
																'view_form940ez' => TRUE,
																'view_form1099misc' => TRUE,
																'view_formW2' => TRUE,
																'view_general_ledger_summary' => TRUE
															),
											);
				if ( isset($preset_flags['invoice']) AND $preset_flags['invoice'] == 1 ) {
					Debug::Text('Applying Invoice Permissions for Payroll Admin Preset', __FILE__, __LINE__, __METHOD__,10);
					$invoice_preset_permissions_30 = array(
											'product' => 	array(
																'enabled' => TRUE,
																'view' => TRUE,
																'add' => TRUE,
																'edit' => TRUE,
																'delete' => TRUE,
															),
											'tax_policy' => 	array(
																'enabled' => TRUE,
																'view' => TRUE,
																'add' => TRUE,
																'edit' => TRUE,
																'delete' => TRUE,
															),
											'shipping_policy' => 	array(
																'enabled' => TRUE,
																'view' => TRUE,
																'add' => TRUE,
																'edit' => TRUE,
																'delete' => TRUE,
															),
											'area_policy' => 	array(
																'enabled' => TRUE,
																'view' => TRUE,
																'add' => TRUE,
																'edit' => TRUE,
																'delete' => TRUE,
															),
											'payment_gateway' => 	array(
																'enabled' => TRUE,
																'view' => TRUE,
																'add' => TRUE,
																'edit' => TRUE,
																'delete' => TRUE,
															),
											'invoice_report' => 	array(
																'enabled' => TRUE,
																'view_transaction_summary' => TRUE,
															),
													);
					$preset_permissions_30 = array_merge_recursive( $preset_permissions_30, $invoice_preset_permissions_30);
					unset($invoice_preset_permissions_30);
				} else {
					Debug::Text('NOT Applying Invoice Permissions for Payroll Admin Preset', __FILE__, __LINE__, __METHOD__,10);
				}
			case 20:
				//Supervisor (All Employees), can see all schedules and shifts, and can do authorizations
				$preset_permissions_20 = array(
											'user' => 	array(
																'add' => TRUE, //Can only add user with permissions level equal or lower.
																'view' => TRUE,
																'edit' => TRUE,
																'enroll' => TRUE,
																'delete' => TRUE
															),
											'user_preference' => 	array(
																'view' => TRUE,
																'edit' => TRUE,
															),
											'recurring_schedule_template' => 	array(
																'view' => TRUE,
																'edit' => TRUE,
																'delete' => TRUE,
															),
											'recurring_schedule' => 	array(
																'view' => TRUE,
																'edit' => TRUE,
																'delete' => TRUE,
															),
											'punch' => 	array(
																'view' => TRUE,
																'edit' => TRUE,
																'delete' => TRUE,
															),
											'absence' => 	array(
																'view' => TRUE,
																'edit' => TRUE,
																'delete' => TRUE,
															),
											'accrual' => 	array(
																'view' => TRUE,
																'edit' => TRUE,
																'delete' => TRUE,
															),
											'request' => 	array(
																'view' => TRUE,
																'edit' => TRUE,
																'delete' => TRUE,
															),
											'schedule' => 	array(
																'view' => TRUE,
																'edit' => TRUE,
																'delete' => TRUE
															),
											'message' => 	array(
																'send_to_any' => TRUE,
															),
											);

				//
				// Most of this is done on level 18;
				//
				if ( isset($preset_flags['job']) AND $preset_flags['job'] == 1 ) {
					Debug::Text('Applying Job Permissions for Supervisor Preset', __FILE__, __LINE__, __METHOD__,10);
					$job_preset_permissions_20 = array(
											'job' => 	array(
																'view' => TRUE,
																'add' => TRUE,
																'edit' => TRUE,
																'delete' => TRUE,
															),
											'job_item' => 	array(
																'enabled' => TRUE,
																'view' => TRUE,
																'add' => TRUE,
																'edit' => TRUE,
																'delete' => TRUE,
															),
													);
					$preset_permissions_20 = array_merge_recursive( $preset_permissions_20, $job_preset_permissions_20);
					unset($job_preset_permissions_20);
				} else {
					Debug::Text('NOT Applying Job Permissions for Supervisor Preset', __FILE__, __LINE__, __METHOD__,10);
				}
			case 18:
				//Supervisor (Suborindates Only), can see all schedules and shifts, and can do authorizations
				$preset_permissions_18 = array(
											'user' => 	array(
																'add' => TRUE, //Can only add user with permissions level equal or lower.
																'view_child' => TRUE,
																'edit_child' => TRUE,
																'edit_advanced' => TRUE,
																'enroll_child' => TRUE,
																'delete_child' => TRUE,
																'edit_pay_period_schedule' => TRUE,
																'edit_permission_group' => TRUE,
																'edit_policy_group' => TRUE,
																'edit_hierarchy' => TRUE,
															),
											'user_preference' => 	array(
																'view_child' => TRUE,
																'edit_child' => TRUE,
															),
											'recurring_schedule_template' => 	array(
																'enabled' => TRUE,
																'view_own' => TRUE,
																'add' => TRUE,
																'edit_own' => TRUE,
																'delete_own' => TRUE,
															),
											'recurring_schedule' => 	array(
																'enabled' => TRUE,
																'view_child' => TRUE,
																'add' => TRUE,
																'edit_child' => TRUE,
																'delete_child' => TRUE,
															),
											'punch' => 	array(
																'view_child' => TRUE,
																'edit_child' => TRUE,
																'delete_child' => TRUE,
																'authorize' => TRUE
															),
											'absence' => 	array(
																'add' => TRUE,
																'view_child' => TRUE,
																'edit_child' => TRUE,
																'delete_child' => TRUE,
																'edit_branch' => TRUE,
																'edit_department' => TRUE,
															),
											'accrual' => 	array(
																'view_child' => TRUE,
																'add' => TRUE,
																'edit_child' => TRUE,
																'delete_child' => TRUE,
															),
											'request' => 	array(
																'view_child' => TRUE,
																'edit_child' => TRUE,
																'delete_child' => TRUE,
																'authorize' => TRUE
															),
											'schedule' => 	array(
																'add' => TRUE,
																'view_child' => TRUE,
																'edit_child' => TRUE,
																'delete_child' => TRUE,
																'edit_branch' => TRUE,
																'edit_department' => TRUE,
															),
											'authorization' => 	array(
																'enabled' => TRUE,
																'view' => TRUE
															),
											'message' => 	array(
																'add_advanced' => TRUE,
																'send_to_child' => TRUE,
															),
											'report' => 		array(
																'enabled' => TRUE,
																'view_active_shift' => TRUE,
																'view_user_information' => TRUE,
																'view_user_detail' => TRUE,
																'view_timesheet_summary' => TRUE,
																'view_schedule_summary' => TRUE,
																'view_punch_summary' => TRUE,
																'view_accrual_balance_summary' => TRUE,
																'view_user_barcode' => TRUE,
															)
											);

				if ( isset($preset_flags['job']) AND $preset_flags['job'] == 1 ) {
					Debug::Text('Applying Job Permissions for Supervisor Preset', __FILE__, __LINE__, __METHOD__,10);
					$job_preset_permissions_18 = array(
											'schedule' => 	array(
																'edit_job' => TRUE,
																'edit_job_item' => TRUE,
															),
											'absence' => 	array(
																'edit_job' => TRUE,
																'edit_job_item' => TRUE,
															),
											'job' => 	array(
																'view_own' => TRUE,
																'add' => TRUE,
																'edit_own' => TRUE,
																'delete_own' => TRUE,
															),
											'job_item' => 	array(
																'enabled' => TRUE,
																'view' => TRUE,
																'add' => TRUE,
																'edit_own' => TRUE,
																'delete_own' => TRUE,
															),
											'job_report' => array(
																'enabled' => TRUE,
																'view_job_summary' => TRUE,
																'view_job_analysis' => TRUE,
																'view_job_payroll_analysis' => TRUE,
																'view_job_barcode' => TRUE
															),
													);
					$preset_permissions_18 = array_merge_recursive( $preset_permissions_18, $job_preset_permissions_18);
					unset($job_preset_permissions_18);
				} else {
					Debug::Text('NOT Applying Job Permissions for Supervisor Preset', __FILE__, __LINE__, __METHOD__,10);
				}

				if ( isset($preset_flags['invoice']) AND $preset_flags['invoice'] == 1 ) {
					Debug::Text('Applying Invoice Permissions for Supervisor Preset', __FILE__, __LINE__, __METHOD__,10);
					$invoice_preset_permissions_18 = array(
											'client' => 	array(
																'enabled' => TRUE,
																'view' => TRUE,
																'add' => TRUE,
																'edit' => TRUE,
																'delete' => TRUE,
															),
											'client_payment' => 	array(
																'enabled' => TRUE,
																'view' => TRUE,
																'add' => TRUE,
																'edit' => TRUE,
																'delete' => TRUE,
															),
											'transaction' => 	array(
																'enabled' => TRUE,
																'view' => TRUE,
																'add' => TRUE,
																'edit' => TRUE,
																'delete' => TRUE,
															),
											'invoice' => 	array(
																'enabled' => TRUE,
																'view' => TRUE,
																'add' => TRUE,
																'edit' => TRUE,
																'delete' => TRUE,
															),
													);
					$preset_permissions_18 = array_merge_recursive( $preset_permissions_18, $invoice_preset_permissions_18);
					unset($invoice_preset_permissions_18);
				} else {
					Debug::Text('NOT Applying Invoice Permissions for Supervisor Preset', __FILE__, __LINE__, __METHOD__,10);
				}

				if ( isset($preset_flags['document']) AND $preset_flags['document'] == 1 ) {
					Debug::Text('Applying Document Permissions for Supervisor Preset', __FILE__, __LINE__, __METHOD__,10);
					$document_preset_permissions_18 = array(
											'document' => 	array(
																'add' => TRUE,
																'view_private' => TRUE,
																'edit' => TRUE,
																'edit_private' => TRUE,
																'delete' => TRUE,
																'delete_private' => TRUE,
															),
													);
					$preset_permissions_18 = array_merge_recursive( $preset_permissions_18, $document_preset_permissions_18);
					unset($document_preset_permissions_18);
				} else {
					Debug::Text('NOT Applying Document Permissions for Supervisor Preset', __FILE__, __LINE__, __METHOD__,10);
				}
			case 10: //Regular Employee
				$preset_permissions_10 = array(
											'system' => array(
																'login' => TRUE,
															),
											'user' => 	array(
																'enabled' => TRUE,
																'view_own' => TRUE,
																'edit_own' => TRUE,
																'edit_own_bank' => TRUE,
																'edit_own_password' => TRUE,
																'edit_own_phone_password' => TRUE,
															),
											'user_preference' => 	array(
																'enabled' => TRUE,
																'view_own' => TRUE,
																'add' => TRUE,
																'edit_own' => TRUE,
																'delete_own' => TRUE,
															),
											'pay_stub' => 	array(
																'enabled' => TRUE,
																'view_own' => TRUE,
															),
											'accrual' => 	array(
																'enabled' => TRUE,
																'view_own' => TRUE
															),
											'request' => 	array(
																'enabled' => TRUE,
																'view_own' => TRUE,
																'add' => TRUE,
																'edit_own' => TRUE,
																'delete_own' => TRUE,
															),
											'schedule' => 	array(
																'enabled' => TRUE,
																'view_own' => TRUE,
															),
											'punch' => 	array(
																'enabled' => TRUE,
																'view_own' => TRUE,
																'add' => TRUE,
																'verify_time_sheet' => TRUE,
																'punch_in_out' => TRUE,
																'edit_transfer' => TRUE,
																'edit_branch' => TRUE,
																'edit_department' => TRUE,
																'edit_note' => TRUE,
																'edit_other_id1' => TRUE,
																'edit_other_id2' => TRUE,
																'edit_other_id3' => TRUE,
																'edit_other_id4' => TRUE,
																'edit_other_id5' => TRUE,
															),
											'absence' => 	array(
																'enabled' => TRUE,
																'view_own' => TRUE,
															),
											'message' => 	array(
																'enabled' => TRUE,
																'view_own' => TRUE,
																'add' => TRUE,
																'edit_own' => TRUE,
																'delete_own' => TRUE,
															),
											'help' => 	array(
																'enabled' => TRUE,
																'view' => TRUE,
															)
										);

				if ( isset($preset_flags['job']) AND $preset_flags['job'] == 1 ) {
					Debug::Text('Applying Job Permissions for Regular Preset', __FILE__, __LINE__, __METHOD__,10);
					$job_preset_permissions_10 = array(
											'punch' =>	array(
																'edit_job' => TRUE,
																'edit_job_item' => TRUE,
																'edit_quantity' => TRUE,
																'edit_bad_quantity' => TRUE,
															),
											'job' => 	array(
																'enabled' => TRUE,
															),
													);
					$preset_permissions_10 = array_merge_recursive( $preset_permissions_10, $job_preset_permissions_10);
					unset($job_preset_permissions_10);
				} else {
					Debug::Text('NOT Applying Job Permissions for Regular Preset', __FILE__, __LINE__, __METHOD__,10);
				}

				if ( isset($preset_flags['document']) AND $preset_flags['document'] == 1 ) {
					Debug::Text('Applying Document Permissions for Regular Preset', __FILE__, __LINE__, __METHOD__,10);
					$document_preset_permissions_10 = array(
											'document' => 	array(
																'enabled' => TRUE,
																'view' => TRUE,
															),
													);
					$preset_permissions_10 = array_merge_recursive( $preset_permissions_10, $document_preset_permissions_10);
					unset($document_preset_permissions_10);
				} else {
					Debug::Text('NOT Applying Document Permissions for Regular Preset', __FILE__, __LINE__, __METHOD__,10);
				}
		}

		//Merge all permissions
		$preset_permissions = array_merge_recursive($preset_permissions_10, $preset_permissions_18, $preset_permissions_20, $preset_permissions_30, $preset_permissions_40);
		//var_dump($preset_permissions);

		return $preset_permissions;
	}

	function applyPreset($permission_control_id, $preset, $preset_flags) {
		$preset_permissions = $this->getPresetPermissions( $preset, $preset_flags );
		
		if ( !is_array($preset_permissions) ) {
			return FALSE;
		}

		$this->setPermissionControl( $permission_control_id );

		$pf = new PermissionFactory();
		$pf->StartTransaction();

		//Delete all previous permissions for this user.
		$this->deletePermissions( $this->getCompany(), $permission_control_id);

		foreach($preset_permissions as $section => $permissions) {
			foreach($permissions as $name => $value) {
				Debug::Text('Setting Permission - Section: '. $section .' Name: '. $name .' Value: '. (int)$value, __FILE__, __LINE__, __METHOD__,10);

				$pf->setPermissionControl( $permission_control_id );
				$pf->setSection( $section );
				$pf->setName( $name );
				$pf->setValue( (int)$value );

				if ( $pf->isValid() ) {
					$pf->save();
				}
			}
		}

		//Clear cache for all users assigned to this permission_control_id
		$pclf = new PermissionControlListFactory();
		$pclf->getById( $permission_control_id );
		if ( $pclf->getRecordCount() > 0 ) {
			$pc_obj = $pclf->getCurrent();

			if ( is_array($pc_obj->getUser() ) ) {
				foreach( $pc_obj->getUser() as $user_id ) {
					$pf->clearCache( $user_id, $this->getCompany() );
				}
			}
		}
		unset($pclf, $pc_obj, $user_id);

		//$pf->FailTransaction();
		$pf->CommitTransaction();

		return TRUE;
	}

	function deletePermissions( $company_id, $permission_control_id ){
		if ( $company_id == '' ) {
			return FALSE;
		}

		if ( $permission_control_id == '' ) {
			return FALSE;
		}

		$plf = new PermissionListFactory();
		$plf->getByCompanyIDAndPermissionControlId( $company_id, $permission_control_id );
		foreach($plf->rs as $permission_obj) {
			$plf->data = (array)$permission_obj;
			$plf->delete(TRUE);
			$this->removeCache( $this->getCacheID() );
		}

		return TRUE;
	}

	static function isIgnore( $section, $name = NULL, $product_edition = 10 ) {
		global $current_company;

		//Ignore by default
		if ( $section == '' ) {
			return TRUE;
		}

		//Debug::Text(' Product Edition: '. $product_edition .' Primary Company ID: '. PRIMARY_COMPANY_ID, __FILE__, __LINE__, __METHOD__,10);
		if ( $product_edition == 20 ) {
			$ignore_permissions = array('help' => 'ALL',
										'company' => array('add','delete','delete_own','undelete','view','edit','login_other_user'),
										);
		} else {
			$ignore_permissions = array('help' => 'ALL',
										'company' => array('add','delete','delete_own','undelete','view','edit','login_other_user'),
										'schedule' => array('edit_job','edit_job_item'),
										'punch' => array('edit_job','edit_job_item','edit_quantity','edit_bad_quantity'),
										'job_item' => 'ALL',
										'invoice_config' => 'ALL',
										'client' => 'ALL',
										'client_payment' => 'ALL',
										'product' => 'ALL',
										'tax_policy' => 'ALL',
										'area_policy' => 'ALL',
										'shipping_policy' => 'ALL',
										'payment_gateway' => 'ALL',
										'transaction' => 'ALL',
										'job_report' => 'ALL',
										'invoice_report' => 'ALL',
										'invoice' => 'ALL',
										'job' => 'ALL',
										'document' => 'ALL',
										);
		}

		//If they are currently logged in as the primary company ID, allow multiple company permissions.
		if ( isset($current_company) AND $current_company->getProductEdition() > 10 AND $current_company->getId() == PRIMARY_COMPANY_ID ) {
			unset($ignore_permissions['company']);
		}

		if ( isset($ignore_permissions[$section])
				AND
					(
						(
							$name != ''
							AND
							($ignore_permissions[$section] == 'ALL'
							OR ( is_array($ignore_permissions[$section]) AND in_array($name, $ignore_permissions[$section]) ) )
						)
						OR
						(
							$name == ''
							AND
							$ignore_permissions[$section] == 'ALL'
						)
					)

					) {
			Debug::Text(' IGNORING... Section: '. $section .' Name: '. $name, __FILE__, __LINE__, __METHOD__,10);
			return TRUE;
		} else {
			//Debug::Text(' NOT IGNORING... Section: '. $section .' Name: '. $name, __FILE__, __LINE__, __METHOD__,10);
			return FALSE;
		}
	}

	function preSave() {
		//Just update any existing permissions. It would probably be faster to delete them all and re-insert though.
		$plf = new PermissionListFactory();
		$obj = $plf->getByCompanyIdAndPermissionControlIdAndSectionAndName( $this->getCompany(), $this->getPermissionControl(), $this->getSection(), $this->getName() )->getCurrent();
		$this->setId( $obj->getId() );

		return TRUE;
	}

	function getCacheID() {
		$cache_id = 'permission_query_'.$this->getSection().$this->getName().$this->getPermissionControl().$this->getCompany();

		return $cache_id;
	}

	function clearCache( $user_id, $company_id ) {
		Debug::Text(' Clearing Cache for User ID: '. $user_id, __FILE__, __LINE__, __METHOD__,10);

		$cache_id = 'permission_all'.$user_id.$company_id;
		return $this->removeCache( $cache_id );
	}

	function postSave() {
		//$cache_id = 'permission_query_'.$this->getSection().$this->getName().$this->getUser().$this->getCompany();
		//$this->removeCache( $this->getCacheID() );

		return TRUE;
	}

	function addLog( $log_action ) {
		if ( $this->getValue() == TRUE ) {
			$value_display =  ( 'ALLOW' );
		} else {
			$value_display =  ( 'DENY' );
		}

		return TTLog::addEntry( $this->getPermissionControl(), $log_action, ('Section').': '. Option::getByKey($this->getSection(), $this->getOptions('section') ) .' Name: '. Option::getByKey( $this->getName(), $this->getOptions('name', $this->getSection() ) ) .' Value: '. $value_display , NULL, $this->getTable() );
	}
}
?>
