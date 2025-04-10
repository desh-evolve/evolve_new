<?php

namespace App\Models\Report;

use App\Models\Core\Debug;
use App\Models\Core\Misc;
use App\Models\Core\Option;
use App\Models\Core\OtherFieldListFactory;
use App\Models\Core\StationListFactory;
use App\Models\Core\TTDate;
use App\Models\Hierarchy\HierarchyListFactory;
use App\Models\PayPeriod\PayPeriodTimeSheetVerifyListFactory;
use App\Models\Punch\PunchListFactory;
use App\Models\Users\UserListFactory;

class PunchSummaryReport extends Report {

	function __construct() {
		$this->title = ('Punch Summary Report');
		$this->file_name = 'punch_summary_report';

		parent::__construct();

		return TRUE;
	}

	protected function _checkPermissions( $user_id, $company_id ) {
		if ( $this->getPermissionObject()->Check('report','enabled', $user_id, $company_id )
				AND $this->getPermissionObject()->Check('report','view_punch_summary', $user_id, $company_id ) ) { //Piggyback on timesheet summary permissions.
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

										'-5000-columns' => ('Display Columns'),
										'-5010-group' => ('Group By'),
										'-5020-sub_total' => ('SubTotal By'),
										'-5030-sort' => ('Sort By'),
							   );

				if ( $this->getUserObject()->getCompanyObject()->getProductEdition() == 20 ) {
					$professional_edition_setup_fields = array(
										'-3010-job_id' => ('Job'),
										'-3010-job_status_id' => ('Job Status'),
										'-3030-job_branch_id' => ('Job Branch'),
										'-3040-job_department_id' => ('Job Department'),
										'-3050-job_group_id' => ('Job Group'),
										'-3060-job_item_id' => ('Task'),
									);
					$retval = array_merge( $retval, $professional_edition_setup_fields );
				}

				break;
			case 'time_period':
				$retval = TTDate::getTimePeriodOptions();
				break;
			case 'date_columns':
				$retval = TTDate::getReportDateOptions( NULL, ('Date'), 15, TRUE );
				break;
			case 'custom_columns':
				//Get custom fields for report data.
				$oflf = new OtherFieldListFactory(); 
				//User and Punch fields conflict as they are merged together in a secondary process.
				$other_field_names = $oflf->getByCompanyIdAndTypeIdArray( $this->getUserObject()->getCompany(), array(15,20,30), array( 15 => '', 20 => 'job_', 30 => 'job_item_') );
				if ( is_array($other_field_names) ) {
					$retval = Misc::addSortPrefix( $other_field_names, 9000 );
				}
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

										'-1200-permission_control' => ('Permission Group'),
										'-1210-pay_period_schedule' => ('Pay Period Schedule'),
										'-1220-policy_group' => ('Policy Group'),

										//Handled in date_columns above.
										//'-1230-pay_period' => ('Pay Period'),

										'-1600-in_time_stamp' => ('In Punch'),
										'-1601-in_type' => ('In Type'),
										'-1610-out_time_stamp' => ('Out Punch'),
										'-1611-out_type' => ('Out Type'),
										'-1620-in_actual_time_stamp' => ('In (Actual)'),
										'-1630-out_actual_time_stamp' => ('Out (Actual)'),
										'-1660-branch' => ('Branch'),
										'-1670-department' => ('Department'),
										'-1671-in_station_type' => ('In Station Type'),
										'-1672-in_station_station_id' => ('In Station ID'),
										'-1673-in_station_source' => ('In Station Source'),
										'-1674-in_station_description' => ('In Station Description'),
										'-1675-out_station_type' => ('Out Station Type'),
										'-1676-out_station_station_id' => ('Out Station ID'),
										'-1677-out_station_source' => ('Out Station Source'),
										'-1678-out_station_description' => ('Out Station Description'),
										'-1720-note' => ('Note'),
										'-1900-in_created_date' => ('In Created Date'),
										'-1905-in_updated_date' => ('In Updated Date'),
										'-1910-out_created_date' => ('Out Created Date'),
										'-1915-out_updated_date' => ('Out Updated Date'),
										'-1920-verified_time_sheet' => ('Verified TimeSheet'),
										'-1925-verified_time_sheet_date' => ('Verified TimeSheet Date'),
										'-1930-verified_time_sheet_tainted' => ('TimeSheet Verification Tainted'),

							   );

				if ( $this->getUserObject()->getCompanyObject()->getProductEdition() == 20 ) {
					$professional_edition_static_columns = array(
											//Static Columns - Aggregate functions can't be used on these.
											'-1810-job' => ('Job'),
											'-1820-job_manual_id' => ('Job Code'),
											'-1830-job_description' => ('Job Description'),
											'-1840-job_status' => ('Job Status'),
											'-1850-job_branch' => ('Job Branch'),
											'-1860-job_department' => ('Job Department'),
											'-1870-job_group' => ('Job Group'),
											'-1910-job_item' => ('Task'),
											'-1920-job_item_manual_id' => ('Task Code'),
											'-1930-job_item_description' => ('Task Description'),
											'-1940-job_item_group' => ('Task Group'),
								   );
					$retval = array_merge( $retval, $professional_edition_static_columns );
				}

				$retval = array_merge( $retval, (array)$this->getOptions('date_columns'), (array)$this->getOptions('custom_columns') );
				ksort($retval);
				break;
			case 'dynamic_columns':
				$retval = array(
										//Dynamic - Aggregate functions can be used

										//Take into account wage groups. However hourly_rates for the same hour type, so we need to figure out an average hourly rate for each column?
										'-2010-hourly_rate' => ('Hourly Rate'),

										'-2100-total_time' => ('Total Time'),
										'-2110-total_time_wage' => ('Total Time Wage'),
										'-2112-total_time_wage_burden' => ('Total Time Wage Burden'),
										'-2114-total_time_wage_with_burden' => ('Total Time Wage w/Burden'),

										'-2120-actual_total_time' => ('Actual Time'),
										'-2120-actual_total_time_wage' => ('Actual Time Wage'),
										'-2125-actual_total_time_diff' => ('Actual Time Difference'),
										'-2127-actual_total_time_diff_wage' => ('Actual Time Difference Wage'),
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
						if ( strpos($column, '_wage') !== FALSE OR strpos($column, '_hourly_rate') !== FALSE OR strpos($column, 'hourly_rate') !== FALSE ) {
							$retval[$column] = 'currency';
						} elseif ( strpos($column, '_time') OR strpos($column, '_policy') ) {
							$retval[$column] = 'time_unit';
						}
					}
				}
				$retval['in_time_stamp'] = $retval['out_time_stamp'] = $retval['in_created_date'] = $retval['in_updated_date'] = $retval['out_created_date'] = $retval['out_updated_date'] = $retval['verified_time_sheet_date'] = 'time_stamp';
				$retval['verified_time_sheet_tainted'] = 'boolean';
				break;
			case 'aggregates':
				$retval = array();
				$dynamic_columns = array_keys( Misc::trimSortPrefix( $this->getOptions('dynamic_columns') ) );
				if ( is_array($dynamic_columns ) ) {
					foreach( $dynamic_columns as $column ) {
						switch ( $column ) {
							default:
								if ( strpos($column, '_hourly_rate') !== FALSE OR strpos($column, 'hourly_rate') !== FALSE ) {
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
										'-1010-by_employee+punch_summary+total_time' => ('Punch Summary By Employee'),
										'-1020-by_branch+punch_summary+total_time' => ('Punch Summary by Branch'),
										'-1030-by_department+punch_summary+total_time' => ('Punch Summary by Department'),
										'-1040-by_branch_by_department+punch_summary+total_time' => ('Punch Summary by Branch/Department'),
										'-1050-by_pay_period+punch_summary+total_time' => ('Punch Summary by Pay Period'),
										'-1060-by_date_stamp+punch_summary+total_time' => ('Punch Summary by Date'),
										'-1070-by_station+punch_summary+total_time' => ('Punch Summary by Station'),

										'-1080-by_employee+punch_summary+total_time+note' => ('Punch Summary+Notes by Employee'),
										'-1090-by_employee+punch_summary+total_time+actual_time' => ('Punch Summary+Actual Time by Employee'),
										'-1100-by_employee+punch_summary+station_summary+total_time' => ('Punch/Station Detail By Employee'),

										'-1110-by_employee+actual_time' => ('Actual Time by Employee'),

										//'-1010-by_job+punch_summary+total_time' => ('Punch Summary by Job'),
										//'-1010-by_job_item+punch_summary+total_time' => ('Punch Summary by Task'),
										'-1120-by_employee+verified_time_sheet' => ('TimeSheet Verification Tainted'),
							   );

				if ( $this->getUserObject()->getCompanyObject()->getProductEdition() == 20 ) {
					$professional_edition_templates = array(
										'-2010-by_job+punch_summary+total_time' => ('Punch Summary by Job'),
										'-2020-by_job_item+punch_summary+total_time' => ('Punch Summary by Task'),
										'-2030-by_job_by_job_item+punch_summary+total_time' => ('Punch Summary by Job/Task'),
										'-2040-by_job_branch+punch_summary+total_time' => ('Punch Summary by Job Branch'),
										'-2050-by_job_branch_by_job_department+punch_summary+total_time' => ('Punch Summary by Job Branch/Department'),
										'-2060-by_job_group+punch_summary+total_time' => ('Punch Summary by Job Group'),
									);
					$retval = array_merge( $retval, $professional_edition_templates );
				}

				break;
			case 'template_config':
				$template = strtolower( Misc::trimSortPrefix( $params['template'] ) );
				if ( isset($template) AND $template != '' ) {
					switch( $template ) {
						case 'by_employee+actual_time':
							$retval['-1010-time_period']['time_period'] = 'last_pay_period';

							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'total_time';
							$retval['columns'][] = 'actual_total_time';
							$retval['columns'][] = 'actual_total_time_diff';
							$retval['columns'][] = 'actual_total_time_diff_wage';

							$retval['group'][] = 'first_name';
							$retval['group'][] = 'last_name';

							$retval['sort'][] = array('actual_total_time_diff' => 'desc');
							$retval['sort'][] = array('last_name' => 'asc');
							$retval['sort'][] = array('first_name' => 'asc');

							break;
						case 'by_employee+verified_time_sheet':
							$retval['-1010-time_period']['time_period'] = 'last_pay_period';

							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'in_type';
							$retval['columns'][] = 'in_time_stamp';
							$retval['columns'][] = 'out_type';
							$retval['columns'][] = 'out_time_stamp';
							$retval['columns'][] = 'verified_time_sheet';
							$retval['columns'][] = 'verified_time_sheet_date';
							$retval['columns'][] = 'verified_time_sheet_tainted';

							$retval['sort'][] = array('verified_time_sheet_tainted' => 'desc');
							$retval['sort'][] = array('verified_time_sheet' => 'asc');
							$retval['sort'][] = array('verified_time_sheet_date' => 'desc');
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
										case 'total_time':
											$retval['columns'][] = 'total_time';
											break;
										case 'actual_time':
											$retval['columns'][] = 'actual_total_time';
											$retval['columns'][] = 'actual_total_time_diff';
											break;
										case 'note':
											$retval['columns'][] = 'note';
											break;
										case 'punch_summary':
											$retval['columns'][] = 'in_type';
											$retval['columns'][] = 'in_time_stamp';
											$retval['columns'][] = 'out_type';
											$retval['columns'][] = 'out_time_stamp';
											break;
										case 'station_summary':
											$retval['columns'][] = 'in_station_type';
											$retval['columns'][] = 'out_station_type';
											break;
										//Filter

										//Group By
										//SubTotal
										//Sort
										case 'by_employee':
											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';

											$retval['sub_total'][] = 'last_name';
											$retval['sub_total'][] = 'first_name';

											$retval['sort'][] = array('last_name' => 'asc');
											$retval['sort'][] = array('first_name' => 'asc');
											$retval['sort'][] = array('date_stamp' => 'asc');
											$retval['sort'][] = array('in_time_stamp' => 'asc');
											break;
										case 'by_branch':
											$retval['columns'][] = 'branch';

											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';

											$retval['sub_total'][] = 'branch';

											$retval['sort'][] = array('branch' => 'asc');
											$retval['sort'][] = array('last_name' => 'asc');
											$retval['sort'][] = array('first_name' => 'asc');
											$retval['sort'][] = array('date_stamp' => 'asc');
											$retval['sort'][] = array('in_time_stamp' => 'asc');
											break;
										case 'by_department':
											$retval['columns'][] = 'department';

											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';

											$retval['sub_total'][] = 'department';

											$retval['sort'][] = array('department' => 'asc');
											$retval['sort'][] = array('last_name' => 'asc');
											$retval['sort'][] = array('first_name' => 'asc');
											$retval['sort'][] = array('date_stamp' => 'asc');
											$retval['sort'][] = array('in_time_stamp' => 'asc');
											break;
										case 'by_branch_by_department':
											$retval['columns'][] = 'branch';
											$retval['columns'][] = 'department';

											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';

											$retval['sub_total'][] = 'branch';
											$retval['sub_total'][] = 'department';

											$retval['sort'][] = array('branch' => 'asc');
											$retval['sort'][] = array('department' => 'asc');
											$retval['sort'][] = array('last_name' => 'asc');
											$retval['sort'][] = array('first_name' => 'asc');
											$retval['sort'][] = array('date_stamp' => 'asc');
											$retval['sort'][] = array('in_time_stamp' => 'asc');
											break;
										case 'by_pay_period':
											$retval['columns'][] = 'pay_period';

											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';

											$retval['sub_total'][] = 'pay_period';

											$retval['sort'][] = array('pay_period' => 'asc');
											$retval['sort'][] = array('last_name' => 'asc');
											$retval['sort'][] = array('first_name' => 'asc');
											$retval['sort'][] = array('date_stamp' => 'asc');
											$retval['sort'][] = array('in_time_stamp' => 'asc');
											break;
										case 'by_station':
											$retval['columns'][] = 'in_station_type';
											$retval['columns'][] = 'in_station_description';

											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';

											$retval['sub_total'][] = 'in_station_type';
											$retval['sub_total'][] = 'in_station_description';

											$retval['sort'][] = array('in_station_type' => 'asc');
											$retval['sort'][] = array('in_station_description' => 'asc');
											$retval['sort'][] = array('last_name' => 'asc');
											$retval['sort'][] = array('first_name' => 'asc');
											$retval['sort'][] = array('date_stamp' => 'asc');
											$retval['sort'][] = array('in_time_stamp' => 'asc');
											break;
										case 'by_date_stamp':
											$retval['columns'][] = 'date_stamp';

											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';

											$retval['sub_total'][] = 'date_stamp';

											$retval['sort'][] = array('date_stamp' => 'asc');
											$retval['sort'][] = array('last_name' => 'asc');
											$retval['sort'][] = array('first_name' => 'asc');
											$retval['sort'][] = array('in_time_stamp' => 'asc');
											break;

										//Professional Edition templates.
										case 'by_job':
											$retval['columns'][] = 'job';

											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';

											$retval['sub_total'][] = 'job';

											$retval['sort'][] = array('job' => 'asc');
											$retval['sort'][] = array('last_name' => 'asc');
											$retval['sort'][] = array('first_name' => 'asc');
											$retval['sort'][] = array('date_stamp' => 'asc');
											$retval['sort'][] = array('in_time_stamp' => 'asc');
											break;
										case 'by_job_item':
											$retval['columns'][] = 'job_item';

											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';

											$retval['sub_total'][] = 'job_item';

											$retval['sort'][] = array('job_item' => 'asc');
											$retval['sort'][] = array('last_name' => 'asc');
											$retval['sort'][] = array('first_name' => 'asc');
											$retval['sort'][] = array('date_stamp' => 'asc');
											$retval['sort'][] = array('in_time_stamp' => 'asc');
											break;
										case 'by_job_by_job_item':
											$retval['columns'][] = 'job';
											$retval['columns'][] = 'job_item';

											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';

											$retval['sub_total'][] = 'job';
											$retval['sub_total'][] = 'job_item';

											$retval['sort'][] = array('job' => 'asc');
											$retval['sort'][] = array('job_item' => 'asc');
											$retval['sort'][] = array('last_name' => 'asc');
											$retval['sort'][] = array('first_name' => 'asc');
											$retval['sort'][] = array('date_stamp' => 'asc');
											$retval['sort'][] = array('in_time_stamp' => 'asc');
											break;
										case 'by_job_branch':
											$retval['columns'][] = 'job_branch';

											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';

											$retval['sub_total'][] = 'job_branch';

											$retval['sort'][] = array('job_branch' => 'asc');
											$retval['sort'][] = array('last_name' => 'asc');
											$retval['sort'][] = array('first_name' => 'asc');
											$retval['sort'][] = array('date_stamp' => 'asc');
											$retval['sort'][] = array('in_time_stamp' => 'asc');
											break;
										case 'by_job_department':
											$retval['columns'][] = 'job_department';

											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';

											$retval['sub_total'][] = 'job_department';

											$retval['sort'][] = array('job_department' => 'asc');
											$retval['sort'][] = array('last_name' => 'asc');
											$retval['sort'][] = array('first_name' => 'asc');
											$retval['sort'][] = array('date_stamp' => 'asc');
											$retval['sort'][] = array('in_time_stamp' => 'asc');
											break;
										case 'by_job_branch_by_job_department':
											$retval['columns'][] = 'job_branch';
											$retval['columns'][] = 'job_department';

											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';

											$retval['sub_total'][] = 'job_branch';
											$retval['sub_total'][] = 'job_department';

											$retval['sort'][] = array('job_branch' => 'asc');
											$retval['sort'][] = array('job_department' => 'asc');
											$retval['sort'][] = array('last_name' => 'asc');
											$retval['sort'][] = array('first_name' => 'asc');
											$retval['sort'][] = array('date_stamp' => 'asc');
											$retval['sort'][] = array('in_time_stamp' => 'asc');
											break;
										case 'by_job_group':
											$retval['columns'][] = 'job_group';

											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';

											$retval['sub_total'][] = 'job_group';

											$retval['sort'][] = array('job_group' => 'asc');
											$retval['sort'][] = array('last_name' => 'asc');
											$retval['sort'][] = array('first_name' => 'asc');
											$retval['sort'][] = array('date_stamp' => 'asc');
											$retval['sort'][] = array('in_time_stamp' => 'asc');
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

	//Get raw data for report
	function _getData( $format = NULL ) {
		$this->tmp_data = array('punch' => array(), 'user' => array(), 'verified_timesheet' => array() );

		$columns = $this->getColumnConfig();
		$filter_data = $this->getFilterConfig();

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

		$slf = new StationListFactory(); 
		$station_type_options = $slf->getOptions('type');

		if ( $this->getUserObject()->getCompanyObject()->getProductEdition() == 20 ) {
			$jlf = new JobListFactory();
			$job_status_options = $jlf->getOptions('status');
		} else {
			$job_status_options = array();
		}

		$pay_period_ids = array();

		$plf = new PunchListFactory(); 
		$punch_type_options = $plf->getOptions('type');

		$plf->getPunchSummaryReportByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text(' Total Rows: '. $plf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), $plf->getRecordCount(), NULL, ('Retrieving Data...') );
		if ( $plf->getRecordCount() > 0 ) {
			foreach ( $plf->rs as $key => $p_obj ) {
				$plf->data = (array)$p_obj;
				$p_obj = $plf;
				$pay_period_ids[$p_obj->getColumn('pay_period_id')] = TRUE;

				if ( !isset($this->tmp_data['punch'][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]) ) {
					$hourly_rate = 0;
					if ( $wage_permission_children_ids === TRUE OR in_array( $p_obj->getColumn('user_id'), $wage_permission_children_ids) ) {
						$hourly_rate = $p_obj->getColumn( 'hourly_rate' );
					}

					$actual_time_diff = (int)$p_obj->getColumn('actual_total_time') - (int)$p_obj->getColumn('total_time');

					$this->tmp_data['punch'][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')] = array(
						'user_id' => $p_obj->getColumn('user_id'),
						'user_group' => $p_obj->getColumn('group'),
						'branch' => $p_obj->getColumn('branch'),
						'department' => $p_obj->getColumn('department'),
						'job' => $p_obj->getColumn('job'),
						'job_status_id' => $p_obj->getColumn('job_status_id'),
						'job_status' => Option::getByKey($p_obj->getColumn('job_status_id'), $job_status_options, NULL ),
						'job_manual_id' => $p_obj->getColumn('job_manual_id'),
						'job_description' => $p_obj->getColumn('job_description'),
						'job_branch' => $p_obj->getColumn('job_branch'),
						'job_department' => $p_obj->getColumn('job_department'),
						'job_group' => $p_obj->getColumn('job_group'),
						'job_item' => $p_obj->getColumn('job_item'),
						'job_other_id1' => $p_obj->getColumn('job_other_id1'),
						'job_other_id2' => $p_obj->getColumn('job_other_id2'),
						'job_other_id3' => $p_obj->getColumn('job_other_id3'),
						'job_other_id4' => $p_obj->getColumn('job_other_id4'),
						'job_other_id5' => $p_obj->getColumn('job_other_id5'),
						'quantity' => $p_obj->getColumn('quantity'),
						'bad_quantity' => $p_obj->getColumn('bad_quantity'),
						'note' => $p_obj->getColumn('note'),
						'total_time' => $p_obj->getColumn('total_time'),
						'total_time_wage' => Misc::MoneyFormat( bcmul( TTDate::getHours( $p_obj->getColumn('total_time') ), $hourly_rate ), FALSE ),
						'total_time_wage_burden' => Misc::MoneyFormat( bcmul( TTDate::getHours( $p_obj->getColumn('total_time') ), bcmul( $hourly_rate, bcdiv( $p_obj->getColumn('labor_burden_percent'), 100 ) ) ), FALSE ),
						'total_time_wage_with_burden' => Misc::MoneyFormat( bcmul( TTDate::getHours( $p_obj->getColumn('total_time') ), bcmul( $hourly_rate, bcadd( bcdiv( $p_obj->getColumn('labor_burden_percent'), 100 ), 1) ) ), FALSE ),
						'actual_total_time' => $p_obj->getColumn('actual_total_time'),
						'actual_total_time_diff' => $actual_time_diff,
						'actual_total_time_wage' => Misc::MoneyFormat( bcmul( TTDate::getHours( $p_obj->getColumn('actual_total_time') ), $hourly_rate ), FALSE ),
						'actual_total_time_diff_wage' => Misc::MoneyFormat( bcmul( TTDate::getHours( $actual_time_diff ), $hourly_rate) ),
						'other_id1' => $p_obj->getColumn('other_id1'),
						'other_id2' => $p_obj->getColumn('other_id2'),
						'other_id3' => $p_obj->getColumn('other_id3'),
						'other_id4' => $p_obj->getColumn('other_id4'),
						'other_id5' => $p_obj->getColumn('other_id5'),
						'date_stamp' => TTDate::strtotime( $p_obj->getColumn('date_stamp') ),
						'in_time_stamp' => NULL,
						'in_actual_time_stamp' => NULL,
						'in_type' => NULL,
						'out_time_stamp' => NULL,
						'out_actual_time_stamp' => NULL,
						'out_type' => NULL,
						'user_wage_id' => $p_obj->getColumn('user_wage_id'),
						'hourly_rate' => Misc::MoneyFormat( $hourly_rate, FALSE ),
						'in_station_type' => NULL,
						'in_station_station_id' => NULL,
						'in_station_source' => NULL,
						'in_station_description' => NULL,
						'out_station_type' => NULL,
						'out_station_station_id' => NULL,
						'out_station_source' => NULL,
						'out_station_description' => NULL,
						'pay_period_start_date' => strtotime( $p_obj->getColumn('pay_period_start_date') ),
						'pay_period_end_date' => strtotime( $p_obj->getColumn('pay_period_end_date') ),
						'pay_period_transaction_date' => strtotime( $p_obj->getColumn('pay_period_transaction_date') ),
						'pay_period' => strtotime( $p_obj->getColumn('pay_period_transaction_date') ),
						'pay_period_id' => $p_obj->getColumn('pay_period_id'),
						);
				}

				if ( $p_obj->getColumn('status_id') == 10 ) {
					$this->tmp_data['punch'][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['in_time_stamp'] = TTDate::strtotime( $p_obj->getColumn('punch_time_stamp') );
					$this->tmp_data['punch'][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['in_type'] = Option::getByKey($p_obj->getColumn('type_id'), $punch_type_options, NULL );
					$this->tmp_data['punch'][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['in_actual_time_stamp'] = TTDate::strtotime( $p_obj->getColumn('punch_actual_time_stamp') );

					$this->tmp_data['punch'][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['in_station_type'] = Option::getByKey($p_obj->getColumn('station_type_id'), $station_type_options, '--' );
					$this->tmp_data['punch'][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['in_station_station_id'] = $p_obj->getColumn('station_station_id');
					$this->tmp_data['punch'][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['in_station_source']  = $p_obj->getColumn('station_source');
					$this->tmp_data['punch'][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['in_station_description'] = $p_obj->getColumn('station_description');

					$this->tmp_data['punch'][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['in_created_date'] = TTDate::strtotime( $p_obj->getColumn('punch_created_date') );
					$this->tmp_data['punch'][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['in_updated_date'] = TTDate::strtotime( $p_obj->getColumn('punch_updated_date') );
				} else {
					$this->tmp_data['punch'][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['out_time_stamp'] = TTDate::strtotime( $p_obj->getColumn('punch_time_stamp') );
					$this->tmp_data['punch'][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['out_type'] = Option::getByKey($p_obj->getColumn('type_id'), $punch_type_options, NULL );
					$this->tmp_data['punch'][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['out_actual_time_stamp'] = TTDate::strtotime( $p_obj->getColumn('punch_actual_time_stamp') );

					$this->tmp_data['punch'][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['out_station_type'] = Option::getByKey($p_obj->getColumn('station_type_id'), $station_type_options, '--' );
					$this->tmp_data['punch'][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['out_station_station_id'] = $p_obj->getColumn('station_station_id');
					$this->tmp_data['punch'][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['out_station_source']  = $p_obj->getColumn('station_source');
					$this->tmp_data['punch'][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['out_station_description'] = $p_obj->getColumn('station_description');

					$this->tmp_data['punch'][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['out_created_date'] = TTDate::strtotime( $p_obj->getColumn('punch_created_date') );
					$this->tmp_data['punch'][$p_obj->getColumn('user_id')][$p_obj->getColumn('punch_control_id')]['out_updated_date'] = TTDate::strtotime( $p_obj->getColumn('punch_updated_date') );
				}
				unset($hourly_rate, $uw_obj, $actual_time_diff);

				$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
			}
		}
		//Debug::Arr($this->tmp_data['punch'], 'Punch Raw Data: ', __FILE__, __LINE__, __METHOD__,10);

		//Get user data for joining.
		$ulf = new UserListFactory(); 
		$ulf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text(' User Total Rows: '. $ulf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), $ulf->getRecordCount(), NULL, ('Retrieving Data...') );
		foreach ( $ulf->rs as $key => $u_obj ) {
			$ulf->data = (array)$u_obj;
			$u_obj = $ulf;
			$this->tmp_data['user'][$u_obj->getId()] = (array)$u_obj->getObjectAsArray( $this->getColumnConfig() );

			$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
		}
		//Debug::Arr($this->tmp_data['user'], 'User Raw Data: ', __FILE__, __LINE__, __METHOD__,10);

		//Get verified timesheets for all pay periods considered in report.
		$pay_period_ids = array_keys( $pay_period_ids );
		if ( isset($pay_period_ids) AND count($pay_period_ids) > 0 ) {
			$pptsvlf = new PayPeriodTimeSheetVerifyListFactory(); 
			$pptsvlf->getByPayPeriodIdAndCompanyId( $pay_period_ids, $this->getUserObject()->getCompany() );
			if ( $pptsvlf->getRecordCount() > 0 ) {
				foreach( $pptsvlf->rs as $pptsv_obj ) {
					$pptsvlf->data = (array)$pptsv_obj;
					$pptsv_obj = $pptsvlf;
					$this->tmp_data['verified_timesheet'][$pptsv_obj->getUser()][$pptsv_obj->getPayPeriod()] = array(
																									'status' => $pptsv_obj->getVerificationStatusShortDisplay(),
																									'created_date' => $pptsv_obj->getCreatedDate(),
																									);
				}
			}
		}

		return TRUE;
	}

	//PreProcess data such as calculating additional columns from raw data etc...
	function _preProcess() {
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), count($this->tmp_data['punch']), NULL, ('Pre-Processing Data...') );

		//Merge time data with user data
		$key=0;
		if ( isset($this->tmp_data['punch']) ) {
			foreach( $this->tmp_data['punch'] as $user_id => $level_1 ) {
				if ( isset($this->tmp_data['user'][$user_id]) ) {
					foreach( $level_1 as $punch_control_id => $row ) {
						$date_columns = TTDate::getReportDates( NULL, $row['date_stamp'], FALSE, $this->getUserObject(), array('pay_period_start_date' => $row['pay_period_start_date'], 'pay_period_end_date' => $row['pay_period_end_date'], 'pay_period_transaction_date' => $row['pay_period_transaction_date']) );
						//$date_columns1 = TTDate::getReportDates( 'in_time_stamp', $row['in_time_stamp'], FALSE, $this->getUserObject() );
						//$date_columns2 = TTDate::getReportDates( 'out_time_stamp', $row['out_time_stamp'], FALSE, $this->getUserObject() );
						$processed_data  = array(
												//'pay_period' => array('sort' => $row['pay_period_start_date'], 'display' => TTDate::getDate('DATE', $row['pay_period_start_date'] ).' -> '. TTDate::getDate('DATE', $row['pay_period_end_date'] ) ),
												);

						if ( isset( $this->tmp_data['verified_timesheet'][$user_id][$row['pay_period_id']]) ) {
							$processed_data['verified_time_sheet'] = $this->tmp_data['verified_timesheet'][$user_id][$row['pay_period_id']]['status'];
							$processed_data['verified_time_sheet_date'] = $this->tmp_data['verified_timesheet'][$user_id][$row['pay_period_id']]['created_date'];
						} else {
							$processed_data['verified_time_sheet'] = ('No');
							$processed_data['verified_time_sheet_date'] = FALSE;
						}

						if ( isset( $this->tmp_data['verified_timesheet'][$user_id][$row['pay_period_id']] )
								AND isset($row['in_updated_date']) AND isset($row['out_updated_date'])
								AND ( $processed_data['verified_time_sheet_date'] < $row['in_updated_date'] OR $processed_data['verified_time_sheet_date'] < $row['out_updated_date'] ) ) {
							$processed_data['verified_time_sheet_tainted'] = TRUE;
						} else {
							$processed_data['verified_time_sheet_tainted'] = FALSE;
						}

						$this->data[] = array_merge( $this->tmp_data['user'][$user_id], $row, $date_columns, $processed_data );

						$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
						$key++;
					}
				}
			}
			unset($this->tmp_data, $row, $date_columns, $processed_data, $level_1);
		}
		//Debug::Arr($this->data, 'preProcess Data: ', __FILE__, __LINE__, __METHOD__,10);

		return TRUE;
	}
/*
	function _output( $format = NULL ) {
		return $this->_pdf();
	}
*/
}
?>
