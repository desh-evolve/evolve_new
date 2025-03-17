<?php

namespace App\Models\Report;

class TimesheetSummaryReport extends Report {

	function __construct() {
		$this->title = ('TimeSheet Summary Report');
		$this->file_name = 'timesheet_summary_report';

		parent::__construct();

		return TRUE;
	}

	protected function _checkPermissions( $user_id, $company_id ) {
		if ( $this->getPermissionObject()->Check('report','enabled', $user_id, $company_id )
				AND $this->getPermissionObject()->Check('report','view_timesheet_summary', $user_id, $company_id ) ) {
			return TRUE;
		}

		return FALSE;
	}

	protected function _getOptions( $name, $params = NULL ) {
		$retval = NULL;
		switch( $name ) {
			case 'default_setup_fields':
				$retval = array(
										'template',
										'time_period',
										'columns',
							   );
				break;
			case 'setup_fields':
				$retval = array(
										//Static Columns - Aggregate functions can't be used on these.
										'-1000-template' => ('Template'),
										'-1010-time_period' => ('Time Period'),

										'-2010-user_status_id' => ('Employee Status'),
										'-2020-user_group_id' => ('Employee Group'),
										'-2030-user_title_id' => ('Employee Title'),
										'-2040-include_user_id' => ('Employee Include'),
										'-2050-exclude_user_id' => ('Employee Exclude'),
										'-2060-default_branch_id' => ('Default Branch'),
										'-2070-default_department_id' => ('Default Department'),
										'-2080-punch_branch_id' => ('Punch Branch'),
										'-2090-punch_department_id' => ('Punch Department'),

										'-4010-pay_period_time_sheet_verify_status_id' => ('TimeSheet Verification'),
										'-4020-include_no_data_rows' => ('Include Blank Records'),

										'-5000-columns' => ('Display Columns'),
										'-5010-group' => ('Group By'),
										'-5020-sub_total' => ('SubTotal By'),
										'-5030-sort' => ('Sort By'),
							   );
				break;
			case 'time_period':
				$retval = TTDate::getTimePeriodOptions();
				break;
			case 'date_columns':
				$retval = TTDate::getReportDateOptions( NULL, ('Date'), 13, TRUE );
				break;
			case 'static_columns':
				$retval = array(
										//Static Columns - Aggregate functions can't be used on these.
										'-1000-first_name' => ('First Name'),
										'-1001-middle_name' => ('Middle Name'),
										'-1002-last_name' => ('Last Name'),
										'-1005-full_name' => ('Full Name'),
										'-1030-employee_number' => ('Employee #'),
										'-1040-status' => ('Status'),
										'-1050-title' => ('Title'),
										'-1060-province' => ('Province/State'),
										'-1070-country' => ('Country'),
										'-1080-user_group' => ('Group'),
										'-1090-default_branch' => ('Default Branch'),
										'-1100-default_department' => ('Default Department'),
										'-1110-currency' => ('Currency'),
										//'-1111-current_currency' => ('Current Currency'),

										//'-1110-verified_time_sheet' => ('Verified TimeSheet'),
										//'-1120-pending_request' => ('Pending Requests'),

										'-1400-permission_control' => ('Permission Group'),
										'-1410-pay_period_schedule' => ('Pay Period Schedule'),
										'-1420-policy_group' => ('Policy Group'),

										'-1430-branch_name' => ('Branch'),
										'-1440-department_name' => ('Department'),

										//Handled in date_columns above.
										//'-1450-pay_period' => ('Pay Period'),

										'-1510-verified_time_sheet' => ('Verified TimeSheet'),
										'-1515-verified_time_sheet_date' => ('Verified TimeSheet Date'),
							   );

				$retval = array_merge( $retval, $this->getOptions('date_columns') );
				ksort($retval);
				break;
			case 'dynamic_columns':
				$retval = array(
										//Dynamic - Aggregate functions can be used

										//Take into account wage groups. However hourly_rates for the same hour type, so we need to figure out an average hourly rate for each column?
										//'-2010-hourly_rate' => ('Hourly Rate'),

										//'-2070-schedule_working' => ('Scheduled Time'),
										//'-2080-schedule_absence' => ('Scheduled Absence'),

										//'-2085-worked_days' => ('Worked Days'), //Doesn't work for this report.
										//'-2090-worked_time' => ('Worked Time'),
										//'-2100-actual_time' => ('Actual Time'),
										//'-2110-actual_time_diff' => ('Actual Time Difference'),
										//'-2130-paid_time' => ('Paid Time'),
										'-2290-regular_time' => ('Regular Time'),

										'-2500-gross_wage' => ('Gross Wage'),
										'-2530-regular_time_wage' => ('Regular Time - Wage'),
										//'-2540-actual_time_wage' => ('Actual Time Wage'),
										//'-2550-actual_time_diff_wage' => ('Actual Time Difference Wage'),

										'-2690-regular_time_hourly_rate' => ('Regular Time - Hourly Rate'),

							);

				$retval = array_merge( $retval, $this->getOptions('overtime_columns'), $this->getOptions('premium_columns'), $this->getOptions('absence_columns') );
				ksort($retval);

				break;
			case 'overtime_columns':
				//Get all Overtime policies.
				$retval = array();
				$otplf = new OverTimePolicyListFactory();
				$otplf->getByCompanyId( $this->getUserObject()->getCompany() );
				if ( $otplf->getRecordCount() > 0 ) {
					foreach( $otplf as $otp_obj ) {
						$retval['-2291-over_time_policy-'.$otp_obj->getId()] = $otp_obj->getName();
						$retval['-2591-over_time_policy-'.$otp_obj->getId().'_wage'] = $otp_obj->getName() .' '. ('- Wage');
						$retval['-2691-over_time_policy-'.$otp_obj->getId().'_hourly_rate'] = $otp_obj->getName() .' '. ('- Hourly Rate');
					}
				}
				break;
			case 'premium_columns':
				$retval = array();
				//Get all Premium policies.
				$pplf = new PremiumPolicyListFactory();
				$pplf->getByCompanyId( $this->getUserObject()->getCompany() );
				if ( $pplf->getRecordCount() > 0 ) {
					foreach( $pplf as $pp_obj ) {
						$retval['-2291-premium_policy-'.$pp_obj->getId()] = $pp_obj->getName();
						$retval['-2591-premium_policy-'.$pp_obj->getId().'_wage'] = $pp_obj->getName() .' '. ('- Wage');
						$retval['-2691-premium_policy-'.$pp_obj->getId().'_hourly_rate'] = $pp_obj->getName() .' '. ('- Hourly Rate');
					}
				}
				break;
			case 'absence_columns':
				$retval = array();
				//Get all Absence Policies.
				$aplf = new AbsencePolicyListFactory();
				$aplf->getByCompanyId( $this->getUserObject()->getCompany() );
				if ( $aplf->getRecordCount() > 0 ) {
					foreach( $aplf as $ap_obj ) {
						$retval['-2291-absence_policy-'.$ap_obj->getId()] = $ap_obj->getName();
						if ( $ap_obj->getType() == 10 ) {
							$retval['-2591-absence_policy-'.$ap_obj->getId().'_wage'] = $ap_obj->getName() .' '. ('- Wage');
							$retval['-2691-absence_policy-'.$ap_obj->getId().'_hourly_rate'] = $ap_obj->getName() .' '. ('- Hourly Rate');
						}
					}
				}
				break;
			case 'columns':
				$retval = array_merge( $this->getOptions('static_columns'), $this->getOptions('dynamic_columns') );
				break;
			case 'column_format':
				//Define formatting function for each column.
				$columns = $this->getOptions('dynamic_columns');
				if ( is_array($columns) ) {
					foreach($columns as $column => $name ) {
						if ( strpos($column, '_wage') !== FALSE OR strpos($column, '_hourly_rate') !== FALSE ) {
							$retval[$column] = 'currency';
						} elseif ( strpos($column, '_time') OR strpos($column, '_policy') ) {
							$retval[$column] = 'time_unit';
						}
					}
				}
				$retval['verified_time_sheet_date'] = 'time_stamp';
				break;
			case 'aggregates':
				$retval = array();
				$dynamic_columns = array_keys( Misc::trimSortPrefix( $this->getOptions('dynamic_columns') ) );
				if ( is_array($dynamic_columns ) ) {
					foreach( $dynamic_columns as $column ) {
						switch ( $column ) {
							default:
								if ( strpos($column, '_hourly_rate') !== FALSE ) {
									$retval[$column] = 'avg';
								} else {
									$retval[$column] = 'sum';
								}
						}
					}
				}
				$retval['verified_time_sheet'] = 'first';
				$retval['verified_time_sheet_date'] = 'first';
				break;
			case 'templates':
				$retval = array(
										'-1010-by_employee+regular' => ('Regular Time by Employee'),
										'-1020-by_employee+overtime' => ('Overtime by Employee'),
										'-1030-by_employee+premium' => ('Premium Time by Employee'),
										'-1040-by_employee+absence' => ('Absence Time by Employee'),
										'-1050-by_employee+regular+overtime+premium+absence' => ('All Time by Employee'),

										'-1060-by_employee+regular+regular_wage' => ('Regular Time+Wage by Employee'),
										'-1070-by_employee+overtime+overtime_wage' => ('Overtime+Wage by Employee'),
										'-1080-by_employee+premium+premium_wage' => ('Premium Time+Wage by Employee'),
										'-1090-by_employee+absence+absence_wage' => ('Absence Time+Wage by Employee'),
										'-1100-by_employee+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => ('All Time+Wage by Employee'),

										'-1110-by_date_by_full_name+regular+regular_wage' => ('Regular Time+Wage by Date/Employee'),
										'-1120-by_date_by_full_name+overtime+overtime_wage' => ('Overtime+Wage by Date/Employee'),
										'-1130-by_date_by_full_name+premium+premium_wage' => ('Premium Time+Wage by Date/Employee'),
										'-1140-by_date_by_full_name+absence+absence_wage' => ('Absence Time+Wage by Date/Employee'),
										'-1150-by_date_by_full_name+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => ('All Time+Wage by Date/Employee'),

										'-1160-by_full_name_by_date+regular+regular_wage' => ('Regular Time+Wage by Employee/Date'),
										'-1170-by_full_name_by_date+overtime+overtime_wage' => ('Overtime+Wage by Employee/Date'),
										'-1180-by_full_name_by_date+premium+premium_wage' => ('Premium Time+Wage by Employee/Date'),
										'-1190-by_full_name_by_date+absence+absence_wage' => ('Absence Time+Wage by Employee/Date'),
										'-1200-by_full_name_by_date+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => ('All Time+Wage by Employee/Date'),

										'-1210-by_branch+regular+regular_wage' => ('Regular Time+Wage by Branch'),
										'-1220-by_branch+overtime+overtime_wage' => ('Overtime+Wage by Branch'),
										'-1230-by_branch+premium+premium_wage' => ('Premium Time+Wage by Branch'),
										'-1240-by_branch+absence+absence_wage' => ('Absence Time+Wage by Branch'),
										'-1250-by_branch+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => ('All Time+Wage by Branch'),

										'-1260-by_department+regular+regular_wage' => ('Regular Time+Wage by Department'),
										'-1270-by_department+overtime+overtime_wage' => ('Overtime+Wage by Department'),
										'-1280-by_department+premium+premium_wage' => ('Premium Time+Wage by Department'),
										'-1290-by_department+absence+absence_wage' => ('Absence Time+Wage by Department'),
										'-1300-by_department+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => ('All Time+Wage by Department'),

										'-1310-by_branch_by_department+regular+regular_wage' => ('Regular Time+Wage by Branch/Department'),
										'-1320-by_branch_by_department+overtime+overtime_wage' => ('Overtime+Wage by Branch/Department'),
										'-1330-by_branch_by_department+premium+premium_wage' => ('Premium Time+Wage by Branch/Department'),
										'-1340-by_branch_by_department+absence+absence_wage' => ('Absence Time+Wage by Branch/Department'),
										'-1350-by_branch_by_department+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => ('All Time+Wage by Branch/Department'),

										'-1360-by_pay_period+regular+regular_wage' => ('Regular Time+Wage by Pay Period'),
										'-1370-by_pay_period+overtime+overtime_wage' => ('Overtime+Wage by Pay Period'),
										'-1380-by_pay_period+premium+premium_wage' => ('Premium Time+Wage by Pay Period'),
										'-1390-by_pay_period+absence+absence_wage' => ('Absence Time+Wage by Pay Period'),
										'-1400-by_pay_period+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => ('All Time+Wage by Pay Period'),

										'-1410-by_pay_period_by_employee+regular+regular_wage' => ('Regular Time+Wage by Pay Period/Employee'),
										'-1420-by_pay_period_by_employee+overtime+overtime_wage' => ('Overtime+Wage by Pay Period/Employee'),
										'-1430-by_pay_period_by_employee+premium+premium_wage' => ('Premium Time+Wage by Pay Period/Employee'),
										'-1440-by_pay_period_by_employee+absence+absence_wage' => ('Absence Time+Wage by Pay Period/Employee'),
										'-1450-by_pay_period_by_employee+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => ('All Time+Wage by Pay Period/Employee'),

										'-1460-by_pay_period_by_branch+regular+regular_wage' => ('Regular Time+Wage by Pay Period/Branch'),
										'-1470-by_pay_period_by_branch+overtime+overtime_wage' => ('Overtime+Wage by Pay Period/Branch'),
										'-1480-by_pay_period_by_branch+premium+premium_wage' => ('Premium Time+Wage by Pay Period/Branch'),
										'-1490-by_pay_period_by_branch+absence+absence_wage' => ('Absence Time+Wage by Pay Period/Branch'),
										'-1500-by_pay_period_by_branch+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => ('All Time+Wage by Pay Period/Branch'),

										'-1510-by_pay_period_by_department+regular+regular_wage' => ('Regular Time+Wage by Pay Period/Department'),
										'-1520-by_pay_period_by_department+overtime+overtime_wage' => ('Overtime+Wage by Pay Period/Department'),
										'-1530-by_pay_period_by_department+premium+premium_wage' => ('Premium Time+Wage by Pay Period/Department'),
										'-1540-by_pay_period_by_department+absence+absence_wage' => ('Absence Time+Wage by Pay Period/Department'),
										'-1550-by_pay_period_by_department+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => ('All Time+Wage by Pay Period/Department'),

										'-1560-by_pay_period_by_branch_by_department+regular+regular_wage' => ('Regular Time+Wage by Pay Period/Branch/Department'),
										'-1570-by_pay_period_by_branch_by_department+overtime+overtime_wage' => ('Overtime+Wage by Pay Period/Branch/Department'),
										'-1580-by_pay_period_by_branch_by_department+premium+premium_wage' => ('Premium Time+Wage by Pay Period/Branch/Department'),
										'-1590-by_pay_period_by_branch_by_department+absence+absence_wage' => ('Absence Time+Wage by Pay Period/Branch/Department'),
										'-1600-by_pay_period_by_branch_by_department+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => ('All Time+Wage by Pay Period/Branch/Department'),

										'-1610-by_employee_by_pay_period+regular+regular_wage' => ('Regular Time+Wage by Employee/Pay Period'),
										'-1620-by_employee_by_pay_period+overtime+overtime_wage' => ('Overtime+Wage by Employee/Pay Period'),
										'-1630-by_employee_by_pay_period+premium+premium_wage' => ('Premium Time+Wage by Employee/Pay Period'),
										'-1640-by_employee_by_pay_period+absence+absence_wage' => ('Absence Time+Wage by Employee/Pay Period'),
										'-1650-by_employee_by_pay_period+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => ('All Time+Wage by Employee/Pay Period'),

										'-1660-by_branch_by_pay_period+regular+regular_wage' => ('Regular Time+Wage by Branch/Pay Period'),
										'-1670-by_branch_by_pay_period+overtime+overtime_wage' => ('Overtime+Wage by Branch/Pay Period'),
										'-1680-by_branch_by_pay_period+premium+premium_wage' => ('Premium Time+Wage by Branch/Pay Period'),
										'-1690-by_branch_by_pay_period+absence+absence_wage' => ('Absence Time+Wage by Branch/Pay Period'),
										'-1700-by_branch_by_pay_period+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => ('All Time+Wage by Pay Branch/Pay Period'),

										'-1810-by_department_by_pay_period+regular+regular_wage' => ('Regular Time+Wage by Department/Pay Period'),
										'-1820-by_department_by_pay_period+overtime+overtime_wage' => ('Overtime+Wage by Pay Department/Pay Period'),
										'-1830-by_department_by_pay_period+premium+premium_wage' => ('Premium Time+Wage by Pay Department/Pay Period'),
										'-1840-by_department_by_pay_period+absence+absence_wage' => ('Absence Time+Wage by Pay Department/Pay Period'),
										'-1850-by_department_by_pay_period+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => ('All Time+Wage by Pay Department/Pay Period'),

										'-1860-by_branch_by_department_by_pay_period+regular+regular_wage' => ('Regular Time+Wage by Branch/Department/Pay Period'),
										'-1870-by_branch_by_department_by_pay_period+overtime+overtime_wage' => ('Overtime+Wage by Pay Branch/Department/Pay Period'),
										'-1880-by_branch_by_department_by_pay_period+premium+premium_wage' => ('Premium Time+Wage by Pay Branch/Department/Pay Period'),
										'-1890-by_branch_by_department_by_pay_period+absence+absence_wage' => ('Absence Time+Wage by Pay Branch/Department/Pay Period'),
										'-1900-by_branch_by_department_by_pay_period+regular+regular_wage+overtime+overtime_wage+premium+premium_wage+absence+absence_wage' => ('All Time+Wage by Branch/Department/Pay Period'),

										'-3000-by_pay_period_by_employee+verified_time_sheet' => ('Timesheet Verification by Pay Period/Employee'),
										'-3010-by_verified_time_sheet_by_pay_period_by_employee+verified_time_sheet' => ('Timesheet Verification by Verification/Pay Period/Employee'),
							   );

				break;
			case 'template_config':
				$template = strtolower( Misc::trimSortPrefix( $params['template'] ) );
				if ( isset($template) AND $template != '' ) {
					switch( $template ) {
						case 'by_pay_period_by_employee+verified_time_sheet':
							$retval['-1010-time_period']['time_period'] = 'last_pay_period';

							$retval['columns'][] = 'pay_period';
							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';
							$retval['columns'][] = 'verified_time_sheet';
							$retval['columns'][] = 'verified_time_sheet_date';

							$retval['group'][] = 'pay_period';
							$retval['group'][] = 'first_name';
							$retval['group'][] = 'last_name';

							$retval['sort'][] = array('pay_period' => 'asc');
							$retval['sort'][] = array('verified_time_sheet' => 'desc');
							$retval['sort'][] = array('verified_time_sheet_date' => 'asc');
							break;
						case 'by_verified_time_sheet_by_pay_period_by_employee+verified_time_sheet':
							$retval['-1010-time_period']['time_period'] = 'last_pay_period';

							$retval['columns'][] = 'verified_time_sheet';
							$retval['columns'][] = 'pay_period';
							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';
							$retval['columns'][] = 'verified_time_sheet_date';

							$retval['group'][] = 'verified_time_sheet';
							$retval['group'][] = 'pay_period';
							$retval['group'][] = 'first_name';
							$retval['group'][] = 'last_name';

							$retval['sort'][] = array('verified_time_sheet' => 'desc');
							$retval['sort'][] = array('pay_period' => 'asc');
							$retval['sort'][] = array('verified_time_sheet_date' => 'asc');
							break;

						default:
							Debug::Text(' Parsing template name: '. $template, __FILE__, __LINE__, __METHOD__,10);
							$retval['-1010-time_period']['time_period'] = 'last_pay_period';

							//Parse template name, and use the keywords separated by '+' to determine settings.
							$template_keywords = explode('+', $template );
							if ( is_array($template_keywords) ) {
								foreach( $template_keywords as $template_keyword ) {
									Debug::Text(' Keyword: '. $template_keyword, __FILE__, __LINE__, __METHOD__,10);

									switch( $template_keyword ) {
										//Columns
										case 'regular':
											$retval['columns'][] = 'regular_time';
											break;
										case 'overtime':
										case 'premium':
										case 'absence':
											$columns = Misc::trimSortPrefix( $this->getOptions( $template_keyword.'_columns') );
											if ( is_array($columns) ) {
												foreach( $columns as $column => $column_name ) {
													if ( strpos( $column, '_wage') === FALSE AND strpos( $column, '_hourly_rate') === FALSE ) {
														$retval['columns'][] = $column;
													}
												}
											}
											break;

										case 'regular_wage':
											$retval['columns'][] = 'regular_time_wage';
											break;
										case 'overtime_wage':
										case 'premium_wage':
										case 'absence_wage':
											$columns = Misc::trimSortPrefix( $this->getOptions( str_replace('_wage', '', $template_keyword).'_columns' ) );
											if ( is_array($columns) ) {
												foreach( $columns as $column => $column_name ) {
													if ( strpos( $column, '_wage') !== FALSE ) {
														$retval['columns'][] = $column;
													}
												}
											}
											break;
										//Filter
										//Group By
										//SubTotal
										//Sort
										case 'by_employee':
											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';

											$retval['group'][] = 'first_name';
											$retval['group'][] = 'last_name';

											$retval['sort'][] = array('last_name' => 'asc');
											$retval['sort'][] = array('first_name' => 'asc');
											break;
										case 'by_branch':
											$retval['columns'][] = 'branch_name';

											$retval['group'][] = 'branch_name';

											$retval['sort'][] = array('branch_name' => 'asc');
											break;
										case 'by_department':
											$retval['columns'][] = 'department_name';

											$retval['group'][] = 'department_name';

											$retval['sort'][] = array('department_name' => 'asc');
											break;
										case 'by_branch_by_department':
											$retval['columns'][] = 'branch_name';
											$retval['columns'][] = 'department_name';

											$retval['group'][] = 'branch_name';
											$retval['group'][] = 'department_name';

											$retval['sub_total'][] = 'branch_name';

											$retval['sort'][] = array('branch_name' => 'asc');
											$retval['sort'][] = array('department_name' => 'asc');
											break;
										case 'by_pay_period':
											$retval['columns'][] = 'pay_period';

											$retval['group'][] = 'pay_period';

											$retval['sort'][] = array('pay_period' => 'asc');
											break;
										case 'by_pay_period_by_employee':
											$retval['columns'][] = 'pay_period';
											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';

											$retval['group'][] = 'pay_period';
											$retval['group'][] = 'first_name';
											$retval['group'][] = 'last_name';

											$retval['sub_total'][] = 'pay_period';

											$retval['sort'][] = array('pay_period' => 'asc');
											$retval['sort'][] = array('last_name' => 'asc');
											$retval['sort'][] = array('first_name' => 'asc');
											break;
										case 'by_pay_period_by_branch':
											$retval['columns'][] = 'pay_period';
											$retval['columns'][] = 'branch_name';

											$retval['group'][] = 'pay_period';
											$retval['group'][] = 'branch_name';

											$retval['sub_total'][] = 'pay_period';

											$retval['sort'][] = array('pay_period' => 'asc');
											$retval['sort'][] = array('branch_name' => 'asc');
											break;
										case 'by_pay_period_by_department':
											$retval['columns'][] = 'pay_period';
											$retval['columns'][] = 'department_name';

											$retval['group'][] = 'pay_period';
											$retval['group'][] = 'department_name';

											$retval['sub_total'][] = 'pay_period';

											$retval['sort'][] = array('pay_period' => 'asc');
											$retval['sort'][] = array('department_name' => 'asc');
											break;
										case 'by_pay_period_by_branch_by_department':
											$retval['columns'][] = 'pay_period';
											$retval['columns'][] = 'branch_name';
											$retval['columns'][] = 'department_name';

											$retval['group'][] = 'pay_period';
											$retval['group'][] = 'branch_name';
											$retval['group'][] = 'department_name';

											$retval['sub_total'][] = 'pay_period';
											$retval['sub_total'][] = 'branch_name';

											$retval['sort'][] = array('pay_period' => 'asc');
											$retval['sort'][] = array('branch_name' => 'asc');
											$retval['sort'][] = array('department_name' => 'asc');
											break;
										case 'by_employee_by_pay_period':
											$retval['columns'][] = 'full_name';
											$retval['columns'][] = 'pay_period';

											$retval['group'][] = 'full_name';
											$retval['group'][] = 'pay_period';

											$retval['sub_total'][] = 'full_name';

											$retval['sort'][] = array('full_name' => 'asc');
											$retval['sort'][] = array('pay_period' => 'asc');
											break;
										case 'by_branch_by_pay_period':
											$retval['columns'][] = 'branch_name';
											$retval['columns'][] = 'pay_period';

											$retval['group'][] = 'branch_name';
											$retval['group'][] = 'pay_period';

											$retval['sub_total'][] = 'branch_name';

											$retval['sort'][] = array('branch_name' => 'asc');
											$retval['sort'][] = array('pay_period' => 'asc');
											break;
										case 'by_department_by_pay_period':
											$retval['columns'][] = 'department_name';
											$retval['columns'][] = 'pay_period';

											$retval['group'][] = 'department_name';
											$retval['group'][] = 'pay_period';

											$retval['sub_total'][] = 'department_name';

											$retval['sort'][] = array('department_name' => 'asc');
											$retval['sort'][] = array('pay_period' => 'asc');
											break;
										case 'by_branch_by_department_by_pay_period':
											$retval['columns'][] = 'branch_name';
											$retval['columns'][] = 'department_name';
											$retval['columns'][] = 'pay_period';

											$retval['group'][] = 'branch_name';
											$retval['group'][] = 'department_name';
											$retval['group'][] = 'pay_period';

											$retval['sub_total'][] = 'branch_name';
											$retval['sub_total'][] = 'department_name';

											$retval['sort'][] = array('branch_name' => 'asc');
											$retval['sort'][] = array('department_name' => 'asc');
											$retval['sort'][] = array('pay_period' => 'asc');
											break;
										case 'by_date_by_full_name':
											$retval['columns'][] = 'date_stamp';
											$retval['columns'][] = 'full_name';

											$retval['group'][] = 'date_stamp';
											$retval['group'][] = 'full_name';

											$retval['sub_total'][] = 'date_stamp';

											$retval['sort'][] = array('date_stamp' => 'asc');
											$retval['sort'][] = array('full_name' => 'asc');
											break;
										case 'by_full_name_by_date':
											$retval['columns'][] = 'full_name';
											$retval['columns'][] = 'date_stamp';

											$retval['group'][] = 'full_name';
											$retval['group'][] = 'date_stamp';

											$retval['sub_total'][] = 'full_name';

											$retval['sort'][] = array('full_name' => 'asc');
											$retval['sort'][] = array('date_stamp' => 'asc');
											break;
									}
								}
							}
							break;
					}
				}

				//Set the template dropdown as well.
				$retval['-1000-template'] = $template;

				//Add sort prefixes so Flex can maintain order.
				if ( isset($retval['filter']) ) {
					$retval['-5000-filter'] = $retval['filter'];
					unset($retval['filter']);
				}
				if ( isset($retval['columns']) ) {
					$retval['-5010-columns'] = $retval['columns'];
					unset($retval['columns']);
				}
				if ( isset($retval['group']) ) {
					$retval['-5020-group'] = $retval['group'];
					unset($retval['group']);
				}
				if ( isset($retval['sub_total']) ) {
					$retval['-5030-sub_total'] = $retval['sub_total'];
					unset($retval['sub_total']);
				}
				if ( isset($retval['sort']) ) {
					$retval['-5040-sort'] = $retval['sort'];
					unset($retval['sort']);
				}
				Debug::Arr($retval, ' Template Config for: '. $template, __FILE__, __LINE__, __METHOD__,10);

				break;
			default:
				//Call report parent class options function for options valid for all reports.
				$retval = $this->__getOptions( $name );
				break;
		}

		return $retval;
	}

	function getPolicyHourlyRates() {
		//Take into account wage groups!
		$policy_rates = array();

		//Get all Overtime policies.
		$otplf = new OverTimePolicyListFactory();
		$otplf->getByCompanyId( $this->getUserObject()->getCompany() );
		if ( $otplf->getRecordCount() > 0 ) {
			foreach( $otplf as $otp_obj ) {
				Debug::Text('Over Time Policy ID: '. $otp_obj->getId() .' Rate: '. $otp_obj->getRate() , __FILE__, __LINE__, __METHOD__,10);
				$policy_rates['over_time_policy-'.$otp_obj->getId()] = $otp_obj;
			}
		}

		//Get all Premium policies.
		$pplf = new PremiumPolicyListFactory();
		$pplf->getByCompanyId( $this->getUserObject()->getCompany() );
		if ( $pplf->getRecordCount() > 0 ) {
			foreach( $pplf as $pp_obj ) {
				$policy_rates['premium_policy-'.$pp_obj->getId()] = $pp_obj;
			}
		}

		//Get all Absence Policies.
		$aplf = new AbsencePolicyListFactory();
		$aplf->getByCompanyId( $this->getUserObject()->getCompany() );
		if ( $aplf->getRecordCount() > 0 ) {
			foreach( $aplf as $ap_obj ) {
				$policy_rates['absence_policy-'.$ap_obj->getId()] = $ap_obj;
			}
		}

		return $policy_rates;
	}

	//Get raw data for report
	function _getData( $format = NULL ) {
		$this->tmp_data = array('user_date_total' => array(), 'user' => array(), 'default_branch' => array(), 'default_department' => array(), 'branch' => array(), 'department' => array(), 'verified_timesheet' => array() );

		$columns = $this->getColumnConfig();
		$filter_data = $this->getFilterConfig();
		$policy_hourly_rates = $this->getPolicyHourlyRates();

		if ( $this->getPermissionObject()->Check('punch','view') == FALSE OR $this->getPermissionObject()->Check('wage','view') == FALSE ) {
			$hlf = new HierarchyListFactory();
			$permission_children_ids = $wage_permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $this->getUserObject()->getCompany(), $this->getUserObject()->getID() );
			//Debug::Arr($permission_children_ids,'Permission Children Ids:', __FILE__, __LINE__, __METHOD__,10);
		} else {
			//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
			$permission_children_ids = array();
			$wage_permission_children_ids = array();
		}
		if ( $this->getPermissionObject()->Check('punch','view') == FALSE ) {
			if ( $this->getPermissionObject()->Check('punch','view_child') == FALSE ) {
				$permission_children_ids = array();
			}
			if ( $this->getPermissionObject()->Check('punch','view_own') ) {
				$permission_children_ids[] = $this->getUserObject()->getID();
			}

			$filter_data['permission_children_ids'] = $permission_children_ids;
		}
		//Get Wage Permission Hierarchy Children first, as this can be used for viewing, or editing.
		if ( $this->getPermissionObject()->Check('wage','view') == TRUE ) {
			$wage_permission_children_ids = TRUE;
		} elseif ( $this->getPermissionObject()->Check('wage','view') == FALSE ) {
			if ( $this->getPermissionObject()->Check('wage','view_child') == FALSE ) {
				$wage_permission_children_ids = array();
			}
			if ( $this->getPermissionObject()->Check('wage','view_own') ) {
				$wage_permission_children_ids[] = $this->getUserObject()->getID();
			}
		}
		//Debug::Text(' Permission Children: '. count($permission_children_ids) .' Wage Children: '. count($wage_permission_children_ids), __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($permission_children_ids, 'Permission Children: '. count($permission_children_ids), __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($wage_permission_children_ids, 'Wage Children: '. count($wage_permission_children_ids), __FILE__, __LINE__, __METHOD__,10);

		$pay_period_ids = array();

		$udtlf = new UserDateTotalListFactory();
		$udtlf->getTimesheetSummaryReportByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text(' Total Rows: '. $udtlf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), $udtlf->getRecordCount(), NULL, ('Retrieving Data...') );
		if ( $udtlf->getRecordCount() > 0 ) {
			foreach ( $udtlf as $key => $udt_obj ) {
				$pay_period_ids[$udt_obj->getColumn('pay_period_id')] = TRUE;

				$user_id = $udt_obj->getColumn('user_id');

				$date_stamp = TTDate::strtotime( $udt_obj->getColumn('date_stamp') );
				$status_id = $udt_obj->getColumn('status_id');
				$type_id = $udt_obj->getColumn('type_id');

				//Can we get rid of Worked and Paid time to simplify things? People have a hard time figuring out what these are anyways for reports.
				//Paid time doesn't belong to a branch/department, so if we try to group by branch/department there will
				//always be a blank line showing just the paid time. So if they don't want to display paid time, just exclude it completely.
				$column = $udt_obj->getTimeCategory();
				if ( $column == 'paid_time' OR $column == 'worked_time' ) {
					$column = NULL;
				}

				//Debug::Text('Column: '. $column .' Total Time: '. $udt_obj->getColumn('total_time') .' Status: '. $status_id .' Type: '. $type_id .' Rate: '. $udt_obj->getColumn( 'hourly_rate' ), __FILE__, __LINE__, __METHOD__,10);
				if ( ( isset($filter_data['include_no_data_rows']) AND $filter_data['include_no_data_rows'] == 1 )
						OR ( $date_stamp != '' AND $column != '' AND $udt_obj->getColumn('total_time') != 0 )  ) {

					$hourly_rate = 0;
					if ( $wage_permission_children_ids === TRUE OR in_array( $user_id, $wage_permission_children_ids) ) {
						$hourly_rate = $udt_obj->getColumn( 'hourly_rate' );
					}
					if ( isset($policy_hourly_rates[$column]) AND is_object($policy_hourly_rates[$column]) ) {
						$hourly_rate = $policy_hourly_rates[$column]->getHourlyRate( $hourly_rate );
					}

					if ( !isset($this->tmp_data['user_date_total'][$user_id][$date_stamp]) ) {
						$this->tmp_data['user_date_total'][$user_id][$date_stamp] = array(
															'branch_id' => $udt_obj->getColumn('branch_id'),
															'department_id' => $udt_obj->getColumn('department_id'),
															'pay_period_start_date' => strtotime( $udt_obj->getColumn('pay_period_start_date') ),
															'pay_period_end_date' => strtotime( $udt_obj->getColumn('pay_period_end_date') ),
															'pay_period_transaction_date' => strtotime( $udt_obj->getColumn('pay_period_transaction_date') ),
															'pay_period' => strtotime( $udt_obj->getColumn('pay_period_transaction_date') ),
															'pay_period_id' => $udt_obj->getColumn('pay_period_id'),
															);
					}

					//Add time/wage and calculate average hourly rate.
					if ( isset($this->tmp_data['user_date_total'][$user_id][$date_stamp][$column]) ) {
						$this->tmp_data['user_date_total'][$user_id][$date_stamp][$column] += $udt_obj->getColumn('total_time');
					} else {
						$this->tmp_data['user_date_total'][$user_id][$date_stamp][$column] = $udt_obj->getColumn('total_time');
					}

					if ( isset($this->tmp_data['user_date_total'][$user_id][$date_stamp][$column.'_wage']) ) {
						$this->tmp_data['user_date_total'][$user_id][$date_stamp][$column.'_wage'] += bcmul( bcdiv($udt_obj->getColumn('total_time'), 3600), $hourly_rate );
					} else {
						$this->tmp_data['user_date_total'][$user_id][$date_stamp][$column.'_wage'] = bcmul( bcdiv($udt_obj->getColumn('total_time'), 3600), $hourly_rate );
					}

					if ( $this->tmp_data['user_date_total'][$user_id][$date_stamp][$column] != 0 ) {
						$this->tmp_data['user_date_total'][$user_id][$date_stamp][$column.'_hourly_rate'] = bcdiv($this->tmp_data['user_date_total'][$user_id][$date_stamp][$column.'_wage'], bcdiv($this->tmp_data['user_date_total'][$user_id][$date_stamp][$column], 3600) );
					} else {
						$this->tmp_data['user_date_total'][$user_id][$date_stamp][$column.'_hourly_rate'] = $hourly_rate;
					}

					if ( isset($this->tmp_data['user_date_total'][$user_id][$date_stamp]['gross_wage']) ) {
						$this->tmp_data['user_date_total'][$user_id][$date_stamp]['gross_wage'] += $this->tmp_data['user_date_total'][$user_id][$date_stamp][$column.'_wage'];
					} else {
						$this->tmp_data['user_date_total'][$user_id][$date_stamp]['gross_wage'] = $this->tmp_data['user_date_total'][$user_id][$date_stamp][$column.'_wage'];
					}

/*
					if ( isset($this->tmp_data['user_date_total'][$user_id][$date_stamp]) ) {
						//Add time/wage and calculate average hourly rate.
						if ( isset($this->tmp_data['user_date_total'][$user_id][$date_stamp][$column]) ) {
							$this->tmp_data['user_date_total'][$user_id][$date_stamp][$column] += $udt_obj->getColumn('total_time');
						} else {
							$this->tmp_data['user_date_total'][$user_id][$date_stamp][$column] = $udt_obj->getColumn('total_time');
						}

						if ( isset($this->tmp_data['user_date_total'][$user_id][$date_stamp][$column.'_wage']) ) {
							$this->tmp_data['user_date_total'][$user_id][$date_stamp][$column.'_wage'] += bcmul( bcdiv($udt_obj->getColumn('total_time'), 3600), $hourly_rate );
						} else {
							$this->tmp_data['user_date_total'][$user_id][$date_stamp][$column.'_wage'] = bcmul( bcdiv($udt_obj->getColumn('total_time'), 3600), $hourly_rate );
						}

						if ( $this->tmp_data['user_date_total'][$user_id][$date_stamp][$column] != 0 ) {
							$this->tmp_data['user_date_total'][$user_id][$date_stamp][$column.'_hourly_rate'] = bcdiv($this->tmp_data['user_date_total'][$user_id][$date_stamp][$column.'_wage'], bcdiv($this->tmp_data['user_date_total'][$user_id][$date_stamp][$column], 3600) );
						} else {
							$this->tmp_data['user_date_total'][$user_id][$date_stamp][$column.'_hourly_rate'] = $hourly_rate;
						}
					} else {
						$this->tmp_data['user_date_total'][$user_id][$date_stamp] = array(
															'branch_id' => $udt_obj->getColumn('branch_id'),
															'department_id' => $udt_obj->getColumn('department_id'),
															'pay_period_start_date' => strtotime( $udt_obj->getColumn('pay_period_start_date') ),
															'pay_period_end_date' => strtotime( $udt_obj->getColumn('pay_period_end_date') ),
															'pay_period_transaction_date' => strtotime( $udt_obj->getColumn('pay_period_transaction_date') ),
															'pay_period' => strtotime( $udt_obj->getColumn('pay_period_transaction_date') ),
															'pay_period_id' => $udt_obj->getColumn('pay_period_id'),

															$column => $udt_obj->getColumn('total_time'),
															$column.'_hourly_rate' => $hourly_rate,
															$column.'_wage' => bcmul( bcdiv($udt_obj->getColumn('total_time'), 3600), $hourly_rate )
															);
					}

					if ( isset($this->tmp_data['user_date_total'][$user_id][$date_stamp]['gross_wage']) ) {
						$this->tmp_data['user_date_total'][$user_id][$date_stamp]['gross_wage'] += $this->tmp_data['user_date_total'][$user_id][$date_stamp][$column.'_wage'];
					} else {
						$this->tmp_data['user_date_total'][$user_id][$date_stamp]['gross_wage'] = $this->tmp_data['user_date_total'][$user_id][$date_stamp][$column.'_wage'];
					}
*/
					unset($hourly_rate);
				}

				$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
			}
		}
		//Debug::Arr($this->tmp_data['user_date_total'], 'User Date Total Raw Data: ', __FILE__, __LINE__, __METHOD__,10);

		//Get user data for joining.
		$ulf = new UserListFactory();
		$ulf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text(' User Total Rows: '. $ulf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), $ulf->getRecordCount(), NULL, ('Retrieving Data...') );
		foreach ( $ulf as $key => $u_obj ) {
			$this->tmp_data['user'][$u_obj->getId()] = (array)$u_obj->getObjectAsArray( $this->getColumnConfig() );
			$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
		}
		//Debug::Arr($this->tmp_data['user'], 'User Raw Data: ', __FILE__, __LINE__, __METHOD__,10);

		$blf = new BranchListFactory();
		$blf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text(' Branch Total Rows: '. $blf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), $blf->getRecordCount(), NULL, ('Retrieving Data...') );
		foreach ( $blf as $key => $b_obj ) {
			$this->tmp_data['default_branch'][$b_obj->getId()] = Misc::addKeyPrefix( 'default_branch_', (array)$b_obj->getObjectAsArray( array('id' => TRUE, 'name' => TRUE, 'manual_id' => TRUE, 'other_id1' => TRUE, 'other_id2' => TRUE, 'other_id3' => TRUE, 'other_id4' => TRUE, 'other_id5' => TRUE ) ) );
			$this->tmp_data['branch'][$b_obj->getId()] = Misc::addKeyPrefix( 'branch_', (array)$b_obj->getObjectAsArray( array('id' => TRUE, 'name' => TRUE, 'manual_id' => TRUE, 'other_id1' => TRUE, 'other_id2' => TRUE, 'other_id3' => TRUE, 'other_id4' => TRUE, 'other_id5' => TRUE ) ) );
			$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
		}
		//Debug::Arr($this->tmp_data['default_branch'], 'Default Branch Raw Data: ', __FILE__, __LINE__, __METHOD__,10);

		$dlf = new DepartmentListFactory();
		$dlf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text(' Department Total Rows: '. $blf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), $dlf->getRecordCount(), NULL, ('Retrieving Data...') );
		foreach ( $dlf as $key => $d_obj ) {
			$this->tmp_data['default_department'][$d_obj->getId()] = Misc::addKeyPrefix( 'default_department_', (array)$d_obj->getObjectAsArray( array('id' => TRUE, 'name' => TRUE, 'manual_id' => TRUE, 'other_id1' => TRUE, 'other_id2' => TRUE, 'other_id3' => TRUE, 'other_id4' => TRUE, 'other_id5' => TRUE ) ) );
			$this->tmp_data['department'][$d_obj->getId()] = Misc::addKeyPrefix( 'department_', (array)$d_obj->getObjectAsArray( array('id' => TRUE, 'name' => TRUE, 'manual_id' => TRUE, 'other_id1' => TRUE, 'other_id2' => TRUE, 'other_id3' => TRUE, 'other_id4' => TRUE, 'other_id5' => TRUE ) ) );
			$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
		}
		//Debug::Arr($this->tmp_data['default_department'], 'Default Department Raw Data: ', __FILE__, __LINE__, __METHOD__,10);

		//Get verified timesheets for all pay periods considered in report.
		$pay_period_ids = array_keys( $pay_period_ids );
		if ( isset($pay_period_ids) AND count($pay_period_ids) > 0 ) {
			$pptsvlf = new PayPeriodTimeSheetVerifyListFactory();
			$pptsvlf->getByPayPeriodIdAndCompanyId( $pay_period_ids, $this->getUserObject()->getCompany() );
			if ( $pptsvlf->getRecordCount() > 0 ) {
				foreach( $pptsvlf as $pptsv_obj ) {
					$this->tmp_data['verified_timesheet'][$pptsv_obj->getUser()][$pptsv_obj->getPayPeriod()] = array(
																									'status' => $pptsv_obj->getVerificationStatusShortDisplay(),
																									'created_date' => $pptsv_obj->getCreatedDate(),
																									);
				}
			}
		}

		//Debug::Arr($this->tmp_data, 'TMP Data: ', __FILE__, __LINE__, __METHOD__,10);
		return TRUE;
	}

	//PreProcess data such as calculating additional columns from raw data etc...
	function _preProcess() {
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), count($this->tmp_data['user_date_total']), NULL, ('Pre-Processing Data...') );

		//Merge time data with user data
		$key=0;
		if ( isset($this->tmp_data['user_date_total']) ) {
			foreach( $this->tmp_data['user_date_total'] as $user_id => $level_1 ) {
				if ( isset($this->tmp_data['user'][$user_id]) ) {
					foreach( $level_1 as $date_stamp => $row ) {
						$date_columns = TTDate::getReportDates( NULL, $date_stamp, FALSE, $this->getUserObject(), array('pay_period_start_date' => $row['pay_period_start_date'], 'pay_period_end_date' => $row['pay_period_end_date'], 'pay_period_transaction_date' => $row['pay_period_transaction_date']) );
						$processed_data  = array(
												//'branch_id' => $branch_id,
												//'department_id' => $department_id,
												//'pay_period' => array('sort' => $row['pay_period_start_date'], 'display' => TTDate::getDate('DATE', $row['pay_period_start_date'] ).' -> '. TTDate::getDate('DATE', $row['pay_period_end_date'] ) ),
												);

						if ( isset( $this->tmp_data['verified_timesheet'][$user_id][$row['pay_period_id']]) ) {
							$processed_data['verified_time_sheet'] = $this->tmp_data['verified_timesheet'][$user_id][$row['pay_period_id']]['status'];
							$processed_data['verified_time_sheet_date'] = $this->tmp_data['verified_timesheet'][$user_id][$row['pay_period_id']]['created_date'];
						} else {
							$processed_data['verified_time_sheet'] = ('No');
							$processed_data['verified_time_sheet_date'] = FALSE;
						}

						if ( isset($this->tmp_data['user'][$user_id]['default_branch_id']) AND isset($this->tmp_data['default_branch'][$this->tmp_data['user'][$user_id]['default_branch_id']]) ) {
							$tmp_default_branch = $this->tmp_data['default_branch'][$this->tmp_data['user'][$user_id]['default_branch_id']];
						} else {
							$tmp_default_branch = array();
						}
						if ( isset($this->tmp_data['user'][$user_id]['default_department_id']) AND isset($this->tmp_data['default_department'][$this->tmp_data['user'][$user_id]['default_department_id']]) ) {
							$tmp_default_department = $this->tmp_data['default_department'][$this->tmp_data['user'][$user_id]['default_department_id']];
						} else {
							$tmp_default_department = array();
						}

						if ( isset($this->tmp_data['branch'][$row['branch_id']]) ) {
							$tmp_branch = $this->tmp_data['branch'][$row['branch_id']];
						} else {
							$tmp_branch = array();
						}
						if ( isset($this->tmp_data['department'][$row['department_id']]) ) {
							$tmp_department = $this->tmp_data['department'][$row['department_id']];
						} else {
							$tmp_department = array();
						}

						$this->data[] = array_merge( $this->tmp_data['user'][$user_id], $tmp_default_branch, $tmp_default_department, $tmp_branch, $tmp_department, $row, $date_columns, $processed_data );

						$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
						$key++;
					}
				}
			}
			unset($this->tmp_data, $row, $date_columns, $processed_data, $level_1, $level_2, $level_3);
		}
		//Debug::Arr($this->data, 'preProcess Data: ', __FILE__, __LINE__, __METHOD__,10);

		return TRUE;
	}
        
        
	/*
	 *FL EDIT -->ADD NEW CODE FOR CREATE ARRAY TO PDF PORTRAIT
	 *
	 *FL ORIENTATION IS PORTRAIT
	 */	
    function Array2PDF($data, $columns = NULL, $current_user, $current_company, $heading = 'Time Sheet Summary Report',$filter_data)
        {
            $ignore_last_row = TRUE;
            $include_header = TRUE;
            $eol = "\n";
//            echo '<pre>';            print_r($filter_data); die;
            	$filter_header_data = array(
                                                'group_ids' => $filter_data['group_ids'],
                                                'branch_ids' => $filter_data['branch_ids'],
                                                'department_ids' => $filter_data['department_ids'],
                                                'pay_period_ids' => $filter_data['pay_period_ids']
                                        );
																					
					foreach($filter_header_data as $fh_key=>$filter_header){
						$dlf = new DepartmentListFactory();
						if($fh_key == 'department_ids'){
							foreach ($filter_header as $dep_id) { 
								$department_list[] = $dlf->getNameById($dep_id); 
							}
							$dep_strng = implode(', ', $department_list);
						}
								
						$blf = new BranchListFactory(); 
						if($fh_key == 'branch_ids'){
							foreach ($filter_header as $br_id) { 
								$branch_list[] = $blf->getNameById($br_id); 
							}
							$br_strng = implode(', ', $branch_list);
						}
                        $br_strng =  $blf->getNameById($br_id); //eranda add code dynamic header data report

                        if($br_strng == null)
                        {
                                $company_name = $current_company->getName();
                                $addrss1 = $current_company->getAddress1();
                                $address2 = $current_company->getAddress2();
                                $city = $current_company->getCity();
                                $postalcode = $current_company->getPostalCode();
                        }else
                        {
                                $company_name = $blf->getNameById($br_id);
                                $addrss1 = $blf->getAddress1ById($br_id);
                                $address2 = $blf->getAddress2ById($br_id);
                                $city = $blf->getCityById($br_id);
                                $postalcode = $blf->getPostCodeById($br_id);

                        }

																					
						$uglf = new UserGroupListFactory(); 
						if($fh_key == 'group_ids'){
							foreach ($filter_header as $gr_id) {   
								$group_list[] = $uglf->getNameById($gr_id); 
							}
							$gr_strng = implode(', ', $group_list);
						}
								
					} 
					if($dep_strng==''){$dep_strng='All';} 
                                         
                $pplf = new PayPeriodListFactory();
                if(isset($filter_data['pay_period_ids'][0])){                                                              
                    $pay_period_start = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getStartDate();
                    $pay_period_end = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getEndDate();
                }else{
                    $pay_period_start = $filter_data['start_date'];
                    $pay_period_end = $filter_data['end_date'];
                
                }
                

            if ( is_array($data) AND count($data) > 0 AND is_array($columns) AND count($columns) > 0 )
            {
                
                if ( $ignore_last_row === TRUE )
                {
                    $last_row = array_pop($data);//ARSP EDIT --> THIS FUNCTION USE TO REMOVE THE LAST ELEMENT OF THEAT ARRAY
                }               
        
                
                $_SESSION['header_data'] = array( 
                                                  'image_path'   => $current_company->getLogoFileName(),
                                                  'company_name' => $company_name,
                                                  'address1'     => $addrss1,
                                                  'address2'     => $address2,
                                                  'city'         => $city,
                                                  'province'     => $current_company->getProvince(),
                                                  'postal_code'  => $postalcode, 
                                                  'heading'  => $heading, 
                                                  'group_list'  => $gr_strng, 
                                                  'department_list'  => $dep_strng, 
                                                  'branch_list'  => $br_strng, 
                                                  'payperiod_end_date'   => date('Y-M',$pay_period_end),
                                                  'line_width'  => 270, 
                    
                                                );
												
				$pdf = new TimeReportHeaderFooter();								
                
                // set default header data
                $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

                // set header and footer fonts
                $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
                $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, 52, 23);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

                // add a page
                //$pdf->AddPage();
                $pdf->AddPage('l','mm','A4');
                
                //Table border
                $pdf->setLineWidth( 0.20 );
                
                //set table position
                $adjust_x = 19;		
                
                $pdf->setXY( Misc::AdjustXY(1, $adjust_x), Misc::AdjustXY(44, $adjust_y) );
				           
                
                //TABLE CODE HERE
                        
                //Header
                // create some HTML content
                $html = '<br><br><br><table border="0" cellspacing="0" cellpadding="0" width="100%">
                		<thead>
                        <tr style="background-color:#CCCCCC;text-align:center;">';
                //$html = $html.'<td width = "3%">#</td>';
                
                $pdf->SetFont('', 'B', 10); 

                //echo '<pre>'; print_r($data); 

                foreach ($data as $key => $row) {
                    $employee_number[$key]  = $row['employee_number'];
                }

                array_multisort($employee_number, SORT_ASC, $data);  

                //echo '<pre>'; print_r($data); die; 

                foreach( $columns as $column_name )
                {                    
                    $html = $html.'<td';

                    if($column_name=='#')
                    {
                    	$html = $html.' width="10%" ';
                    }
                    if($column_name=='Full Name')
                    {
                    	$html = $html.' width="25%" ';
                    }
                    if($column_name!='#' && $column_name!='Full Name')
                    {
                    	$html = $html.' width="15%" ';
                    }/**/

                    $html = $html.' style ="text-align:center:justify;font-weight:bold;" >'.$column_name.'</td>';                      
                }
                $html=  $html.'</tr></thead>';
                
                $pdf->SetFont('','',10);

                $html=  $html.'<tbody>';  
  
                $x=1;   
                foreach( $data as $rows ) 
                {                    
                    if($x % 2 == 0)
                    {
                        $html=  $html.'<tr style ="text-align:center" bgcolor="#EEEEEE" nobr="true">';
                    }
                    else
                    {
                        $html=  $html.'<tr style ="text-align:center" bgcolor="WHITE" nobr="true">';
                    }                    
                    
                   // $html = $html.'<td>'.$x++.'</td>';
                    
                    foreach ($columns as $column_key => $column_name ) 
                    {
                        
						if($column_key=='employee_number')
	                    {
	                    	$width_w = ' width="10%" ';
	                    }
	                    if($column_key=='full_name')
	                    {
	                    	$width_w = ' style ="text-align:left;"  width="25%"';
	                    }
	                    if($column_key!='employee_number' && $column_key!='full_name')
	                    {
	                    	$width_w = ' width="15%"';
	                    }

	                    if ( isset($rows[$column_key])  && $rows[$column_key] != "")
                        {
                            $html = $html.'<td '.$width_w.' >'.$rows[$column_key].'</td>'; 
                        }
                        
                        else
                        {
                            $html = $html.'<td  '.$width_w.'>'.'--'.'</td>';
                        }
                    }
                    $html=  $html.'</tr>'; 

                    $x++;         
                }
				
				
				//SUM ROW
                $html=  $html.'<tr style ="background-color:#CCCCCC;text-align:center">';
                //$html = $html.'<td></td>';	
                //$html = $html.'<td width = "3%"></td>';	
							
                foreach($columns as $column_key1=>$column_value)
                {
                	if($column_key1=='#')
                    {
                    	$width_w = ' width="1%" ';
                    }
                    if($column_key1=='full_name')
                    {
                    	$width_w = ' style ="text-align:left;" width="25%"';
                    }
                    if($column_key1!='#' && $column_key1!='full_name')
                    {
                    	$width_w = ' width="15%"';
                    }
                    $checked=0;
                    foreach( $last_row as $key=>$value)
                    {
                        if($key == $column_key1 && isset($value) != "")
                        {
                            $html = $html.'<td '.$width_w.'>'.$value.'</td>'; 
                            $checked=1;
                        }
                    }
                    
                    if($checked != 1)
                    {
                        $html = $html.'<td ></td>';
                    }                        
                }
                $html=  $html.'</tr>';					
				
				$html=  $html.'</tbody>';
				
				                        
                $html=  $html.'</table>';        //echo $html; die;
      
                        
                // output the HTML content
                $pdf->writeHTML($html, true, false, true, false, '');
                        
                unset($_SESSION['header_data']);
                        
                //Close and output PDF document
                //$pdf->Output('example_006.pdf', 'I');
				$output = $pdf->Output('','S');
                        
                //exit;  
				
				if ( isset($output) )
				{
					return $output;				
				}
				
				return FALSE;              
                                
            }

        }
		
        //FL ADDED FOR LATE DETAIL REPORT 20160517
        function LateDetailReport($data, $columns = NULL, $current_user, $current_company)
        {
//           echo '<pre>'; print_r($data); echo '<pre>'; die;
            $ignore_last_row = TRUE;
            $include_header = TRUE;
            $eol = "\n";
            


            if ( is_array($data) AND count($data) > 0 AND is_array($columns) AND count($columns) > 0 )
            {
                
                if ( $ignore_last_row === TRUE )
                {
                    $last_row = array_pop($data);//ARSP EDIT --> THIS FUNCTION USE TO REMOVE THE LAST ELEMENT OF THEAT ARRAY
                }               


                
                
                
                $_SESSION['header_data'] = array( 
                                                  'image_path'   => $current_company->getLogoFileName(),
                                                  'company_name' => $current_company->getName(),
                                                  'address1'     => $current_company->getAddress1(),
                                                  'address2'     => $current_company->getAddress2(),
                                                  'city'         => $current_company->getCity(),
                                                  'province'     => $current_company->getProvince(),
                                                  'postal_code'  => $current_company->getPostalCode(), 
                                                );
												
				$pdf = new TimeReportHeaderFooter();								
                
                // set default header data
                $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

                // set header and footer fonts
                $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
                $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, 44, 23);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

                // add a page
                $pdf->AddPage();
                
                //Table border
                $pdf->setLineWidth( 0.20 );
                
                //set table position
                $adjust_x = 19;		
                
                $pdf->setXY( Misc::AdjustXY(1, $adjust_x), Misc::AdjustXY(44, $adjust_y) );
				           
                
                //TABLE CODE HERE
                        
                //Header
                // create some HTML content
                $html = '<table border="0" cellspacing="0" cellpadding="0" width="113%">
                        <tr style="background-color:#CCCCCC;text-align:center;">';
                $html = $html.'<td width = "3%">#</td>';
                
                $pdf->SetFont('', 'B');    
                foreach( $columns as $column_name )
                {                    
                    $html = $html.'<td style ="text-align:center:justify;font-weight:bold;" >'.$column_name.'</td>';                      
                }
                $html=  $html.'</tr>';
                
                $pdf->SetFont('','',8);  
  
                $x=1;   
                foreach( $data as $rows ) 
                {                    
                        if($x % 2 == 0)
                        {
                            $html=  $html.'<tr style ="text-align:center" bgcolor="#EEEEEE" nobr="true">';
                        }
                        else
                        {
                            $html=  $html.'<tr style ="text-align:center" bgcolor="WHITE" nobr="true">';
                        }                    
                    
                    $html = $html.'<td>'.$x++.'</td>';
                    
                    foreach ($columns as $column_key => $column_name ) 
                    {
                        if ( isset($rows[$column_key])  && $rows[$column_key] != "")
                        {
                            $html = $html.'<td>'.$rows[$column_key].'</td>'; 
                        }
                        
                        else
                        {
                            $html = $html.'<td>'.'--'.'</td>';
                        }
                    }
                    $html=  $html.'</tr>';          
                }
				
				
				//SUM ROW
                $html=  $html.'<tr style ="background-color:#CCCCCC;text-align:justify;" >';
                $html = $html.'<td width = "3%"></td>';	
							
                foreach($columns as $column_key1=>$column_value)
                {
                    $checked=0;
                    foreach( $last_row as $key=>$value)
                    {
                        if($key == $column_key1 && isset($value) != "")
                        {
                            $html = $html.'<td style ="center;text-align:center:justify;font-weight:bold;" >'.$value.'</td>'; 
                            $checked=1;
                        }
                    }
                    
                    if($checked != 1)
                    {
                        $html = $html.'<td style ="text-align:center:justify;font-weight:bold;">--</td>';
                    }                        
                }
                $html=  $html.'</tr>';					
				
				
				
				                        
                $html=  $html.'</table>';        
      
                        
                // output the HTML content
                $pdf->writeHTML($html, true, false, true, false, '');
                        
                unset($_SESSION['header_data']);
                        
                //Close and output PDF document
                //$pdf->Output('example_006.pdf', 'I');
				$output = $pdf->Output('','S');
                        
                //exit;  
				
				if ( isset($output) )
				{
					return $output;				
				}
				
				return FALSE;              
                                
            }

        }
	


}
?>
