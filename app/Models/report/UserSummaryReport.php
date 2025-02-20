<?php

namespace App\Models\Report;

class UserSummaryReport extends Report {

	function __construct() {
		$this->title = TTi18n::getText('Employee Summary Report');
		$this->file_name = 'employee_summary_report';

		parent::__construct();

		return TRUE;
	}

	protected function _checkPermissions( $user_id, $company_id ) {
		if ( $this->getPermissionObject()->Check('report','enabled', $user_id, $company_id )
				AND $this->getPermissionObject()->Check('report','view_user_information', $user_id, $company_id ) ) {
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
										//'time_period',
										'columns',
							   );
				break;
			case 'setup_fields':
				$retval = array(
										//Static Columns - Aggregate functions can't be used on these.
										'-1000-template' => TTi18n::gettext('Template'),
										//'-1010-time_period' => TTi18n::gettext('Time Period'),

										'-2010-user_status_id' => TTi18n::gettext('Employee Status'),
										'-2020-user_group_id' => TTi18n::gettext('Employee Group'),
										'-2030-user_title_id' => TTi18n::gettext('Employee Title'),
										'-2040-include_user_id' => TTi18n::gettext('Employee Include'),
										'-2050-exclude_user_id' => TTi18n::gettext('Employee Exclude'),
										'-2060-default_branch_id' => TTi18n::gettext('Default Branch'),
										'-2070-default_department_id' => TTi18n::gettext('Default Department'),
										//'-2080-punch_branch_id' => TTi18n::gettext('Punch Branch'),
										//'-2090-punch_department_id' => TTi18n::gettext('Punch Department'),

										'-5000-columns' => TTi18n::gettext('Display Columns'),
										'-5010-group' => TTi18n::gettext('Group By'),
										'-5020-sub_total' => TTi18n::gettext('SubTotal By'),
										'-5030-sort' => TTi18n::gettext('Sort By'),
							   );
				break;
			case 'date_columns':
				$retval = array_merge(
									TTDate::getReportDateOptions( 'hire', TTi18n::getText('Appointment Date'), 16, FALSE ),
									TTDate::getReportDateOptions( 'termination', TTi18n::getText('Termination Date'), 16, FALSE ),
									TTDate::getReportDateOptions( 'birth', TTi18n::getText('Birth Date'), 17, FALSE )
								);
				break;
			case 'static_columns':
				$retval = array(
										//Static Columns - Aggregate functions can't be used on these.
										'-1000-first_name' => TTi18n::gettext('First Name'),
										'-1001-middle_name' => TTi18n::gettext('Middle Name'),
										'-1002-last_name' => TTi18n::gettext('Last Name'),
										'-1005-full_name' => TTi18n::gettext('Full Name'),

										'-1010-user_name' => TTi18n::gettext('User Name'),
										'-1020-phone_id' => TTi18n::gettext('PIN/Phone ID'),

										'-1030-employee_number' => TTi18n::gettext('Employee #'),

										'-1040-status' => TTi18n::gettext('Status'),
										'-1050-title' => TTi18n::gettext('Title'),
										'-1060-province' => TTi18n::gettext('Province/State'),
										'-1070-country' => TTi18n::gettext('Country'),
										'-1080-user_group' => TTi18n::gettext('Group'),
										'-1090-default_branch' => TTi18n::gettext('Branch'), //abbreviate for space
										'-1100-default_department' => TTi18n::gettext('Department'), //abbreviate for space
										'-1110-currency' => TTi18n::gettext('Currency'),

										'-1200-permission_control' => TTi18n::gettext('Permission Group'),
										'-1210-pay_period_schedule' => TTi18n::gettext('Pay Period Schedule'),
										'-1220-policy_group' => TTi18n::gettext('Policy Group'),

										'-1310-sex' => TTi18n::gettext('Sex'),
										'-1320-address1' => TTi18n::gettext('Address 1'),
										'-1330-address2' => TTi18n::gettext('Address 2'),

										'-1340-city' => TTi18n::gettext('City'),
										'-1350-province' => TTi18n::gettext('Province/State'),
										'-1360-country' => TTi18n::gettext('Country'),
										'-1370-postal_code' => TTi18n::gettext('Postal Code'),
										'-1380-work_phone' => TTi18n::gettext('Work Phone'),
										'-1391-work_phone_ext' => TTi18n::gettext('Work Phone Ext'),
										'-1400-home_phone' => TTi18n::gettext('Home Phone'),
										'-1410-mobile_phone' => TTi18n::gettext('Mobile Phone'),
										'-1420-fax_phone' => TTi18n::gettext('Fax Phone'),
										'-1430-home_email' => TTi18n::gettext('Home Email'),
										'-1440-work_email' => TTi18n::gettext('Work Email'),
										'-1480-sin' => TTi18n::gettext('SIN/SSN'),
										'-1490-note' => TTi18n::gettext('Note'),

										'-1495-tag' => TTi18n::gettext('Tags'),
										'-1499-hierarchy_control_display' => TTi18n::gettext('Hierarchy'),

										//Date columns handles these.
										//'-1500-hire_date' => TTi18n::gettext('Hire Date'),
										//'-1600-termination_date' => TTi18n::gettext('Termination Date'),
										//'-1700-birth_date' => TTi18n::gettext('Birth Date'),

										'-1500-institution' => TTi18n::gettext('Bank Institution'),
										'-1510-transit' => TTi18n::gettext('Bank Transit/Routing'),
										'-1520-account' => TTi18n::gettext('Bank Account'),

										'-1619-currency' => TTi18n::gettext('Currency'),
										'-1620-type' => TTi18n::gettext('Wage Type'),
										'-1640-effective_date' => TTi18n::gettext('Wage Effective Date'),

										'-1700-language_display' => TTi18n::gettext('Language'),
										'-1710-date_format_display' => TTi18n::gettext('Date Format'),
										'-1720-time_format_display' => TTi18n::gettext('Time Format'),
										'-1730-time_unit_format_display' => TTi18n::gettext('Time Units'),
										'-1740-time_zone_display' => TTi18n::gettext('Time Zone'),
										'-1750-items_per_page' => TTi18n::gettext('Rows Per page'),
							   );

				$retval = array_merge( $retval, $this->getOptions('date_columns') );
				ksort($retval);
				break;
			case 'dynamic_columns':
				$retval = array(
										//Dynamic - Aggregate functions can be used
										'-1630-wage' => TTi18n::gettext('Wage'),
										'-1635-hourly_rate' => TTi18n::gettext('Hourly Rate'),

										'-2000-total_user' => TTi18n::gettext('Total Employees'), //Group counter...
							);

				break;
			case 'columns':
				$retval = array_merge( $this->getOptions('static_columns'), $this->getOptions('dynamic_columns') );
				break;
			case 'column_format':
				//Define formatting function for each column.
				$columns = $this->getOptions('dynamic_columns');
				if ( is_array($columns) ) {
					foreach($columns as $column => $name ) {
						if ( strpos($column, 'wage') !== FALSE OR strpos($column, 'hourly_rate') !== FALSE ) {
							$retval[$column] = 'currency';
						}
					}
				}
				break;
			case 'aggregates':
				$retval = array();
				$dynamic_columns = array_keys( Misc::trimSortPrefix( $this->getOptions('dynamic_columns') ) );
				if ( is_array($dynamic_columns ) ) {
					foreach( $dynamic_columns as $column ) {
						switch ( $column ) {
							default:
								if ( strpos($column, 'hourly_rate') !== FALSE OR strpos($column, 'wage') !== FALSE ) {
									$retval[$column] = 'avg';
								} else {
									$retval[$column] = 'sum';
								}
						}
					}
				}
				break;
			case 'templates':
				$retval = array(
										'-1010-by_employee+contact' => TTi18n::gettext('Contact Information By Employee'),

										'-1020-by_employee+employment' => TTi18n::gettext('Employment Information By Employee'), //Branch, Department, Title, Group, Hire Date?

										'-1030-by_employee+address' => TTi18n::gettext('Addresses By Employee'),
										'-1040-by_employee+wage' => TTi18n::gettext('Wages By Employee'),

										'-1050-by_employee+bank' => TTi18n::gettext('Bank Information By Employee'),
										'-1060-by_employee+preference' => TTi18n::gettext('Preferences By Employee'),
										//'-1020-by_employee+deduction' => TTi18n::gettext('Deductions By Employee'),
										'-1070-by_employee+birth_date' => TTi18n::gettext('Birthdays By Employee'),

										'-1080-by_branch_by_employee+contact' => TTi18n::gettext('Contact Information By Branch/Employee'),
										'-1090-by_branch_by_employee+address' => TTi18n::gettext('Addresses By Branch/Employee'),
										'-1110-by_branch_by_employee+wage' => TTi18n::gettext('Wages by Branch/Employee'),
										'-1120-by_branch+total_user' => TTi18n::gettext('Total Employees by Branch'),

										'-1130-by_department_by_employee+contact' => TTi18n::gettext('Contact Information By Department/Employee'),
										'-1140-by_department_by_employee+address' => TTi18n::gettext('Addresses By Department/Employee'),
										'-1150-by_department_by_employee+wage' => TTi18n::gettext('Wages by Department/Employee'),
										'-1160-by_department+total_user' => TTi18n::gettext('Total Employees by Department'),

										'-1170-by_branch_by_department_by_employee+contact' => TTi18n::gettext('Contact Information By Branch/Department/Employee'),
										'-1180-by_branch_by_department_by_employee+address' => TTi18n::gettext('Addresses By Branch/Department/Employee'),
										'-1190-by_branch_by_department+wage' => TTi18n::gettext('Wages by Branch/Department/Employee'),
										'-1200-by_branch_by_department+total_user' => TTi18n::gettext('Total Employees by Branch/Department'),

										'-1210-by_type_by_employee+wage' => TTi18n::gettext('Wages By Type/Employee'),
										'-1220-by_type+total_user' => TTi18n::gettext('Total Employees by Wage Type'),


										'-1230-by_hired_month+total_user' => TTi18n::gettext('Total Employees Hired By Month'),
										'-1240-by_termination_month+total_user' => TTi18n::gettext('Total Employees Terminated By Month'),
							   );

				break;
			case 'template_config':
				$template = strtolower( Misc::trimSortPrefix( $params['template'] ) );
				if ( isset($template) AND $template != '' ) {
					switch( $template ) {

						//Contact
						case 'by_employee+contact':
							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'work_phone';
							$retval['columns'][] = 'work_phone_ext';
							$retval['columns'][] = 'work_email';
							$retval['columns'][] = 'mobile_phone';
							$retval['columns'][] = 'home_phone';
							$retval['columns'][] = 'home_email';

							$retval['sort'][] = array('last_name' => 'asc');
							$retval['sort'][] = array('first_name' => 'asc');
							break;
						case 'by_branch_by_employee+contact':
							$retval['columns'][] = 'default_branch';

							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'work_phone';
							$retval['columns'][] = 'work_phone_ext';
							$retval['columns'][] = 'work_email';
							$retval['columns'][] = 'mobile_phone';
							$retval['columns'][] = 'home_phone';
							$retval['columns'][] = 'home_email';

							$retval['sort'][] = array('default_branch' => 'asc');
							$retval['sort'][] = array('last_name' => 'asc');
							$retval['sort'][] = array('first_name' => 'asc');
							break;
						case 'by_department_by_employee+contact':
							$retval['columns'][] = 'default_department';

							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'work_phone';
							$retval['columns'][] = 'work_phone_ext';
							$retval['columns'][] = 'work_email';
							$retval['columns'][] = 'mobile_phone';
							$retval['columns'][] = 'home_phone';
							$retval['columns'][] = 'home_email';

							$retval['sort'][] = array('default_department' => 'asc');
							$retval['sort'][] = array('last_name' => 'asc');
							$retval['sort'][] = array('first_name' => 'asc');
							break;
						case 'by_branch_by_department_by_employee+contact':
							$retval['columns'][] = 'default_branch';
							$retval['columns'][] = 'default_department';

							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'work_phone';
							$retval['columns'][] = 'work_phone_ext';
							$retval['columns'][] = 'work_email';
							$retval['columns'][] = 'mobile_phone';
							$retval['columns'][] = 'home_phone';
							$retval['columns'][] = 'home_email';

							$retval['sort'][] = array('default_branch' => 'asc');
							$retval['sort'][] = array('default_department' => 'asc');
							$retval['sort'][] = array('last_name' => 'asc');
							$retval['sort'][] = array('first_name' => 'asc');
							break;

						//Birth Dates
						case 'by_employee+birth_date':
							$retval['columns'][] = 'birth-date_month';

							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'birth-date_stamp';

							//$retval['group'][] = 'birth-date_month';

							$retval['sub_total'][] = 'birth-date_month';

							$retval['sort'][] = array('birth-date_month' => 'asc');
							$retval['sort'][] = array('birth-date_dom' => 'asc');
							$retval['sort'][] = array('last_name' => 'asc');
							$retval['sort'][] = array('first_name' => 'asc');
							break;

						//Employment
						case 'by_employee+employment':
							$retval['columns'][] = 'status';
							$retval['columns'][] = 'default_branch';
							$retval['columns'][] = 'default_department';
							$retval['columns'][] = 'title';
							$retval['columns'][] = 'user_group';

							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'hire-date_stamp';
							$retval['columns'][] = 'termination-date_stamp';

							$retval['sort'][] = array('status' => 'asc');
							$retval['sort'][] = array('default_branch' => 'asc');
							$retval['sort'][] = array('default_department' => 'asc');
							$retval['sort'][] = array('title' => 'asc');
							$retval['sort'][] = array('user_group' => 'asc');
							$retval['sort'][] = array('last_name' => 'asc');
							$retval['sort'][] = array('first_name' => 'asc');
							//$retval['sort'][] = array('hire-date_month' => 'asc');
							break;
						case 'by_hired_month+total_user':
							$retval['columns'][] = 'hire-date_year';
							$retval['columns'][] = 'hire-date_month_year';
							$retval['columns'][] = 'total_user';

							$retval['group'][] = 'hire-date_year';
							$retval['group'][] = 'hire-date_month_year';

							$retval['sub_total'][] = 'hire-date_year';

							$retval['sort'][] = array('hire-date_year' => 'desc');
							$retval['sort'][] = array('hire-date_month_year' => 'desc');
							$retval['sort'][] = array('total_user' => 'desc');
							break;
						case 'by_termination_month+total_user':
							$retval['columns'][] = 'termination-date_year';
							$retval['columns'][] = 'termination-date_month_year';
							$retval['columns'][] = 'total_user';

							$retval['group'][] = 'termination-date_year';
							$retval['group'][] = 'termination-date_month_year';

							$retval['sub_total'][] = 'termination-date_year';

							$retval['sort'][] = array('termination-date_year' => 'desc');
							$retval['sort'][] = array('termination-date_month_year' => 'desc');
							$retval['sort'][] = array('total_user' => 'desc');
							break;


						//Address
						case 'by_employee+address':
							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'address1';
							$retval['columns'][] = 'address2';
							$retval['columns'][] = 'city';
							$retval['columns'][] = 'country';
							$retval['columns'][] = 'province';
							$retval['columns'][] = 'postal_code';

							$retval['sort'][] = array('last_name' => 'asc');
							$retval['sort'][] = array('first_name' => 'asc');
							break;
						case 'by_branch_by_employee+address':
							$retval['columns'][] = 'default_branch';

							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'address1';
							$retval['columns'][] = 'address2';
							$retval['columns'][] = 'city';
							$retval['columns'][] = 'country';
							$retval['columns'][] = 'province';
							$retval['columns'][] = 'postal_code';

							$retval['sort'][] = array('default_branch' => 'asc');
							$retval['sort'][] = array('last_name' => 'asc');
							$retval['sort'][] = array('first_name' => 'asc');
							break;
						case 'by_department_by_employee+address':
							$retval['columns'][] = 'default_department';

							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'address1';
							$retval['columns'][] = 'address2';
							$retval['columns'][] = 'city';
							$retval['columns'][] = 'country';
							$retval['columns'][] = 'province';
							$retval['columns'][] = 'postal_code';

							$retval['sort'][] = array('default_department' => 'asc');
							$retval['sort'][] = array('last_name' => 'asc');
							$retval['sort'][] = array('first_name' => 'asc');
							break;
						case 'by_branch_by_department_by_employee+address':
							$retval['columns'][] = 'default_branch';
							$retval['columns'][] = 'default_department';

							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'address1';
							$retval['columns'][] = 'address2';
							$retval['columns'][] = 'city';
							$retval['columns'][] = 'country';
							$retval['columns'][] = 'province';
							$retval['columns'][] = 'postal_code';

							$retval['sort'][] = array('default_branch' => 'asc');
							$retval['sort'][] = array('default_department' => 'asc');
							$retval['sort'][] = array('last_name' => 'asc');
							$retval['sort'][] = array('first_name' => 'asc');
							break;

						//Wage
						case 'by_employee+wage':
							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'type';
							$retval['columns'][] = 'wage';
							$retval['columns'][] = 'hourly_rate';
							$retval['columns'][] = 'effective_date';

							$retval['sort'][] = array('last_name' => 'asc');
							$retval['sort'][] = array('first_name' => 'asc');
							$retval['sort'][] = array('type' => 'asc');
							$retval['sort'][] = array('wage' => 'desc');
							break;
						case 'by_branch_by_employee+wage':
							$retval['columns'][] = 'default_branch';

							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'type';
							$retval['columns'][] = 'wage';
							$retval['columns'][] = 'hourly_rate';
							$retval['columns'][] = 'effective_date';

							$retval['sort'][] = array('default_branch' => 'asc');
							$retval['sort'][] = array('type' => 'asc');
							$retval['sort'][] = array('wage' => 'desc');
							$retval['sort'][] = array('last_name' => 'asc');
							$retval['sort'][] = array('first_name' => 'asc');
							break;
						case 'by_department_by_employee+wage':
							$retval['columns'][] = 'default_department';

							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'type';
							$retval['columns'][] = 'wage';
							$retval['columns'][] = 'hourly_rate';
							$retval['columns'][] = 'effective_date';

							$retval['sort'][] = array('default_department' => 'asc');
							$retval['sort'][] = array('type' => 'asc');
							$retval['sort'][] = array('wage' => 'desc');
							$retval['sort'][] = array('last_name' => 'asc');
							$retval['sort'][] = array('first_name' => 'asc');
							break;
						case 'by_branch_by_department_by_employee+wage':
							$retval['columns'][] = 'default_branch';
							$retval['columns'][] = 'default_department';

							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'type';
							$retval['columns'][] = 'wage';
							$retval['columns'][] = 'hourly_rate';
							$retval['columns'][] = 'effective_date';

							$retval['sort'][] = array('default_branch' => 'asc');
							$retval['sort'][] = array('default_department' => 'asc');
							$retval['sort'][] = array('type' => 'asc');
							$retval['sort'][] = array('wage' => 'desc');
							$retval['sort'][] = array('last_name' => 'asc');
							$retval['sort'][] = array('first_name' => 'asc');
							break;
						case 'by_type_by_employee+wage':
							$retval['columns'][] = 'type';

							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'wage';
							$retval['columns'][] = 'hourly_rate';
							$retval['columns'][] = 'effective_date';

							$retval['sub_total'][] = 'type';

							$retval['sort'][] = array('type' => 'asc');
							$retval['sort'][] = array('wage' => 'desc');
							$retval['sort'][] = array('last_name' => 'asc');
							$retval['sort'][] = array('first_name' => 'asc');
							break;

						//Bank Account
						case 'by_employee+bank':
							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'transit';
							$retval['columns'][] = 'account';
							$retval['columns'][] = 'institution';

							$retval['sort'][] = array('last_name' => 'asc');
							$retval['sort'][] = array('first_name' => 'asc');
							break;

						//Preferences
						case 'by_employee+preference':
							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'date_format_display';
							$retval['columns'][] = 'time_format_display';
							$retval['columns'][] = 'time_unit_format_display';
							$retval['columns'][] = 'time_zone_display';
							$retval['columns'][] = 'language_display';
							$retval['columns'][] = 'items_per_page';

							$retval['sort'][] = array('last_name' => 'asc');
							$retval['sort'][] = array('first_name' => 'asc');
							break;

						//Other
						case 'by_branch+total_user':
							$retval['columns'][] = 'default_branch';

							$retval['columns'][] = 'total_user';

							$retval['group'][] = 'default_branch';

							$retval['sort'][] = array('total_user' => 'desc');
							break;
						case 'by_department+total_user':
							$retval['columns'][] = 'default_department';

							$retval['columns'][] = 'total_user';

							$retval['group'][] = 'default_department';

							$retval['sort'][] = array('total_user' => 'desc');
							break;
						case 'by_branch_by_department+total_user':
							$retval['columns'][] = 'default_branch';
							$retval['columns'][] = 'default_department';

							$retval['columns'][] = 'total_user';

							$retval['group'][] = 'default_branch';
							$retval['group'][] = 'default_department';

							$retval['sub_total'][] = 'default_branch';

							$retval['sort'][] = array('default_branch' => 'asc');
							//$retval['sort'][] = array('' => 'asc');
							$retval['sort'][] = array('total_user' => 'desc');
							break;
						case 'by_type+total_user':
							$retval['columns'][] = 'type';

							$retval['columns'][] = 'total_user';

							$retval['group'][] = 'type';

							$retval['sub_total'][] = 'type';

							$retval['sort'][] = array('type' => 'asc');
							$retval['sort'][] = array('total_user' => 'desc');
							break;

						default:
							Debug::Text(' Parsing template name: '. $template, __FILE__, __LINE__, __METHOD__,10);
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

	//Get raw data for report
	function _getData( $format = NULL ) {
		$this->tmp_data = array('user' => array(), 'user_preference' => array(), 'user_wage' => array(),  'user_bank' => array(), 'user_deduction' => array(), 'total_user' => array() );

		$columns = $this->getColumnConfig();
		$filter_data = $this->getFilterConfig();

		if ( $this->getPermissionObject()->Check('user','view') == FALSE OR $this->getPermissionObject()->Check('wage','view') == FALSE ) {
			$hlf = TTnew( 'HierarchyListFactory' );
			$permission_children_ids = $wage_permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $this->getUserObject()->getCompany(), $this->getUserObject()->getID() );
			Debug::Arr($permission_children_ids,'Permission Children Ids:', __FILE__, __LINE__, __METHOD__,10);
		} else {
			//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
			$permission_children_ids = array();
			$wage_permission_children_ids = array();
		}
		if ( $this->getPermissionObject()->Check('user','view') == FALSE ) {
			if ( $this->getPermissionObject()->Check('user','view_child') == FALSE ) {
				$permission_children_ids = array();
			}
			if ( $this->getPermissionObject()->Check('user','view_own') ) {
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

		//Always include date columns, because 'hire-date_stamp' is not recognized by the UserFactory.
		$columns['hire_date'] = $columns['termination_date'] = $columns['birth_date'] = TRUE;

		//Get user data for joining.
		$ulf = TTnew( 'UserListFactory' );
		$ulf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text(' User Rows: '. $ulf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), $ulf->getRecordCount(), NULL, TTi18n::getText('Retrieving Data...') );
		foreach ( $ulf as $key => $u_obj ) {
			//We used to just get return the entire $u_obj->data array, but this wouldn't include tags and other columns that required some additional processing.
			//Not sure why this was done that way... I think because we had problems with the multiple date fields (Hire Date/Termination Date/Birth Date, etc...)
			$this->tmp_data['user'][$u_obj->getId()] = (array)$u_obj->getObjectAsArray( $columns );
			//$this->tmp_data['user'][$u_obj->getId()] = (array)$u_obj->data;
			//$this->tmp_data['user'][$u_obj->getId()]['status'] = Option::getByKey( $u_obj->getStatus(), $u_obj->getOptions( 'status' ) );

			$this->tmp_data['user_preference'][$u_obj->getId()] = array();
			$this->tmp_data['user_wage'][$u_obj->getId()] = array();

			$this->tmp_data['user'][$u_obj->getId()]['total_user'] = 1;

			$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
		}
		//Debug::Arr($this->tmp_data['user'], 'TMP User Data: ', __FILE__, __LINE__, __METHOD__,10);

		//Get user preference data for joining.
		$uplf = TTnew( 'UserPreferenceListFactory' );
		$uplf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text(' User Preference Rows: '. $ulf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), $uplf->getRecordCount(), NULL, TTi18n::getText('Retrieving Data...') );
		foreach ( $uplf as $key => $up_obj ) {
			$this->tmp_data['user_preference'][$up_obj->getUser()] = (array)$up_obj->getObjectAsArray( $columns );
			$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
		}

		//Get user wage data for joining.
		$filter_data['wage_group_id'] = 0; //Use default wage groups only.
		$uwlf = TTnew( 'UserWageListFactory' );
		$uwlf->getAPILastWageSearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text(' User Wage Rows: '. $uwlf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), $ulf->getRecordCount(), NULL, TTi18n::getText('Retrieving Data...') );
		foreach ( $uwlf as $key => $uw_obj ) {
			if ( $wage_permission_children_ids === TRUE OR in_array( $uw_obj->getUser(), $wage_permission_children_ids) ) {
				$this->tmp_data['user_wage'][$uw_obj->getUser()] = (array)$uw_obj->getObjectAsArray( $columns );
			}
			$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
		}


		//Get user bank data for joining.
		$balf = TTnew( 'BankAccountListFactory' );
		$balf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text(' User Bank Rows: '. $balf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), $balf->getRecordCount(), NULL, TTi18n::getText('Retrieving Data...') );
		foreach ( $balf as $key => $ba_obj ) {
			$this->tmp_data['user_bank'][$ba_obj->getUser()] = (array)$ba_obj->getObjectAsArray( $columns );
			$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
		}

		//Debug::Arr($this->tmp_data['user_preference'], 'TMP Data: ', __FILE__, __LINE__, __METHOD__,10);
		return TRUE;
	}

	//PreProcess data such as calculating additional columns from raw data etc...
	function _preProcess() {
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), count($this->tmp_data['user']), NULL, TTi18n::getText('Pre-Processing Data...') );

		$key=0;
		if ( isset($this->tmp_data['user']) ) {
			foreach( $this->tmp_data['user'] as $user_id => $row ) {
				if ( isset($row['hire_date']) ) {
					$hire_date_columns = TTDate::getReportDates( 'hire', TTDate::parseDateTime( $row['hire_date'] ), FALSE, $this->getUserObject() );
				} else {
					$hire_date_columns = array();
				}

				if ( isset($row['termination_date']) ) {
					$termination_date_columns = TTDate::getReportDates( 'termination', TTDate::parseDateTime( $row['termination_date'] ), FALSE, $this->getUserObject() );
				} else {
					$termination_date_columns = array();
				}
				if ( isset($row['birth_date']) ) {
					$birth_date_columns = TTDate::getReportDates( 'birth', TTDate::parseDateTime( $row['birth_date'] ), FALSE, $this->getUserObject() );
				} else {
					$birth_date_columns = array();
				}

				$processed_data = array();
				if ( isset($this->tmp_data['user_preference'][$user_id]) ) {
					$processed_data = array_merge( $processed_data, $this->tmp_data['user_preference'][$user_id] );
				}
				if ( isset($this->tmp_data['user_bank'][$user_id]) ) {
					$processed_data = array_merge( $processed_data, $this->tmp_data['user_bank'][$user_id] );
				}
				if ( isset($this->tmp_data['user_wage'][$user_id]) ) {
					$processed_data = array_merge( $processed_data, $this->tmp_data['user_wage'][$user_id] );
				}

				$this->data[] = array_merge( $row, $hire_date_columns, $termination_date_columns, $birth_date_columns, $processed_data );

				$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
				$key++;
			}
			unset($this->tmp_data, $row, $date_columns, $user_id, $hire_date_columns, $termination_date_columns, $birth_date_columns, $processed_data );
		}
		//Debug::Arr($this->data, 'preProcess Data: ', __FILE__, __LINE__, __METHOD__,10);

		return TRUE;
	}
}
?>
