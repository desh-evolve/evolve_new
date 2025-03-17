<?php

namespace App\Models\Report;

class ScheduleSummaryReport extends Report {

	function __construct() {
		$this->title = ('Schedule Summary Report');
		$this->file_name = 'schedule_summary_report';

		parent::__construct();

		return TRUE;
	}

	protected function _checkPermissions( $user_id, $company_id ) {
		if ( $this->getPermissionObject()->Check('report','enabled', $user_id, $company_id )
				AND $this->getPermissionObject()->Check('report','view_schedule_summary', $user_id, $company_id ) ) { //Piggyback on timesheet summary permissions.
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
										'-2080-schedule_branch_id' => ('Schedule Branch'),
										'-2090-schedule_department_id' => ('Schedule Department'),

										'-3000-status_id' => ('Schedule Status'),

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
				$retval = TTDate::getReportDateOptions( NULL, ('Date'), 15, TRUE );
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

										'-1600-branch' => ('Branch'),
										'-1610-department' => ('Department'),
										'-1620-schedule_policy' => ('Schedule Policy'),
										//'-1630-schedule_type' => ('Schedule Type'),
										'-1640-schedule_status' => ('Schedule Status'),
										'-1650-absence_policy' => ('Absence Policy'),
										'-1660-date_stamp' => ('Date'),
										'-1670-start_time' => ('Start Time'),
										'-1680-end_time' => ('End Time'),
							   );

				$retval = array_merge( $retval, $this->getOptions('date_columns') );
				ksort($retval);
				break;
			case 'dynamic_columns':
				$retval = array(
										//Dynamic - Aggregate functions can be used

										//Take into account wage groups. However hourly_rates for the same hour type, so we need to figure out an average hourly rate for each column?
										'-2010-hourly_rate' => ('Hourly Rate'),

										'-2100-total_time' => ('Total Time'),
										'-2110-total_time_wage' => ('Total Time Wage'),

										'-4000-total_shift' => ('Total Shifts'), //Group counter...
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
				break;
			case 'templates':
				$retval = array(
										'-1010-by_employee+work+total_time' => ('Work Time by Employee'),
										'-1020-by_employee+work+total_time+total_time_wage' => ('Work Time+Wage by Employee'),
										'-1030-by_title+work+total_time+total_time_wage' => ('Work Time+Wage by Title'),

										'-1110-by_date_by_full_name+work+total_time+total_time_wage' => ('Work Time+Wage by Date/Employee'),
										'-1120-by_full_name_by_date+work+total_time+total_time_wage' => ('Work Time+Wage by Employee/Date'),

										'-1210-by_branch+work+total_time+total_time_wage' => ('Work Time+Wage by Branch'),
										'-1220-by_department+work+total_time+total_time_wage' => ('Work Time+Wage by Department'),
										'-1230-by_branch_by_department+work+total_time+total_time_wage' => ('Work Time+Wage by Branch/Department'),

										'-1310-by_pay_period+work+total_time+total_time_wage' => ('Work Time+Wage by Pay Period'),
										'-1320-by_pay_period_by_employee+work+total_time+total_time_wage' => ('Work Time+Wage by Pay Period/Employee'),
										'-1330-by_pay_period_by_branch+work+total_time+total_time_wage' => ('Work  Time+Wage by Pay Period/Branch'),
										'-1340-by_pay_period_by_department+work+total_time+total_time_wage' => ('Work  Time+Wage by Pay Period/Department'),
										'-1350-by_pay_period_by_branch_by_department+work+total_time+total_time_wage' => ('Work  Time+Wage by Pay Period/Branch/Department'),

										'-1410-by_employee_by_pay_period+work+total_time+total_time_wage' => ('Work Time+Wage by Employee/Pay Period'),
										'-1420-by_branch_by_pay_period+work+total_time+total_time_wage' => ('Work Time+Wage by Branch/Pay Period'),
										'-1430-by_department_by_pay_period+work+total_time+total_time_wage' => ('Work Time+Wage by Department/Pay Period'),
										'-1440-by_branch_by_department_by_pay_period+work+total_time+total_time_wage' => ('Work Time+Wage by Branch/Department/Pay Period'),

										'-1510-by_title_by_start_time+work+total_time+total_time_wage+total_shift' => ('Work Time+Wage+Total Shifts by Title/Start Time'),
										'-1520-by_date_by_title+work+total_time+total_time_wage+total_shift' => ('Work Time+Wage+Total Shifts by Date/Title'),

										'-2010-by_employee+absence+total_time' => ('Absence Time by Employee'),
										'-2020-by_employee+absence+total_time+total_time_wage' => ('Absence Time+Wage by Employee'),
										'-2030-by_title+absence+total_time+total_time_wage' => ('Absence Time+Wage by Title'),

										'-2110-by_date_by_full_name+absence+total_time+total_time_wage' => ('Absence Time+Wage by Date/Employee'),
										'-2120-by_full_name_by_date+absence+total_time+total_time_wage' => ('Absence Time+Wage by Employee/Date'),

										'-2210-by_branch+absence+total_time+total_time_wage' => ('Absence Time+Wage by Branch'),
										'-2220-by_department+absence+total_time+total_time_wage' => ('Absence Time+Wage by Department'),
										'-2230-by_branch_by_department+absence+total_time+total_time_wage' => ('Absence Time+Wage by Branch/Department'),

										'-2310-by_pay_period+absence+total_time+total_time_wage' => ('Absence Time+Wage by Pay Period'),
										'-2320-by_pay_period_by_employee+absence+total_time+total_time_wage' => ('Absence Time+Wage by Pay Period/Employee'),
										'-2330-by_pay_period_by_branch+absence+total_time+total_time_wage' => ('Work Time+Wage by Pay Period/Branch'),
										'-2340-by_pay_period_by_department+absence+total_time+total_time_wage' => ('Work Time+Wage by Pay Period/Department'),
										'-2350-by_pay_period_by_branch_by_department+absence+total_time+total_time_wage' => ('Work Time+Wage by Pay Period/Branch/Department'),

										'-2410-by_employee_by_pay_period+absence+total_time+total_time_wage' => ('Absence Time+Wage by Employee/Pay Period'),
										'-2420-by_branch_by_pay_period+absence+total_time+total_time_wage' => ('Absence Time+Wage by Branch/Pay Period'),
										'-2430-by_department_by_pay_period+absence+total_time+total_time_wage' => ('Absence Time+Wage by Department/Pay Period'),
										'-2440-by_branch_by_department_by_pay_period+absence+total_time+total_time_wage' => ('Absence Time+Wage by Branch/Department/Pay Period'),


							   );

				break;
			case 'template_config':
				$template = strtolower( Misc::trimSortPrefix( $params['template'] ) );
				if ( isset($template) AND $template != '' ) {
					switch( $template ) {
						//case 'by_employee+actual_time':
						//	break;
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
										case 'total_time_wage':
											$retval['columns'][] = 'total_time_wage';
											break;
										case 'absence_policy':
											$retval['columns'][] = 'absence_policy';
											break;
										//Filter
										case 'work':
											$retval['filter']['status_id'] = array(10);
											break;
										case 'absence':
											$retval['filter']['status_id'] = array(20);
											break;
										case 'total_shift':
											$retval['columns'][] = 'total_shift';
											break;

										//Group By
										//SubTotal
										//Sort
										case 'by_employee':
											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';

											$retval['group'][] = 'last_name';
											$retval['group'][] = 'first_name';

											$retval['sort'][] = array('last_name' => 'asc');
											$retval['sort'][] = array('first_name' => 'asc');
											break;
										case 'by_title':
											$retval['columns'][] = 'title';

											$retval['group'][] = 'title';

											$retval['sort'][] = array('title' => 'asc');
											break;
										case 'by_branch':
											$retval['columns'][] = 'branch';

											$retval['group'][] = 'branch';

											$retval['sort'][] = array('branch' => 'asc');
											break;
										case 'by_department':
											$retval['columns'][] = 'department';

											$retval['group'][] = 'department';

											$retval['sort'][] = array('department' => 'asc');
											break;
										case 'by_branch_by_department':
											$retval['columns'][] = 'branch';
											$retval['columns'][] = 'department';

											$retval['group'][] = 'branch';
											$retval['group'][] = 'department';

											$retval['sub_total'][] = 'branch';

											$retval['sort'][] = array('branch' => 'asc');
											$retval['sort'][] = array('department' => 'asc');
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
											$retval['columns'][] = 'branch';

											$retval['group'][] = 'pay_period';
											$retval['group'][] = 'branch';

											$retval['sub_total'][] = 'pay_period';

											$retval['sort'][] = array('pay_period' => 'asc');
											$retval['sort'][] = array('branch' => 'asc');
											break;
										case 'by_pay_period_by_department':
											$retval['columns'][] = 'pay_period';
											$retval['columns'][] = 'department';

											$retval['group'][] = 'pay_period';
											$retval['group'][] = 'department';

											$retval['sub_total'][] = 'pay_period';

											$retval['sort'][] = array('pay_period' => 'asc');
											$retval['sort'][] = array('department' => 'asc');
											break;
										case 'by_pay_period_by_branch_by_department':
											$retval['columns'][] = 'pay_period';
											$retval['columns'][] = 'branch';
											$retval['columns'][] = 'department';

											$retval['group'][] = 'pay_period';
											$retval['group'][] = 'branch';
											$retval['group'][] = 'department';

											$retval['sub_total'][] = 'pay_period';
											$retval['sub_total'][] = 'branch';

											$retval['sort'][] = array('pay_period' => 'asc');
											$retval['sort'][] = array('branch' => 'asc');
											$retval['sort'][] = array('department' => 'asc');
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
											$retval['columns'][] = 'branch';
											$retval['columns'][] = 'pay_period';

											$retval['group'][] = 'branch';
											$retval['group'][] = 'pay_period';

											$retval['sub_total'][] = 'branch';

											$retval['sort'][] = array('branch' => 'asc');
											$retval['sort'][] = array('pay_period' => 'asc');
											break;
										case 'by_department_by_pay_period':
											$retval['columns'][] = 'department';
											$retval['columns'][] = 'pay_period';

											$retval['group'][] = 'department';
											$retval['group'][] = 'pay_period';

											$retval['sub_total'][] = 'department';

											$retval['sort'][] = array('department' => 'asc');
											$retval['sort'][] = array('pay_period' => 'asc');
											break;
										case 'by_branch_by_department_by_pay_period':
											$retval['columns'][] = 'branch';
											$retval['columns'][] = 'department';
											$retval['columns'][] = 'pay_period';

											$retval['group'][] = 'branch';
											$retval['group'][] = 'department';
											$retval['group'][] = 'pay_period';

											$retval['sub_total'][] = 'branch';
											$retval['sub_total'][] = 'department';

											$retval['sort'][] = array('branch' => 'asc');
											$retval['sort'][] = array('department' => 'asc');
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
										case 'by_date_by_title':
											$retval['columns'][] = 'date_stamp';
											$retval['columns'][] = 'title';

											$retval['group'][] = 'date_stamp';
											$retval['group'][] = 'title';

											$retval['sub_total'][] = 'date_stamp';

											$retval['sort'][] = array('date_stamp' => 'asc');
											$retval['sort'][] = array('title' => 'asc');
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
										case 'by_title_by_start_time':
											$retval['columns'][] = 'title';
											$retval['columns'][] = 'start_time';
											$retval['columns'][] = 'total_shift';

											$retval['group'][] = 'title';
											$retval['group'][] = 'start_time';

											$retval['sub_total'][] = 'title';

											$retval['sort'][] = array('title' => 'asc');
											$retval['sort'][] = array('start_time' => 'asc');
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
		$this->tmp_data = array('schedule' => array(), 'user' => array(), 'total_shift' => array() );

		$columns = $this->getColumnConfig();
		$filter_data = $this->getFilterConfig();

		if ( $this->getPermissionObject()->Check('schedule','view') == FALSE OR $this->getPermissionObject()->Check('wage','view') == FALSE ) {
			$hlf = new HierarchyListFactory();
			$permission_children_ids = $wage_permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $this->getUserObject()->getCompany(), $this->getUserObject()->getID() );
			Debug::Arr($permission_children_ids,'Permission Children Ids:', __FILE__, __LINE__, __METHOD__,10);
		} else {
			//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
			$permission_children_ids = array();
			$wage_permission_children_ids = array();
		}
		if ( $this->getPermissionObject()->Check('schedule','view') == FALSE ) {
			if ( $this->getPermissionObject()->Check('schedule','view_child') == FALSE ) {
				$permission_children_ids = array();
			}
			if ( $this->getPermissionObject()->Check('schedule','view_own') ) {
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

		if ( $this->getUserObject()->getCompanyObject()->getProductEdition() == 20 ) {
			$jlf = new JobListFactory();
			$job_status_options = $jlf->getOptions('status');
		} else {
			$job_status_options = array();
		}

		$pay_period_ids = array();

		$slf = new ScheduleListFactory();
		$slf->getScheduleSummaryReportByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text(' Total Rows: '. $slf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), $slf->getRecordCount(), NULL, ('Retrieving Data...') );
		if ( $slf->getRecordCount() > 0 ) {
			foreach ( $slf as $key => $s_obj ) {
				$hourly_rate = 0;
				if ( $wage_permission_children_ids === TRUE OR in_array( $s_obj->getColumn('user_id'), $wage_permission_children_ids) ) {
					$hourly_rate = $s_obj->getColumn( 'user_wage_hourly_rate' );
				}

				$this->tmp_data['schedule'][$s_obj->getColumn('user_id')][] = array(
					'user_id' => $s_obj->getColumn('user_id'),
					'group' => $s_obj->getColumn('group'),
					'branch' => $s_obj->getColumn('branch'),
					'department' => $s_obj->getColumn('department'),
					'job' => $s_obj->getColumn('job'),
					'job_status_id' => Option::getByKey($s_obj->getColumn('job_status_id'), $job_status_options, NULL ),
					'job_manual_id' => $s_obj->getColumn('job_manual_id'),
					'job_description' => $s_obj->getColumn('job_description'),
					'job_branch' => $s_obj->getColumn('job_branch'),
					'job_department' => $s_obj->getColumn('job_department'),
					'job_group' => $s_obj->getColumn('job_group'),
					'job_item' => $s_obj->getColumn('job_item'),
					'quantity' => $s_obj->getColumn('quantity'),
					'bad_quantity' => $s_obj->getColumn('bad_quantity'),

					'total_time' => $s_obj->getColumn('total_time'),
					'total_time_wage' => Misc::MoneyFormat( bcmul( TTDate::getHours( $s_obj->getColumn('total_time') ), $hourly_rate ), FALSE ),

					'other_id1' => $s_obj->getColumn('other_id1'),
					'other_id2' => $s_obj->getColumn('other_id2'),
					'other_id3' => $s_obj->getColumn('other_id3'),
					'other_id4' => $s_obj->getColumn('other_id4'),
					'other_id5' => $s_obj->getColumn('other_id5'),

					'date_stamp' => TTDate::strtotime( $s_obj->getColumn('date_stamp') ),

					'schedule_policy' => $s_obj->getColumn('schedule_policy'),
					'absence_policy' => $s_obj->getColumn('absence_policy'),

					//'schedule_type' => Option::getByKey( $s_obj->getType(), $s_obj->getOptions('type'), NULL ), //Recurring/Scheduled?
					'schedule_status' => Option::getByKey( $s_obj->getStatus(), $s_obj->getOptions('status'), NULL ),

					'start_time' => TTDate::getDate( 'TIME', TTDate::strtotime( $s_obj->getColumn('start_time') ) ),
					'end_time' => TTDate::getDate( 'TIME', TTDate::strtotime( $s_obj->getColumn('end_time') ) ),

					'user_wage_id' => $s_obj->getColumn('user_wage_id'),
					'hourly_rate' => Misc::MoneyFormat( $hourly_rate, FALSE ),

					'pay_period_start_date' => strtotime( $s_obj->getColumn('pay_period_start_date') ),
					'pay_period_end_date' => strtotime( $s_obj->getColumn('pay_period_end_date') ),
					'pay_period_transaction_date' => strtotime( $s_obj->getColumn('pay_period_transaction_date') ),
					'pay_period' => strtotime( $s_obj->getColumn('pay_period_transaction_date') ),
					'pay_period_id' => $s_obj->getColumn('pay_period_id'),

					'total_shift' => 1,
					);
				unset($hourly_rate);

				$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
			}
		}
		//Debug::Arr($this->tmp_data['schedule'], 'Schedule Raw Data: ', __FILE__, __LINE__, __METHOD__,10);

		//Get user data for joining.
		$ulf = new UserListFactory();
		$ulf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text(' User Total Rows: '. $ulf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), $ulf->getRecordCount(), NULL, ('Retrieving Data...') );
		foreach ( $ulf as $key => $u_obj ) {
			$this->tmp_data['user'][$u_obj->getId()] = (array)$u_obj->getObjectAsArray( $this->getColumnConfig() );

			//$this->tmp_data['user'][$u_obj->getId()]['total_shift'] = 1;

			$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
		}
		//Debug::Arr($this->tmp_data['user'], 'User Raw Data: ', __FILE__, __LINE__, __METHOD__,10);

		return TRUE;
	}

	//PreProcess data such as calculating additional columns from raw data etc...
	function _preProcess() {
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), count($this->tmp_data['schedule']), NULL, ('Pre-Processing Data...') );

		//Merge time data with user data
		$key=0;
		if ( isset($this->tmp_data['schedule']) ) {
			foreach( $this->tmp_data['schedule'] as $user_id => $level_1 ) {
				if ( isset($this->tmp_data['user'][$user_id]) ) {
					foreach( $level_1 as $key => $row ) {
					$date_columns = TTDate::getReportDates( NULL, $row['date_stamp'], FALSE, $this->getUserObject(), array('pay_period_start_date' => $row['pay_period_start_date'], 'pay_period_end_date' => $row['pay_period_end_date'], 'pay_period_transaction_date' => $row['pay_period_transaction_date']) );
					$processed_data  = array(
											//'pay_period' => array('sort' => $row['pay_period_start_date'], 'display' => TTDate::getDate('DATE', $row['pay_period_start_date'] ).' -> '. TTDate::getDate('DATE', $row['pay_period_end_date'] ) ),
											);

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
