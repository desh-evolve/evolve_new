<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: TimesheetSummary.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('report','enabled')
		OR !$permission->Check('report','view_timesheet_summary') ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', __($title = 'TimeSheet Summary Report'));  // See index.php


/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'generic_data',
												'filter_data'

												) ) );

URLBuilder::setURL($_SERVER['SCRIPT_NAME'],
											array(
													'filter_data' => $filter_data
//													'sort_column' => $sort_column,
//													'sort_order' => $sort_order,
												) );

$static_columns = array(			'-1000-full_name' => _('Full Name'),
									'-1002-employee_number' => _('Employee #'),
									'-1005-status' => _('Status'),
									'-1010-title' => _('Title'),
									'-1020-province' => _('Province/State'),
									'-1030-country' => _('Country'),
									'-1039-group' => _('Group'),
									'-1040-default_branch' => _('Default Branch'),
									'-1050-default_department' => _('Default Department'),
									'-1060-verified_time_sheet' => _('Verified TimeSheet'),
									'-1062-pending_request' => _('Pending Requests'),
									'-1065-pay_period' => _('Pay Period')
									);

$columns = array(					'-1070-schedule_working' => _('Scheduled Time'),
									'-1080-schedule_absence' => _('Scheduled Absence'),
									'-1085-worked_days' => _('Worked Days'),
									'-1090-worked_time' => _('Worked Time'),
									'-1100-actual_time' => _('Actual Time'),
									'-1110-actual_time_diff' => _('Actual Time Difference'),
									'-1120-actual_time_diff_wage' => _('Actual Time Difference Wage'),
									'-1130-paid_time' => _('Paid Time'),
									'-1140-regular_time' => _('Regular Time'),
									);

$columns = Misc::prependArray( $static_columns, $columns);

//Get all Overtime policies.
$otplf = new OverTimePolicyListFactory();
$otplf->getByCompanyId($current_company->getId());
if ( $otplf->getRecordCount() > 0 ) {
	foreach ($otplf as $otp_obj ) {
		$otp_columns['over_time_policy-'.$otp_obj->getId()] = $otp_obj->getName();
	}

	$columns = array_merge( $columns, $otp_columns);
}

//Get all Premium policies.
$pplf = new PremiumPolicyListFactory();
$pplf->getByCompanyId($current_company->getId());
if ( $pplf->getRecordCount() > 0 ) {
	foreach ($pplf as $pp_obj ) {
		$pp_columns['premium_policy-'.$pp_obj->getId()] = $pp_obj->getName();
	}

	$columns = array_merge( $columns, $pp_columns);
}


//Get all Absence Policies.
$aplf = new AbsencePolicyListFactory();
$aplf->getByCompanyId($current_company->getId());
if ( $aplf->getRecordCount() > 0 ) {
	foreach ($aplf as $ap_obj ) {
		$ap_columns['absence_policy-'.$ap_obj->getId()] = $ap_obj->getName();
	}

	$columns = array_merge( $columns, $ap_columns);
}


$default_start_date = TTDate::getBeginMonthEpoch();
$default_end_date = TTDate::getEndMonthEpoch();

//Get all pay periods
$pplf = new PayPeriodListFactory();
$pplf->getByCompanyId( $current_company->getId() );
if ( $pplf->getRecordCount() > 0 ) {
	$pp=0;
	foreach ($pplf as $pay_period_obj) {
		$pay_period_ids[] = $pay_period_obj->getId();
		$pay_period_end_dates[$pay_period_obj->getId()] = $pay_period_obj->getEndDate();

		if ( $pp == 0 ) {
			$default_start_date = $pay_period_obj->getStartDate();
			$default_end_date = $pay_period_obj->getEndDate();
		}
		$pp++;
	}

	$pplf = new PayPeriodListFactory();
	$pay_period_options = $pplf->getByIdListArray($pay_period_ids, NULL, array('start_date' => 'desc'));
} else {
	$pay_period_options = array();
}

if ( isset($filter_data['start_date']) ) {
	$filter_data['start_date'] = TTDate::parseDateTime($filter_data['start_date']);
}

if ( isset($filter_data['end_date']) ) {
	$filter_data['end_date'] = TTDate::parseDateTime($filter_data['end_date']);
}

$filter_data = Misc::preSetArrayValues( $filter_data, array('include_user_ids', 'exclude_user_ids', 'user_status_ids', 'group_ids', 'branch_ids', 'department_ids', 'user_title_ids', 'pay_period_ids', 'column_ids' ), array());

//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
$permission_children_ids = array();
$wage_permission_children_ids = array();
if ( $permission->Check('punch','view') == FALSE ) {
	$hlf = new HierarchyListFactory();
	$permission_children_ids = $wage_permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );
	Debug::Arr($permission_children_ids,'Permission Children Ids:', __FILE__, __LINE__, __METHOD__,10);

	if ( $permission->Check('punch','view_child') == FALSE ) {
		$permission_children_ids = array();
	}
	if ( $permission->Check('punch','view_own') ) {
		$permission_children_ids[] = $current_user->getId();
	}

	$filter_data['permission_children_ids'] = $permission_children_ids;
}

//Get Wage Permission Hierarchy Children first, as this can be used for viewing, or editing.
if ( $permission->Check('wage','view') == FALSE ) {
	if ( $permission->Check('wage','view_child') == FALSE ) {
		$wage_permission_children_ids = array();
	}
	if ( $permission->Check('wage','view_own') ) {
		$wage_permission_children_ids[] = $current_user->getId();
	}

	$wage_filter_data['permission_children_ids'] = $wage_permission_children_ids;
}

$ugdlf = new UserGenericDataListFactory();
$ugdf = new UserGenericDataFactory();

$action = Misc::findSubmitButton();
switch ($action) {
	case 'export':
	case 'display_report':
		//Debug::setVerbosity(11);
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($filter_data, 'Filter Data', __FILE__, __LINE__, __METHOD__,10);

/*
	protected $status_options = array(
										10 => 'System',
										20 => 'Worked',
										30 => 'Absence'
									);

	protected $type_options = array(
										10 => 'Total',
										20 => 'Regular',
										30 => 'Overtime'
									);
*/

		$ulf = new UserListFactory();
		$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
		if ( $ulf->getRecordCount() > 0 ) {
			if ( isset($filter_data['date_type']) AND $filter_data['date_type'] == 'pay_period_ids' ) {
				unset($filter_data['start_date']);
				unset($filter_data['end_date']);
			} else {
				unset($filter_data['pay_period_ids']);
			}

			foreach( $ulf as $u_obj ) {
				$filter_data['user_id'][] = $u_obj->getId();
			}

			if ( isset($filter_data['pay_period_ids']) ) {
				//Trim sort prefix from selected pay periods.
				$tmp_filter_pay_period_ids = $filter_data['pay_period_ids'];
				$filter_data['pay_period_ids'] = array();
				foreach( $tmp_filter_pay_period_ids as $key => $filter_pay_period_id) {
					$filter_data['pay_period_ids'][] = Misc::trimSortPrefix($filter_pay_period_id);
				}
				unset($key, $tmp_filter_pay_period_ids, $filter_pay_period_id);
			}

			//Get greatest end date of the selected ones.
			if ( isset($filter_data['pay_period_ids']) AND count($filter_data['pay_period_ids']) > 0 ) {
				if ( in_array('-1', $filter_data['pay_period_ids']) ) {
					$end_date = time();
				} else {
					$i=0;
					foreach ( $filter_data['pay_period_ids'] as $tmp_pay_period_id ) {
						$tmp_pay_period_id = Misc::trimSortPrefix($tmp_pay_period_id);
						if ( $i == 0 ) {
							$end_date = $pay_period_end_dates[$tmp_pay_period_id];
						} else {
							if ( $pay_period_end_dates[$tmp_pay_period_id] > $end_date ) {
								$end_date = $pay_period_end_dates[$tmp_pay_period_id];
							}
						}

						$i++;
					}
					unset($tmp_pay_period_id, $i);
				}
			} else {
				$end_date = $filter_data['end_date'];
			}

            //Make sure we account for wage permissions.
            if ( $permission->Check('wage','view') == TRUE ) {
                $wage_filter_data['permission_children_ids'] = $filter_data['user_id'];
            }
			$uwlf = new UserWageListFactory();
			$uwlf->getLastWageByUserIdAndDate( $wage_filter_data['permission_children_ids'], $end_date );
			if ( $uwlf->getRecordCount() > 0 ) {
				foreach($uwlf as $uw_obj) {
					$user_wage[$uw_obj->getUser()] = $uw_obj->getBaseCurrencyHourlyRate( $uw_obj->getHourlyRate() );
				}
			}
			unset($end_date);
			//var_dump($user_wage);

			$pending_requests = array();
			if ( isset($filter_data['pay_period_ids']) AND count($filter_data['pay_period_ids']) > 0 ) {
				//Get all pending requests
				$rlf = new RequestListFactory();
				$rlf->getSumByPayPeriodIdAndStatus( $filter_data['pay_period_ids'], 30 );
				if ( $rlf->getRecordCount() > 0 ) {
					$r_obj = $rlf->getCurrent();
					$pending_requests[$r_obj->getColumn('pay_period_id')] = $r_obj->getColumn('total');
				}
			}

			$slf = new ScheduleListFactory();
			//$slf->getReportByPayPeriodIdAndUserId($filter_data['pay_period_ids'], $filter_data['user_ids']);
			$slf->getReportByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
			if ( $slf->getRecordCount() > 0 ) {
				foreach($slf as $s_obj) {
					$user_id = $s_obj->getColumn('user_id');
					$pay_period_id = $s_obj->getColumn('pay_period_id');
					$status_id = $s_obj->getColumn('status_id');
					$status = strtolower( Option::getByKey($status_id, $s_obj->getOptions('status') ) );

					$schedule_rows[$user_id][$pay_period_id][$status] = $s_obj->getColumn('total_time');

					unset($user_id, $pay_period_id, $status_id, $status);
				}
			}
			//var_dump($schedule_rows);

			$pay_period_ids = array();

			$udtlf = new UserDateTotalListFactory();
			//$udtlf->getReportByPayPeriodIDListAndUserIdList($filter_data['pay_period_ids'], $filter_data['user_ids'], array($filter_data['primary_sort'] => 'asc') );
			$udtlf->getReportByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
			foreach ($udtlf as $udt_obj ) {
				$user_id = $udt_obj->getColumn('id');
				$pay_period_id = $udt_obj->getColumn('pay_period_id');
				$pay_period_ids[$pay_period_id] = NULL; //Keep list of all pay periods for timesheet verify.

				$status_id = $udt_obj->getColumn('status_id');
				$type_id = $udt_obj->getColumn('type_id');

				if ( $status_id == 10 AND $type_id == 10 ) {
					$column = 'paid_time';
				} elseif ($status_id == 10 AND $type_id == 20) {
					$column = 'regular_time';
				} elseif ($status_id == 10 AND $type_id == 30) {
					$column = 'over_time_policy-'. $udt_obj->getColumn('over_time_policy_id');
				} elseif ($status_id == 10 AND $type_id == 40) {
					$column = 'premium_policy-'. $udt_obj->getColumn('premium_policy_id');
				} elseif ($status_id == 30 AND $type_id == 10) {
					$column = 'absence_policy-'. $udt_obj->getColumn('absence_policy_id');
				} elseif ( ($status_id == 20 AND $type_id == 10 ) OR ($status_id == 10 AND $type_id == 100 ) ) {
					$column = 'worked_time';
				} else {
					$column = NULL;
				}

				//Debug::Text('Column: '. $column .' Status ID: '. $status_id .' Type ID: '. $type_id , __FILE__, __LINE__, __METHOD__,10);

				if ( $column == 'worked_time' ) {
					//Handle actual time diff/wage here.
					if ( isset($tmp_rows[$user_id][$pay_period_id][$column]) ) {
						$tmp_rows[$user_id][$pay_period_id][$column] += (int)$udt_obj->getColumn('total_time');
					} else {
						$tmp_rows[$user_id][$pay_period_id][$column] = (int)$udt_obj->getColumn('total_time');
					}

					if ( isset($tmp_rows[$user_id][$pay_period_id]['actual_time']) ) {
						$tmp_rows[$user_id][$pay_period_id]['actual_time'] += (int)$udt_obj->getColumn('actual_total_time');
					} else {
						$tmp_rows[$user_id][$pay_period_id]['actual_time'] = (int)$udt_obj->getColumn('actual_total_time');
					}

					$actual_time_diff = $udt_obj->getColumn('actual_total_time') - $udt_obj->getColumn('total_time');

					if ( isset($tmp_rows[$user_id][$pay_period_id]['actual_time_diff'] ) ) {
						$tmp_rows[$user_id][$pay_period_id]['actual_time_diff']  += $actual_time_diff;
					} else {
						$tmp_rows[$user_id][$pay_period_id]['actual_time_diff']  = $actual_time_diff;
					}

					if ( isset($user_wage[$user_id]) ) {
						$tmp_rows[$user_id][$pay_period_id]['actual_time_diff_wage'] = Misc::MoneyFormat( TTDate::getHours($tmp_rows[$user_id][$pay_period_id]['actual_time_diff']) * $user_wage[$user_id], FALSE );
					} else {
						$tmp_rows[$user_id][$pay_period_id]['actual_time_diff_wage'] = Misc::MoneyFormat( 0, FALSE );
					}
					unset($actual_time_diff);
				} elseif ( $column != NULL )  {
					if ( isset($tmp_rows[$user_id][$pay_period_id][$column]) ) {
						$tmp_rows[$user_id][$pay_period_id][$column] += $udt_obj->getColumn('total_time');
					} else {
						$tmp_rows[$user_id][$pay_period_id][$column] = $udt_obj->getColumn('total_time');
					}
				}

				if ( isset($schedule_rows[$user_id][$pay_period_id]['working']) ) {
					$tmp_rows[$user_id][$pay_period_id]['schedule_working'] = $schedule_rows[$user_id][$pay_period_id]['working'];
				} else {
					$tmp_rows[$user_id][$pay_period_id]['schedule_working'] = NULL;
				}

				if ( isset($schedule_rows[$user_id][$pay_period_id]['absence']) ) {
					$tmp_rows[$user_id][$pay_period_id]['schedule_absence'] = $schedule_rows[$user_id][$pay_period_id]['absence'];
				} else {
					$tmp_rows[$user_id][$pay_period_id]['schedule_absence'] = NULL;
				}

				if ( isset($tmp_rows[$user_id][$pay_period_id]['worked_days']) ) {
					$tmp_rows[$user_id][$pay_period_id]['worked_days'] += $udt_obj->getColumn('worked_days');
				} else {
					$tmp_rows[$user_id][$pay_period_id]['worked_days'] = $udt_obj->getColumn('worked_days');
				}
			}
			//print_r($tmp_rows);

			$ulf = new UserListFactory();

			$utlf = new UserTitleListFactory();
			$title_options = $utlf->getByCompanyIdArray( $current_company->getId() );

			$uglf = new UserGroupListFactory();
			$group_options = $uglf->getArrayByNodes( FastTree::FormatArray( $uglf->getByCompanyIdArray( $current_company->getId() ), 'no_tree_text', TRUE) );

			$blf = new BranchListFactory();
			$branch_options = $blf->getByCompanyIdArray( $current_company->getId() );

			$dlf = new DepartmentListFactory();
			$department_options = $dlf->getByCompanyIdArray( $current_company->getId() );

			//Get verified timesheets
			//Ignore if more then one pay period is selected
			$verified_time_sheets = NULL;
			if ( isset($pay_period_ids) AND count($pay_period_ids) > 0 ) {
				$pptsvlf = new PayPeriodTimeSheetVerifyListFactory();
				$pptsvlf->getByPayPeriodIdAndCompanyId( array_keys($pay_period_ids), $current_company->getId() );
				if ( $pptsvlf->getRecordCount() > 0 ) {
					foreach( $pptsvlf as $pptsv_obj ) {
						$verified_time_sheets[$pptsv_obj->getUser()][$pptsv_obj->getPayPeriod()] = $pptsv_obj->getStatus();
					}
				}
			}

			if ( isset($tmp_rows) ) {
				$x=0;
				foreach($tmp_rows as $user_id => $data_a ) {
					$user_obj = $ulf->getById( $user_id )->getCurrent();

					foreach($data_a as $pay_period_id => $data_b ) {
						$rows[$x]['user_id'] = $user_obj->getId();
						$rows[$x]['full_name'] = $user_obj->getFullName(TRUE);
						$rows[$x]['employee_number'] = $user_obj->getEmployeeNumber();
						$rows[$x]['status'] = Option::getByKey( $user_obj->getStatus(), $user_obj->getOptions('status') );

						//$rows[$x]['province'] = Option::getByKey($user_obj->getProvince(), $user_obj->getCompanyObject()->getOptions('province', $user_obj->getCountry() ) );
						//$rows[$x]['country'] = Option::getByKey($user_obj->getCountry(), $user_obj->getCompanyObject()->getOptions('country') );
						$rows[$x]['province'] = $user_obj->getProvince();
						$rows[$x]['country'] = $user_obj->getCountry();

						$rows[$x]['pay_period_id'] = $pay_period_id;
						$rows[$x]['pay_period_order'] = Option::getByKey($pay_period_id, $pay_period_end_dates, NULL );
						$rows[$x]['pay_period'] = Option::getByKey($pay_period_id, $pay_period_options, NULL );

						$rows[$x]['title'] = Option::getByKey($user_obj->getTitle(), $title_options, NULL );
						$rows[$x]['group'] = Option::getByKey($user_obj->getGroup(), $group_options, NULL );
						$rows[$x]['default_branch'] =  Option::getByKey($user_obj->getDefaultBranch(), $branch_options, NULL );
						$rows[$x]['default_department'] = Option::getByKey($user_obj->getDefaultDepartment(), $department_options, NULL );

						if ( $verified_time_sheets !== NULL AND isset($verified_time_sheets[$user_id][$pay_period_id]) ) {
							if ( $verified_time_sheets[$user_id][$pay_period_id] == 50 ) {
								$rows[$x]['verified_time_sheet'] = _('Yes');
							} elseif ( $verified_time_sheets[$user_id][$pay_period_id] == 30 OR $verified_time_sheets[$user_id][$pay_period_id] == 45 ) {
								$rows[$x]['verified_time_sheet'] = _('Pending');
							} else {
								$rows[$x]['verified_time_sheet'] = _('Declined');
							}
						} else {
							$rows[$x]['verified_time_sheet'] = _('No');
						}

						if ( isset($pending_requests[$pay_period_id]) ) {
							$rows[$x]['pending_request'] = $pending_requests[$pay_period_id];
						} else {
							$rows[$x]['pending_request'] = 0;
						}

						foreach($data_b as $column => $total_time) {
							$rows[$x][$column] = $total_time;
						}

						$x++;
					}
				}
			}
			//var_dump($rows);
			unset($tmp_rows);

			if ( isset($filter_data['primary_group_by']) AND $filter_data['primary_group_by'] != '0' ) {
				Debug::Text('Primary Grouping Data By: '. $filter_data['primary_group_by'], __FILE__, __LINE__, __METHOD__,10);

				$ignore_elements = array_keys($static_columns);

				$filter_data['column_ids'] = array_diff( $filter_data['column_ids'], $ignore_elements );

				//Add the group by element back in
				if ( isset($filter_data['secondary_group_by']) AND $filter_data['secondary_group_by'] != 0 ) {
					array_unshift( $filter_data['column_ids'], $filter_data['primary_group_by'], $filter_data['secondary_group_by'] );
				} else {
					array_unshift( $filter_data['column_ids'], $filter_data['primary_group_by'] );
				}

				$rows = Misc::ArrayGroupBy( $rows, array(Misc::trimSortPrefix($filter_data['primary_group_by']),Misc::trimSortPrefix($filter_data['secondary_group_by'])), Misc::trimSortPrefix($ignore_elements) );
			}

			if ( isset($rows) ) {
				foreach($rows as $row) {
					$tmp_rows[] = $row;
				}
				//var_dump($tmp_rows);

				$special_sort_columns = array('pay_period');
				if ( in_array( Misc::trimSortPrefix($filter_data['primary_sort']), $special_sort_columns ) ) {
						$filter_data['primary_sort'] = $filter_data['primary_sort'].'_order';
				}
				if ( in_array( Misc::trimSortPrefix($filter_data['secondary_sort']), $special_sort_columns ) ) {
						$filter_data['secondary_sort'] = $filter_data['secondary_sort'].'_order';
				}

				$rows = Sort::Multisort($tmp_rows, Misc::trimSortPrefix($filter_data['primary_sort']), Misc::trimSortPrefix($filter_data['secondary_sort']), $filter_data['primary_sort_dir'], $filter_data['secondary_sort_dir']);

				$total_row = Misc::ArrayAssocSum($rows, NULL, 2);

				$last_row = count($rows);
				$rows[$last_row] = $total_row;
				foreach ($static_columns as $static_column_key => $static_column_val) {
					$rows[$last_row][Misc::trimSortPrefix($static_column_key)] = NULL;
				}
				unset($static_column_key, $static_column_val);

				//Convert units
				$tmp_rows = $rows;
				unset($rows);

               		$trimmed_static_columns = array_keys( Misc::trimSortPrefix($static_columns) );
				foreach($tmp_rows as $row ) {
					foreach($row as $column => $column_data) {
						if ( !strstr($column, 'wage') AND !strstr($column, 'worked_days') AND !in_array( $column, $trimmed_static_columns) ) {
							$column_data = TTDate::getTimeUnit( $column_data );
						}

						$row_columns[$column] = $column_data;
						unset($column, $column_data);
					}

					$rows[] = $row_columns;
					unset($row_columns);
				}
		
			}
		}

		//var_dump($rows);
		foreach( $filter_data['column_ids'] as $column_key ) {
			$filter_columns[Misc::trimSortPrefix($column_key)] = $columns[$column_key];
		}


//                                     echo '<pre>'; print_r($filter_data['export_type']); echo '<pre>'; die;
		if ( $action == 'export'  ) {
                    
                    if( $filter_data['export_type'] == 'pdfp'){
                        if ( isset($rows) AND isset($filter_columns) ) {

				Debug::Text('Exporting as PDF', __FILE__, __LINE__, __METHOD__,10);
                                
                                $tssr= new TimesheetSummaryReport();//new code                                
                                $output = $tssr->Array2PDF($rows, $filter_columns, $current_user, $current_company);//new code                               
                                                                                               
                                if ( Debug::getVerbosity() < 11 ) {                                    
                                    Misc::FileDownloadHeader('OverTimeReport.pdf', 'application/pdf', strlen($output));
                                    echo $output;
                                    exit;                           
                                }                        
                        }else {
                                echo _('No PDF Data To Export!') ."<br>\n";                                    
                                }
                    }
                     if( $filter_data['export_type'] == 'csv'){
			if ( isset($rows) AND isset($filter_columns) ) {
				Debug::Text('Exporting as CSV', __FILE__, __LINE__, __METHOD__,10);
				$data = Misc::Array2CSV( $rows, $filter_columns );

				Misc::FileDownloadHeader('report.csv', 'application/csv', strlen($data) );
				echo $data;
			} else {
				echo __("No Data To Export!") ."<br>\n";
			}
                     }
                     //FL ADDED FOR LATE SUMMARY REPORT - 20160517
                     if( $filter_data['export_type'] == 'pdfOTreport'){
                        if ( isset($rows) AND isset($filter_columns) ) {

				Debug::Text('Exporting as PDF', __FILE__, __LINE__, __METHOD__,10);
                                //FL HARD CODED $filter_columns FOR OT REPORT OF NATIONAL PVC REPORT FORMAT
                                $filter_columns  = array(
                                                        'full_name' => 'Full Name',
                                                        'pay_period' => 'Pay Period',
                                                        'over_time_policy-1' => 'Holiday OT', 
                                                        'over_time_policy-2' => 'Weekday OT', 
                                                        'over_time_policy-4' => 'Sunday OT'
                                                        );
//                               echo '<pre>'; print_r($filter_columns); echo '<pre>'; die;
                                $tssr= new TimesheetSummaryReport();//new code                                
                                $output = $tssr->Array2PDF($rows, $filter_columns, $current_user, $current_company,'Monthly OT Report');//new code                               
                                                                                               
                                if ( Debug::getVerbosity() < 11 ) {                                    
                                    Misc::FileDownloadHeader('OverTimeReport.pdf', 'application/pdf', strlen($output));
                                    echo $output;
                                    exit;                           
                                }                        
                        }else {
                                echo _('No PDF Data To Export!') ."<br>\n";                                    
                                }
                    }
		} else {
			$smarty->assign_by_ref('generated_time', TTDate::getTime() );
			$smarty->assign_by_ref('pay_period_options', $pay_period_options );
			$smarty->assign_by_ref('filter_data', $filter_data );
			$smarty->assign_by_ref('columns', $filter_columns );
			$smarty->assign_by_ref('rows', $rows);

			$smarty->display('report/TimesheetSummaryReport.tpl');
		}

		break;
	case 'delete':
	case 'save':
		Debug::Text('Action: '. $action, __FILE__, __LINE__, __METHOD__,10);

		$generic_data['id'] = UserGenericDataFactory::reportFormDataHandler( $action, $filter_data, $generic_data, URLBuilder::getURL(NULL, $_SERVER['SCRIPT_NAME']) );
		unset($generic_data['name']);
	default:
		BreadCrumb::setCrumb($title);
		if ( $action == 'load' ) {
			Debug::Text('Loading Report!', __FILE__, __LINE__, __METHOD__,10);
			extract( UserGenericDataFactory::getReportFormData( $generic_data['id'] ) );
		} elseif ( $action == '' ) {
			//Check for default saved report first.
			$ugdlf->getByUserIdAndScriptAndDefault( $current_user->getId(), $_SERVER['SCRIPT_NAME'] );
			if ( $ugdlf->getRecordCount() > 0 ) {
				Debug::Text('Found Default Report!', __FILE__, __LINE__, __METHOD__,10);

				$ugd_obj = $ugdlf->getCurrent();
				$filter_data = $ugd_obj->getData();
				$generic_data['id'] = $ugd_obj->getId();
			} else {
				Debug::Text('Default Settings!', __FILE__, __LINE__, __METHOD__,10);
				//Default selections
				$filter_data['user_status_ids'] = array( -1 );
				$filter_data['branch_ids'] = array( -1 );
				$filter_data['department_ids'] = array( -1 );
				$filter_data['user_title_ids'] = array( -1 );
				$filter_data['pay_period_ids'] = array( '-0000-'.array_shift(array_keys($pay_period_options)) );
				$filter_data['start_date'] = $default_start_date;
				$filter_data['end_date'] = $default_end_date;
				$filter_data['group_ids'] = array( -1 );

				//$filter_data['user_ids'] = array_keys( UserListFactory::getByCompanyIdArray( $current_company->getId(), FALSE, FALSE ) );
				if ( !isset($filter_data['column_ids']) ) {
					$filter_data['column_ids']	= array();
				}

				$filter_data['column_ids'] = array_merge( $filter_data['column_ids'],
										array(
											'-1000-full_name',
											'-1065-pay_period',
											'-1090-worked_time',
											'-1130-paid_time',
											'-1140-regular_time',
												) );

				$filter_data['primary_sort'] = '-1000-full_name';
				$filter_data['secondary_sort'] = '-1065-pay_period';
				$filter_data['secondary_sort_dir'] = '-1';
			}
		}
		$filter_data = Misc::preSetArrayValues( $filter_data, array('include_user_ids', 'exclude_user_ids', 'user_status_ids', 'group_ids', 'branch_ids', 'department_ids', 'punch_branch_ids', 'punch_department_ids', 'user_title_ids', 'pay_period_ids', 'column_ids' ), NULL);

		$ulf = new UserListFactory();
		$all_array_option = array('-1' => _('-- All --'));

		//Get include employee list.
		$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), array('permission_children_ids' => $permission_children_ids ) );
		$user_options = $ulf->getArrayByListFactory( $ulf, FALSE, TRUE );
		$filter_data['src_include_user_options'] = Misc::arrayDiffByKey( (array)$filter_data['include_user_ids'], $user_options );
		$filter_data['selected_include_user_options'] = Misc::arrayIntersectByKey( (array)$filter_data['include_user_ids'], $user_options );

		//Get exclude employee list
		$exclude_user_options = Misc::prependArray( $all_array_option, $ulf->getArrayByListFactory( $ulf, FALSE, TRUE ) );
		$filter_data['src_exclude_user_options'] = Misc::arrayDiffByKey( (array)$filter_data['exclude_user_ids'], $user_options );
		$filter_data['selected_exclude_user_options'] = Misc::arrayIntersectByKey( (array)$filter_data['exclude_user_ids'], $user_options );

		//Get employee status list.
		$user_status_options = Misc::prependArray( $all_array_option, $ulf->getOptions('status') );
		$filter_data['src_user_status_options'] = Misc::arrayDiffByKey( (array)$filter_data['user_status_ids'], $user_status_options );
		$filter_data['selected_user_status_options'] = Misc::arrayIntersectByKey( (array)$filter_data['user_status_ids'], $user_status_options );

		//Get Employee Groups
		$uglf = new UserGroupListFactory();
		$group_options = Misc::prependArray( $all_array_option, $uglf->getArrayByNodes( FastTree::FormatArray( $uglf->getByCompanyIdArray( $current_company->getId() ), 'TEXT', TRUE) ) );
		$filter_data['src_group_options'] = Misc::arrayDiffByKey( (array)$filter_data['group_ids'], $group_options );
		$filter_data['selected_group_options'] = Misc::arrayIntersectByKey( (array)$filter_data['group_ids'], $group_options );

		//Get branches
		$blf = new BranchListFactory();
		$blf->getByCompanyId( $current_company->getId() );
		$branch_options = Misc::prependArray( $all_array_option, $blf->getArrayByListFactory( $blf, FALSE, TRUE ) );
		$filter_data['src_branch_options'] = Misc::arrayDiffByKey( (array)$filter_data['branch_ids'], $branch_options );
		$filter_data['selected_branch_options'] = Misc::arrayIntersectByKey( (array)$filter_data['branch_ids'], $branch_options );

		$filter_data['src_punch_branch_options'] = Misc::arrayDiffByKey( (array)$filter_data['punch_branch_ids'], $branch_options );
		$filter_data['selected_punch_branch_options'] = Misc::arrayIntersectByKey( (array)$filter_data['punch_branch_ids'], $branch_options );

		//Get departments
		$dlf = new DepartmentListFactory();
		$dlf->getByCompanyId( $current_company->getId() );
		$department_options = Misc::prependArray( $all_array_option, $dlf->getArrayByListFactory( $dlf, FALSE, TRUE ) );
		$filter_data['src_department_options'] = Misc::arrayDiffByKey( (array)$filter_data['department_ids'], $department_options );
		$filter_data['selected_department_options'] = Misc::arrayIntersectByKey( (array)$filter_data['department_ids'], $department_options );

		$filter_data['src_punch_department_options'] = Misc::arrayDiffByKey( (array)$filter_data['punch_department_ids'], $department_options );
		$filter_data['selected_punch_department_options'] = Misc::arrayIntersectByKey( (array)$filter_data['punch_department_ids'], $department_options );

		//Get employee titles
		$utlf = new UserTitleListFactory();
		$utlf->getByCompanyId( $current_company->getId() );
		$user_title_options = Misc::prependArray( $all_array_option, $utlf->getArrayByListFactory( $utlf, FALSE, TRUE ) );
		$filter_data['src_user_title_options'] = Misc::arrayDiffByKey( (array)$filter_data['user_title_ids'], $user_title_options );
		$filter_data['selected_user_title_options'] = Misc::arrayIntersectByKey( (array)$filter_data['user_title_ids'], $user_title_options );

		//Get pay periods
		$pplf = new PayPeriodListFactory();
		$pplf->getByCompanyId( $current_company->getId() );
		$pay_period_options = Misc::prependArray( $all_array_option, $pplf->getArrayByListFactory( $pplf, FALSE, TRUE ) );
		$filter_data['src_pay_period_options'] = Misc::arrayDiffByKey( (array)$filter_data['pay_period_ids'], $pay_period_options );
		$filter_data['selected_pay_period_options'] = Misc::arrayIntersectByKey( (array)$filter_data['pay_period_ids'], $pay_period_options );

		//Get column list
		$filter_data['src_column_options'] = Misc::arrayDiffByKey( (array)$filter_data['column_ids'], $columns );
		$filter_data['selected_column_options'] = Misc::arrayIntersectByKey( (array)$filter_data['column_ids'], $columns );


		//Get primary/secondary order list
		$filter_data['sort_options'] = $columns;
		$filter_data['sort_options']['effective_date_order'] = 'Wage Effective Date';
		unset($filter_data['sort_options']['effective_date']);
		$filter_data['sort_direction_options'] = Misc::getSortDirectionArray();

		/***/
		$filter_data['group_by_options'] = Misc::prependArray( array('0' => _('No Grouping')), $static_columns );
		/***/
                $filter_data['export_type_options'] = Misc::prependArray( array( 'csv' => _('CSV (Excel)'), 'pdfp' => _('PDF (PORTRAIT)'), 'pdfOTreport' => _('Monthly OT Report')) );
		
		$saved_report_options = $ugdlf->getByUserIdAndScriptArray( $current_user->getId(), $_SERVER['SCRIPT_NAME']);
		$generic_data['saved_report_options'] = $saved_report_options;
		$smarty->assign_by_ref('generic_data', $generic_data);

		$smarty->assign_by_ref('filter_data', $filter_data);

		$smarty->assign_by_ref('ugdf', $ugdf);

		$smarty->display('report/TimesheetSummary.tpl');

		break;
}
?>
