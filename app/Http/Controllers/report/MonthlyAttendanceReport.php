<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: TimesheetDetail.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');
require_once(Environment::getBasePath() .'classes/misc/arr_multisort.class.php');
require_once(Environment::getBasePath() .'classes/php_excel/PHPExcel.php');

$smarty->assign('title', TTi18n::gettext($title = 'Monthly Attendance Per User')); // See index.php

//Debug::setVerbosity(11);
/*
 * Get FORM variables
 */

//echo '<pre>'; print_r($_REQUEST);
extract	(FormVariables::GetVariables(
    array	(
                    'action',
                    'generic_data',
                    'filter_data'
                    ) ) );

if ( isset($filter_data['print_timesheet']) AND $filter_data['print_timesheet'] >= 1 ) {
	if ( !$permission->Check('punch','enabled')
			OR !( $permission->Check('punch','view') OR $permission->Check('punch','view_own') OR $permission->Check('punch','view_child'))
			) {
		$permission->Redirect( FALSE ); //Redirect
	}
} else {
	if ( !$permission->Check('report','enabled')
			OR !$permission->Check('report','view_timesheet_summary') ) {
		$permission->Redirect( FALSE ); //Redirect
	}
}

URLBuilder::setURL($_SERVER['SCRIPT_NAME'],
                    array(
                            'filter_data' => $filter_data
							//'sort_column' => $sort_column,
							//'sort_order' => $sort_order,
                            ) 
              );

$static_columns = array(
/*
//Report shows full name by default.
    'full_name' => 'Full Name',
    'title' => 'Title',
    'province' => 'Province',
    'country' => 'Country',
    'default_branch' => 'Default Branch',
    'default_department' => 'Default Department',
*/
    '-1000-date_stamp' => TTi18n::gettext('Date'),
    '-1050-min_punch_time_stamp' => 'First In Punch',
    '-1060-max_punch_time_stamp' => 'Last Out Punch',
    );

$columns = array(

    '-1070-schedule_working' => TTi18n::gettext('Scheduled Time'),
    '-1080-schedule_absence' => TTi18n::gettext('Scheduled Absence'),
    '-1090-worked_time' => TTi18n::gettext('Worked Timezzzz'),
    '-1100-actual_time' => TTi18n::gettext('Actual Time'),
    '-1110-actual_time_diff' => TTi18n::gettext('Actual Time Difference'),
    '-1120-actual_time_diff_wage' => TTi18n::gettext('Actual Time Difference Wage'),
    '-1130-paid_time' => TTi18n::gettext('Paid Time'),
    '-1140-regular_time' => TTi18n::gettext('Regular Time'),
    '-1150-over_time' => TTi18n::gettext('Total Over Time'),
    '-1160-absence_time' => TTi18n::gettext('Total Absence Time'),
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
	$pay_period_options = $pplf->getByIdListArray($pay_period_ids, NULL, array('start_date' => 'desc'), FALSE );
}

if ( isset($filter_data['start_date']) ) {
	$filter_data['start_date'] = TTDate::parseDateTime($filter_data['start_date']);
}

if ( isset($filter_data['end_date']) ) {
	$filter_data['end_date'] = TTDate::parseDateTime($filter_data['end_date']);
}

$filter_data = Misc::preSetArrayValues( $filter_data, array('include_user_ids', 'exclude_user_ids', 'user_status_ids', 'group_ids', 'branch_ids', 'department_ids', 'user_title_ids', 'pay_period_ids', 'column_ids' ), array() );

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
	case 'display_timesheet':
	case 'display_detailed_timesheet':

		//echo '<pre>vgggggv'; print_r($filter_data);
		//Debug::setVerbosity(11);

		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($filter_data, 'Filter Data', __FILE__, __LINE__, __METHOD__,10);

		//Determine if this is a regular employee trying to print their own timesheet.
		//from the MyTimeSheet page.
       /*     
        if(date('Y-m',$filter_data['start_date']) != date('Y-m',$filter_data['end_date'])){
            echo 'Error! Start Date and End Date Should be in same Month.'; 
            exit();
        }
          */   
             
//                                echo '<pre>'; print_r(date('Y-m',$filter_data['end_date'])); die;

		if ( isset($filter_data['print_timesheet']) AND $filter_data['print_timesheet'] >= 1 ) { 
			//If they don't have permissions to see more then just their own punches, force
			//to currently logged in user.
			if ( !isset($filter_data['user_id']) OR !( $permission->Check('punch','view') OR $permission->Check('punch','view_child') ) ) {
				$filter_data['user_id'] = $current_user->getId();
			}

			//Force as many settings as possible so they can't manually override them.
			$action = 'display_timesheet';
			if ( $filter_data['print_timesheet'] == 2 ) {
				$action = 'display_detailed_timesheet';
			}
			$filter_data = array(
                            'permission_children_ids' => array( (int)$filter_data['user_id'] ),
                            'pay_period_ids' => array( (int)$filter_data['pay_period_ids'] ),
                            'date_type' => 'pay_period_ids',
                            'primary_sort' => '-1000-date_stamp',
                            'secondary_sort' => NULL,
                            'primary_sort_dir' => 1,
                            'secondary_sort_dir' => NULL,
                            'column_ids' => $static_columns
                    );
		}



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
                                    if ( $i == 0 AND isset($pay_period_end_dates[$tmp_pay_period_id]) ) {
                                            $end_date = $pay_period_end_dates[$tmp_pay_period_id];
                                    } elseif ( isset($pay_period_end_dates[$tmp_pay_period_id]) AND $pay_period_end_dates[$tmp_pay_period_id] > $end_date ) {
                                            $end_date = $pay_period_end_dates[$tmp_pay_period_id];
                                    } else {
                                            $end_date = time();
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

			$udtlf = new UserDateTotalListFactory();
			if ( isset($filter_data['user_id']) ) {
				$udtlf->getDayReportByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
			}

			$slf = new ScheduleListFactory();
			if ( isset($filter_data['user_id']) ) {
				$slf->getDayReportByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
			}
			if ( $slf->getRecordCount() > 0 ) {
				foreach($slf as $s_obj) {
					$user_id = $s_obj->getColumn('user_id');
					$status_id = $s_obj->getColumn('status_id');
					$status = strtolower( Option::getByKey($status_id, $s_obj->getOptions('status') ) );
					$pay_period_id = $s_obj->getColumn('pay_period_id');
					$date_stamp = TTDate::strtotime( $s_obj->getColumn('date_stamp') );

					$schedule_rows[$pay_period_id][$user_id][$date_stamp][$status] = $s_obj->getColumn('total_time');  
					$schedule_rows[$pay_period_id][$user_id][$date_stamp]['start_time'] = $s_obj->getColumn('start_time');  
					$schedule_rows[$pay_period_id][$user_id][$date_stamp]['end_time'] = $s_obj->getColumn('end_time');
                                  
                                        unset($user_id, $status_id, $status, $pay_period_id, $date_stamp);
				}
			}
			//echo '<pre>'; print_r($schedule_rows); echo'<pre>';
                       //exit();

                        $prev_records;
			foreach ($udtlf as $udt_obj ) {
				$user_id = $udt_obj->getColumn('id');
				 $pay_period_id = $udt_obj->getColumn('pay_period_id');
                                 
                                 if($pay_period_id ==0){
                                     continue;
                                 }
                                /*
                                if(isset($prev_records[$user_id])){
                                    $pay_period_id = $prev_records[$user_id];
                                }else{
                                    $prev_records[$user_id] = $pay_period_id;
                                }*/
                               
				$date_stamp = TTDate::strtotime( $udt_obj->getColumn('date_stamp') );
                                
				$status_id = $udt_obj->getColumn('status_id');
				$type_id = $udt_obj->getColumn('type_id');

				$category = 0;
				$policy_id = 0;

				if ( $status_id == 10 AND $type_id == 10 ) {
					$column = 'paid_time';
					$category = $column;
				} elseif ($status_id == 10 AND $type_id == 20) {
					$column = 'regular_time';
					$category = $column;
				} elseif ($status_id == 10 AND $type_id == 30) {
					$column = 'over_time_policy-'. $udt_obj->getColumn('over_time_policy_id');
					$category = 'over_time_policy';
					$policy_id = $udt_obj->getColumn('over_time_policy_id');
				} elseif ($status_id == 10 AND $type_id == 40) {
					$column = 'premium_policy-'. $udt_obj->getColumn('premium_policy_id');
					$category = 'premium_policy';
					$policy_id = $udt_obj->getColumn('premium_policy_id');
				} elseif ($status_id == 30 AND $type_id == 10) {
					$column = 'absence_policy-'. $udt_obj->getColumn('absence_policy_id');
					$category = 'absence_policy';
					$policy_id = $udt_obj->getColumn('absence_policy_id');
				} elseif ( ($status_id == 20 AND $type_id == 10 ) OR ($status_id == 10 AND $type_id == 100 ) ) {
					$column = 'worked_time';
					$category = $column;
				} else {
					$column = NULL;
				}

				//Debug::Text('Column: '. $column .' Status ID: '. $status_id .' Type ID: '. $type_id .' Total Time: '. $udt_obj->getColumn('total_time'), __FILE__, __LINE__, __METHOD__,10);
				if ( $column == 'worked_time' ) {
					//Handle actual time diff/wage here.
					if ( isset($tmp_rows[$pay_period_id][$user_id][$date_stamp][$column]) ) {
						$tmp_rows[$pay_period_id][$user_id][$date_stamp][$column] += (int)$udt_obj->getColumn('total_time');
					} else {
						$tmp_rows[$pay_period_id][$user_id][$date_stamp][$column] = (int)$udt_obj->getColumn('total_time');
					}
					if ( isset($tmp_rows[$pay_period_id][$user_id][$date_stamp]['actual_time']) ) {
						$tmp_rows[$pay_period_id][$user_id][$date_stamp]['actual_time'] += $udt_obj->getColumn('actual_total_time');
					} else {
						$tmp_rows[$pay_period_id][$user_id][$date_stamp]['actual_time'] = $udt_obj->getColumn('actual_total_time');
					}

					$actual_time_diff = bcsub($udt_obj->getColumn('actual_total_time'), $udt_obj->getColumn('total_time') );
					if ( isset($tmp_rows[$pay_period_id][$user_id][$date_stamp]['actual_time_diff']) ) {
						$tmp_rows[$pay_period_id][$user_id][$date_stamp]['actual_time_diff'] += $actual_time_diff;
					} else {
						$tmp_rows[$pay_period_id][$user_id][$date_stamp]['actual_time_diff'] = $actual_time_diff;
					}

					if ( isset($user_wage[$user_id]) ) {
						$tmp_rows[$pay_period_id][$user_id][$date_stamp]['actual_time_diff_wage'] = Misc::MoneyFormat( bcmul( TTDate::getHours($actual_time_diff), $user_wage[$user_id]), FALSE );
					} else {
						$tmp_rows[$pay_period_id][$user_id][$date_stamp]['actual_time_diff_wage'] = Misc::MoneyFormat( 0, FALSE );
					}
					unset($actual_time_diff);
				} elseif ( $column != NULL ) {
					if ( $udt_obj->getColumn('total_time') > 0 ) {

						//Total up all absence time.
						if ($status_id == 30 AND $type_id == 10) {
							if ( isset($tmp_rows[$pay_period_id][$user_id][$date_stamp]['absence_time']) ) {
								$tmp_rows[$pay_period_id][$user_id][$date_stamp]['absence_time'] += $udt_obj->getColumn('total_time');
							} else {
								$tmp_rows[$pay_period_id][$user_id][$date_stamp]['absence_time'] = $udt_obj->getColumn('total_time');
							}
						}

						if ($status_id == 10 AND $type_id == 30) {
							if ( isset($tmp_rows[$pay_period_id][$user_id][$date_stamp]['over_time']) ) {
								$tmp_rows[$pay_period_id][$user_id][$date_stamp]['over_time'] += $udt_obj->getColumn('total_time');
                                                                
							} else {
								$tmp_rows[$pay_period_id][$user_id][$date_stamp]['over_time'] = $udt_obj->getColumn('total_time');
							}
						}

						if ( isset($tmp_rows[$pay_period_id][$user_id][$date_stamp][$column]) ) {
							$tmp_rows[$pay_period_id][$user_id][$date_stamp][$column] += $udt_obj->getColumn('total_time');
						} else {
							$tmp_rows[$pay_period_id][$user_id][$date_stamp][$column] = $udt_obj->getColumn('total_time');
						}

						//This messes with the ArraySum'ing, so only include it when we're generating a PDF timesheet.
						if ( $action == 'display_timesheet' OR $action == 'display_detailed_timesheet' ) {
							if ( isset($tmp_rows[$pay_period_id][$user_id][$date_stamp]['categorized_time'][$category][$policy_id]) ) {
								$tmp_rows[$pay_period_id][$user_id][$date_stamp]['categorized_time'][$category][$policy_id] += $udt_obj->getColumn('total_time');
							} else {
								$tmp_rows[$pay_period_id][$user_id][$date_stamp]['categorized_time'][$category][$policy_id] = $udt_obj->getColumn('total_time');
							}
						}
					}
				}
                                
//                                echo '<pre>'; print_r($schedule_rows[$pay_period_id][$user_id][$date_stamp]['start_time']); echo '<pre>'; die;
        
				if ( isset($schedule_rows[$pay_period_id][$user_id][$date_stamp]['working']) ) {
					$tmp_rows[$pay_period_id][$user_id][$date_stamp]['schedule_working'] = $schedule_rows[$pay_period_id][$user_id][$date_stamp]['working'];
				} else {
					$tmp_rows[$pay_period_id][$user_id][$date_stamp]['schedule_working'] = NULL;
				}

				if ( isset($schedule_rows[$pay_period_id][$user_id][$date_stamp]['absence']) ) {
					$tmp_rows[$pay_period_id][$user_id][$date_stamp]['schedule_absence'] = $schedule_rows[$pay_period_id][$user_id][$date_stamp]['absence'];
				} else {
					$tmp_rows[$pay_period_id][$user_id][$date_stamp]['schedule_absence'] = NULL;
				}

				if ( isset($schedule_rows[$pay_period_id][$user_id][$date_stamp]['start_time']) ) {
                                    
					$tmp_rows[$pay_period_id][$user_id][$date_stamp]['shedule_start_time'] = $schedule_rows[$pay_period_id][$user_id][$date_stamp]['start_time'];
				} else {
					$tmp_rows[$pay_period_id][$user_id][$date_stamp]['shedule_start_time'] = NULL;
				}

				if ( isset($schedule_rows[$pay_period_id][$user_id][$date_stamp]['end_time']) ) {
					$tmp_rows[$pay_period_id][$user_id][$date_stamp]['shedule_end_time'] = $schedule_rows[$pay_period_id][$user_id][$date_stamp]['end_time'];
				} else {
					$tmp_rows[$pay_period_id][$user_id][$date_stamp]['shedule_end_time'] = NULL;
				}

				$tmp_rows[$pay_period_id][$user_id][$date_stamp]['min_punch_time_stamp'] = TTDate::strtotime( $udt_obj->getColumn('min_punch_time_stamp') );
				$tmp_rows[$pay_period_id][$user_id][$date_stamp]['max_punch_time_stamp'] = TTDate::strtotime( $udt_obj->getColumn('max_punch_time_stamp') );

                    	}
;
			//Get all punches
			if ( $action == 'display_detailed_timesheet'  ) {
				$plf = new PunchListFactory();
				$plf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data);
				if ( $plf->getRecordCount() > 0 ) {
					foreach( $plf as $p_obj ) {
						$punch_rows[$p_obj->getColumn('pay_period_id')][$p_obj->getColumn('user_id')][TTDate::strtotime( $p_obj->getColumn('date_stamp') )][$p_obj->getPunchControlID()][$p_obj->getStatus()] = array( 'status_id' => $p_obj->getStatus(), 'type_id' => $p_obj->getType(), 'type_code' => $p_obj->getTypeCode(), 'time_stamp' => $p_obj->getTimeStamp() );
					}
				}
				unset($plf,$p_obj);
			}

			$ulf = new UserListFactory();

			$utlf = new UserTitleListFactory();
			$title_options = $utlf->getByCompanyIdArray( $current_company->getId() );

			$blf = new BranchListFactory();
			$branch_options = $blf->getByCompanyIdArray( $current_company->getId() );

			$dlf = new DepartmentListFactory();
			$department_options = $dlf->getByCompanyIdArray( $current_company->getId() );

			$uglf = new UserGroupListFactory();
			$group_options = $uglf->getArrayByNodes( FastTree::FormatArray( $uglf->getByCompanyIdArray( $current_company->getId() ), 'no_tree_text', TRUE) );

			//Get verified timesheets
			//Ignore if more then one pay period is selected
			$verified_time_sheets = NULL;
			if ( isset($filter_data['pay_period_ids']) AND count($filter_data['pay_period_ids']) > 0 ) {
				$pptsvlf = new PayPeriodTimeSheetVerifyListFactory();
				$pptsvlf->getByPayPeriodIdAndCompanyId( $filter_data['pay_period_ids'][0], $current_company->getId() );
				if ( $pptsvlf->getRecordCount() > 0 ) {
					foreach( $pptsvlf as $pptsv_obj ) {
						$verified_time_sheets[$pptsv_obj->getUser()][$pptsv_obj->getPayPeriod()] = array(
								 'status_id' => $pptsv_obj->getStatus(),
								 'created_date' => $pptsv_obj->getCreatedDate(),
							);
					}
				}
			}

                       // echo '<pre>'.print_r($tmp_rows);die;
                        
			if ( isset($tmp_rows) ) {
				$i=0;
				foreach($tmp_rows as $pay_period_id => $data_a ) {
					foreach($data_a as $user_id => $data_b ) {
						$user_obj = $ulf->getById( $user_id )->getCurrent();

						if ( isset($pay_period_options[$pay_period_id]) ) {
							$rows[$i]['pay_period'] = $pay_period_options[$pay_period_id];
						} else {
							$rows[$i]['pay_period'] = 'N/A';
						}
						$rows[$i]['pay_period_id'] = $pay_period_id;
						$rows[$i]['user_id'] = $user_id;
						$rows[$i]['first_name'] = $user_obj->getFirstName();
						$rows[$i]['last_name'] = $user_obj->getLastName();
						$rows[$i]['full_name'] = $user_obj->getFullName(TRUE);
						$rows[$i]['employee_number'] = $user_obj->getEmployeeNumber();
						$rows[$i]['province'] = $user_obj->getProvince();
						$rows[$i]['country'] = $user_obj->getCountry();

						$rows[$i]['group'] = Option::getByKey($user_obj->getGroup(), $group_options, NULL );
						$rows[$i]['title'] = Option::getByKey($user_obj->getTitle(), $title_options, NULL );
						$rows[$i]['default_branch'] =  Option::getByKey($user_obj->getDefaultBranch(), $branch_options, NULL );
						$rows[$i]['default_department'] = Option::getByKey($user_obj->getDefaultDepartment(), $department_options, NULL );

						$rows[$i]['verified_time_sheet_date'] = FALSE;
						if ( $verified_time_sheets !== NULL AND isset($verified_time_sheets[$user_id][$pay_period_id]) ) {
							if ( $verified_time_sheets[$user_id][$pay_period_id]['status_id'] == 50 ) {
								$rows[$i]['verified_time_sheet'] = TTi18n::gettext('Yes');
								$rows[$i]['verified_time_sheet_date'] = $verified_time_sheets[$user_id][$pay_period_id]['created_date'];
							} elseif ( $verified_time_sheets[$user_id][$pay_period_id]['status_id'] == 30 OR $verified_time_sheets[$user_id][$pay_period_id]['status_id'] == 45 ) {
								$rows[$i]['verified_time_sheet'] = TTi18n::gettext('Pending');
							} else {
								$rows[$i]['verified_time_sheet'] = TTi18n::gettext('Declined');
							}
						} else {
							$rows[$i]['verified_time_sheet'] = TTi18n::gettext('No');
						}

						$x=0;
						foreach($data_b as $date_stamp => $data_c ) {
							$sub_rows[$x]['date_stamp'] = $date_stamp;

							foreach($data_c as $column => $total_time) {
								$sub_rows[$x][$column] = $total_time;
							}
							$x++;
						}

						if ( isset($sub_rows) ) {
							foreach($sub_rows as $sub_row) {
								$tmp_sub_rows[] = $sub_row;
                                                                
							}

							$sub_rows = Sort::Multisort($tmp_sub_rows, Misc::trimSortPrefix($filter_data['primary_sort']), Misc::trimSortPrefix($filter_data['secondary_sort']), $filter_data['primary_sort_dir'], $filter_data['secondary_sort_dir']);
                                                      //  print_r($sub_rows);
                                                      //  exit();
							if ( $action != 'display_timesheet' AND $action != 'display_detailed_timesheet') {
								$total_sub_row = Misc::ArrayAssocSum($sub_rows, NULL, 2);

								$last_sub_row = count($sub_rows);
								$sub_rows[$last_sub_row] = $total_sub_row;
								//$static_columns['epoch'] = 'epoch';
								foreach ($static_columns as $static_column_key => $static_column_val) {
									$sub_rows[$last_sub_row][Misc::trimSortPrefix($static_column_key)] = NULL;
								}
								unset($static_column_key, $static_column_val);
							}

							//Convert units
							$tmp_sub_rows = $sub_rows;
							unset($sub_rows);


							$trimmed_static_columns = array_keys( Misc::trimSortPrefix($static_columns) );
							foreach($tmp_sub_rows as $sub_row ) {
                                                          
                                                            
								foreach($sub_row as $column => $column_data) {
                                                                    
                                                                    
									if ( $action != 'display_timesheet' AND $action != 'display_detailed_timesheet') {
										if ( $column == 'shedule_start_time' ||  $column == 'shedule_end_time') {// FL ADDED FOR SHEDULE START AND END TIME FOR DAY
                                                                                   
											//$column_data = substr($column_data,11,5 ); 
                                                                                        
										}elseif ( $column == 'date_stamp' ) {
											$column_data = TTDate::getDate('DATE', $column_data);
										} elseif ( $column == 'min_punch_time_stamp' OR $column == 'max_punch_time_stamp' ) {
											$column_data = TTDate::getDate('epoch', $column_data);
										} elseif ( !strstr($column, 'wage') AND !in_array( $column, $trimmed_static_columns ) ) {
											$column_data = TTDate::getTimeUnit( $column_data );
										}
									}
									$sub_row_columns[$column] = $column_data;
									unset($column, $column_data);
								} 

								$sub_rows[] = $sub_row_columns;
								unset($sub_row_columns);

								//$prev_row = $sub_row;
							}

							//var_dump($rows);
							foreach( $filter_data['column_ids'] as $column_key ) {
								if ( isset($columns[$column_key]) ) {
									$filter_columns[Misc::trimSortPrefix($column_key)] = $columns[$column_key];
								}
							}
						}
                                               
                                               // echo '<pre>'.print_r($sub_rows);
                                              //  exit();
						$rows[$i]['data'] = $sub_rows;
						unset($sub_rows, $tmp_sub_rows);
						$i++;
					}
				}
			}
			unset($tmp_rows);
		}
		if ( $action == 'display_timesheet' ) {
			if ( isset($rows) ) {
				$pdf_created_date = time();

				//Page width: 205mm
				$pdf = new TTPDF('P','mm','Letter');
				$pdf->setMargins(10,5);
				$pdf->SetAutoPageBreak(FALSE);
				$pdf->SetFont('freeserif','',10);

				$border = 0;

				//Create PDF TimeSheet for each employee.
				foreach( $rows as $user_data ) {
					$pdf->AddPage();

					$adjust_x = 10;
					$adjust_y = 10;

					//$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(0, $adjust_y) );

					$pdf->SetFont('','B',32);
					$pdf->Cell(200,15, TTi18n::gettext('Employee TimeSheet') , $border, 0, 'C');
					$pdf->Ln();
					$pdf->SetFont('','B',12);
					$pdf->Cell(200,5, $current_company->getName() , $border, 0, 'C');
					$pdf->Ln(10);

					$pdf->Rect( $pdf->getX(), $pdf->getY()-2, 200, 19 );

					$pdf->SetFont('','',12);
					$pdf->Cell(30,5, TTi18n::gettext('Employee:') , $border, 0, 'R');
					$pdf->SetFont('','B',12);
					$pdf->Cell(70,5, $user_data['first_name'] .' '. $user_data['last_name'] .' (#'. $user_data['employee_number'] .')', $border, 0, 'L');

					$pdf->SetFont('','',12);
					$pdf->Cell(40,5, TTi18n::gettext('Pay Period:') , $border, 0, 'R');
					$pdf->SetFont('','B',12);
					$pdf->Cell(60,5, $user_data['pay_period'], $border, 0, 'L');
					$pdf->Ln();

					$pdf->SetFont('','',12);
					$pdf->Cell(30,5, TTi18n::gettext('Title:') , $border, 0, 'R');
					$pdf->Cell(70,5, $user_data['title'], $border, 0, 'L');
					$pdf->Cell(40,5, TTi18n::gettext('Branch:') , $border, 0, 'R');
					$pdf->Cell(60,5, $user_data['default_branch'], $border, 0, 'L');
					$pdf->Ln();

					$pdf->Cell(30,5, TTi18n::gettext('Group:') , $border, 0, 'R');
					$pdf->Cell(70,5, $user_data['group'], $border, 0, 'L');
					$pdf->Cell(40,5, TTi18n::gettext('Department:') , $border, 0, 'R');
					$pdf->Cell(60,5, $user_data['default_department'], $border, 0, 'L');
					$pdf->Ln(5);

					$pdf->SetFont('','',10);
					//Start displaying dates/times here. Start with header.
					$column_widths = array(
										'line' => 5,
										'date_stamp' => 20,
										'dow' => 10,
										'min_punch_time_stamp' => 25,
										'max_punch_time_stamp' => 25,
										'worked_time' => 25,
										'regular_time' => 25,
										'over_time' => 20,
										'paid_time' => 20,
										'absence_time' => 25,
										);


					if ( isset($user_data['data']) AND is_array($user_data['data']) ) {
						if ( isset($filter_data['date_type']) AND $filter_data['date_type'] == 'pay_period_ids' )  {
							//Fill in any missing days, only if they select by pay period.
							$pplf = new PayPeriodListFactory();
							$pplf->getById( $user_data['pay_period_id'] );
							if ( $pplf->getRecordCount() == 1 ) {
								$pp_obj = $pplf->getCurrent();

								for( $d=TTDate::getBeginDayEpoch($pp_obj->getStartDate()); $d <= $pp_obj->getEndDate(); $d+=86400) {
									if ( Misc::inArrayByKeyAndValue($user_data['data'], 'date_stamp', TTDate::getBeginDayEpoch($d) ) == FALSE ) {
										$user_data['data'][] = array(
																'date_stamp' => TTDate::getBeginDayEpoch($d),
																'min_punch_time' => NULL,
																'max_punch_time' => NULL,
																'worked_time' => NULL,
																'regular_time' => NULL,
																'over_time' => NULL,
																'paid_time' => NULL,
																'absence_time' => NULL
															);

									}
								}
							}
						}
						$user_data['data'] = Sort::Multisort( $user_data['data'], 'date_stamp', NULL, 'ASC' );

						$week_totals = Misc::preSetArrayValues( NULL, array( 'worked_time', 'paid_time', 'absence_time', 'regular_time', 'over_time' ), 0 );
						$totals = array();
						$totals = Misc::preSetArrayValues( $totals, array( 'worked_time', 'paid_time', 'absence_time', 'regular_time', 'over_time' ), 0 );

						$i=1;
						$x=1;
						$y=1;
						$max_i = count($user_data['data']);
						foreach( $user_data['data'] as $data) {
							//Show Header
							if ( $i == 1 OR $x == 1 ) {
								if ( $x == 1 ) {
									$pdf->Ln();
								}

								$line_h = 6;
								$cell_h_min = $cell_h_max = $line_h * 2;

								$pdf->SetFont('','B',10);
								$pdf->setFillColor(220,220,220);
								$pdf->MultiCell( $column_widths['line'], $line_h, '#' , 1, 'C', 1, 0);
								$pdf->MultiCell( $column_widths['date_stamp'], $line_h, TTi18n::gettext('Date') , 1, 'C', 1, 0);
								$pdf->MultiCell( $column_widths['dow'], $line_h, TTi18n::gettext('DoW') , 1, 'C', 1, 0);
								$pdf->MultiCell( $column_widths['min_punch_time_stamp'], $line_h, TTi18n::gettext('First In') , 1, 'C', 1, 0);
								$pdf->MultiCell( $column_widths['max_punch_time_stamp'], $line_h, TTi18n::gettext('Last Out') , 1, 'C', 1, 0);
								$pdf->MultiCell( $column_widths['worked_time'], $line_h, TTi18n::gettext('Worked Time') , 1, 'C', 1, 0);
								$pdf->MultiCell( $column_widths['regular_time'], $line_h, TTi18n::gettext('Regular Time') , 1, 'C', 1, 0);
								$pdf->MultiCell( $column_widths['over_time'], $line_h, TTi18n::gettext('Over Time') , 1, 'C', 1, 0);
								$pdf->MultiCell( $column_widths['paid_time'], $line_h, TTi18n::gettext('Paid Time') , 1, 'C', 1, 0);
								$pdf->MultiCell( $column_widths['absence_time'], $line_h, TTi18n::gettext('Absence Time') , 1, 'C', 1, 0);
								$pdf->Ln();
							}

							$data = Misc::preSetArrayValues( $data, array('date_stamp', 'min_punch_time_stamp', 'max_punch_time_stamp', 'worked_time', 'paid_time', 'absence_time', 'regular_time', 'over_time' ), '--' );

							if ( $x % 2 == 0 ) {
								$pdf->setFillColor(220,220,220);
							} else {
								$pdf->setFillColor(255,255,255);
							}

							if ( $data['date_stamp'] !== '' ) {
								$pdf->SetFont('','',10);
								$pdf->Cell( $column_widths['line'], 6, $x , 1, 0, 'C', 1);
								$pdf->Cell( $column_widths['date_stamp'], 6, TTDate::getDate('DATE', $data['date_stamp'] ), 1, 0, 'C', 1);
								$pdf->Cell( $column_widths['dow'], 6, date('D', $data['date_stamp']) , 1, 0, 'C', 1);
								$pdf->Cell( $column_widths['min_punch_time_stamp'], 6, TTDate::getDate('TIME', $data['min_punch_time_stamp'] ), 1, 0, 'C', 1);
								$pdf->Cell( $column_widths['max_punch_time_stamp'], 6, TTDate::getDate('TIME', $data['max_punch_time_stamp'] ), 1, 0, 'C', 1);
								$pdf->Cell( $column_widths['worked_time'], 6, TTDate::getTimeUnit( $data['worked_time'] ) , 1, 0, 'C', 1);
								$pdf->Cell( $column_widths['regular_time'], 6, TTDate::getTimeUnit( $data['regular_time'] ), 1, 0, 'C', 1);
								$pdf->Cell( $column_widths['over_time'], 6, TTDate::getTimeUnit( $data['over_time'] ), 1, 0, 'C', 1);
								$pdf->Cell( $column_widths['paid_time'], 6,  TTDate::getTimeUnit( $data['paid_time'] ), 1, 0, 'C', 1);
								$pdf->Cell( $column_widths['absence_time'], 6, TTDate::getTimeUnit( $data['absence_time'] ), 1, 0, 'C', 1);
								$pdf->Ln();
							}

							$totals['worked_time'] += $data['worked_time'];
							$totals['paid_time'] += $data['paid_time'];
							$totals['absence_time'] += $data['absence_time'];
							$totals['regular_time'] += $data['regular_time'];
							$totals['over_time'] += $data['over_time'];

							$week_totals['worked_time'] += $data['worked_time'];
							$week_totals['paid_time'] += $data['paid_time'];
							$week_totals['absence_time'] += $data['absence_time'];
							$week_totals['regular_time'] += $data['regular_time'];
							$week_totals['over_time'] += $data['over_time'];

							if ( $x % 7 == 0 OR $i == $max_i ) {
								//Show Week Total.
								$total_cell_width = $column_widths['line']+$column_widths['date_stamp']+$column_widths['dow']+$column_widths['min_punch_time_stamp']+$column_widths['max_punch_time_stamp'];
								$pdf->SetFont('','B',10);
								$pdf->Cell( $total_cell_width, 6, TTi18n::gettext('Week Total:').' ', 0, 0, 'R', 0);
								$pdf->Cell( $column_widths['worked_time'], 6, TTDate::getTimeUnit( $week_totals['worked_time'] ) , 0, 0, 'C', 0);
								$pdf->Cell( $column_widths['regular_time'], 6, TTDate::getTimeUnit( $week_totals['regular_time'] ), 0, 0, 'C', 0);
								$pdf->Cell( $column_widths['over_time'], 6, TTDate::getTimeUnit( $week_totals['over_time'] ), 0, 0, 'C', 0);
								$pdf->Cell( $column_widths['paid_time'], 6,  TTDate::getTimeUnit( $week_totals['paid_time'] ), 0, 0, 'C', 0);
								$pdf->Cell( $column_widths['absence_time'], 6, TTDate::getTimeUnit( $week_totals['absence_time'] ), 0, 0, 'C', 0);
								$pdf->Ln(2);

								unset($week_totals);
								$week_totals = Misc::preSetArrayValues( NULL, array( 'worked_time', 'paid_time', 'absence_time', 'regular_time', 'over_time' ), 0 );

								$x=0;
								$y++;

								//Force page break every 3 weeks.
								if ( $y == 4 AND $i !== $max_i ) {
									$pdf->AddPage();
								}
							}


							$i++;
							$x++;
						}
						unset($data);
					}

					if ( isset($totals) AND is_array($totals) ) {
						//Display overall totals.
						$pdf->Ln(3);
						$total_cell_width = $column_widths['line']+$column_widths['date_stamp']+$column_widths['dow']+$column_widths['min_punch_time_stamp'];
						$pdf->SetFont('','B',10);
						$pdf->Cell( $total_cell_width, 6, '' , 0, 0, 'R', 0);
						$pdf->Cell( $column_widths['max_punch_time_stamp'], 6, TTi18n::gettext('Overall Total:').' ', 'T', 0, 'R', 0);
						$pdf->Cell( $column_widths['worked_time'], 6, TTDate::getTimeUnit( $totals['worked_time'] ) , 'T', 0, 'C', 0);
						$pdf->Cell( $column_widths['regular_time'], 6, TTDate::getTimeUnit( $totals['regular_time'] ), 'T', 0, 'C', 0);
						$pdf->Cell( $column_widths['over_time'], 6, TTDate::getTimeUnit( $totals['over_time'] ), 'T', 0, 'C', 0);
						$pdf->Cell( $column_widths['paid_time'], 6,  TTDate::getTimeUnit( $totals['paid_time'] ), 'T', 0, 'C', 0);
						$pdf->Cell( $column_widths['absence_time'], 6, TTDate::getTimeUnit( $totals['absence_time'] ), 'T', 0, 'C', 0);
						$pdf->Ln();
						unset($totals);
					}

					$pdf->SetFont('','',10);
					$pdf->setFillColor(255,255,255);
					$pdf->Ln();

					//Signature lines
					$pdf->MultiCell(200,5, TTi18n::gettext('By signing this timesheet I hereby certify that the above time accurately and fully reflects the time that').' '. $user_data['first_name'] .' '. $user_data['last_name'] .' '.TTi18n::gettext('worked during the designated period.'), $border, 'L');
					$pdf->Ln(5);

					$border = 0;
					$pdf->Cell(40,5, TTi18n::gettext('Employee Signature:'), $border, 0, 'L');
					$pdf->Cell(60,5, '_____________________________' , $border, 0, 'C');
					$pdf->Cell(40,5, TTi18n::gettext('Supervisor Signature:'), $border, 0, 'R');
					$pdf->Cell(60,5, '_____________________________' , $border, 0, 'C');

					$pdf->Ln();
					$pdf->Cell(40,5, '', $border, 0, 'R');
					$pdf->Cell(60,5, $user_data['first_name'] .' '. $user_data['last_name'] , $border, 0, 'C');

					$pdf->Ln();
					$pdf->Cell(140,5, '', $border, 0, 'R');
					$pdf->Cell(60,5, '_____________________________' , $border, 0, 'C');

					$pdf->Ln();
					$pdf->Cell(140,5, '', $border, 0, 'R');
					$pdf->Cell(60,5, TTi18n::gettext('(print name)'), $border, 0, 'C');

					if ( $user_data['verified_time_sheet_date'] != FALSE ) {
						$pdf->Ln();
						$pdf->SetFont('','B',10);
						$pdf->Cell(200,5, TTi18n::gettext('TimeSheet electronically signed by').' '. $user_data['first_name'] .' '. $user_data['last_name'] .' '. TTi18n::gettext('on') .' '. TTDate::getDate('DATE+TIME', $user_data['verified_time_sheet_date'] ), $border, 0, 'C');
						$pdf->SetFont('','',10);
					}


					//Add generated date/time at the bottom.
					$pdf->SetFont('','I',8);
					$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(245, $adjust_y) );
					$pdf->Cell(200,5, TTi18n::gettext('Generated:') .' '. TTDate::getDate('DATE+TIME', $pdf_created_date ), $border, 0, 'C');
				}

				$output = $pdf->Output('','S');
			}

			if ( isset($output) AND $output !== FALSE AND Debug::getVerbosity() < 11 ) {
				Misc::FileDownloadHeader('timesheet.pdf', 'application/pdf', strlen($output));
				echo $output;
				exit;
			} else {
				//Debug::Display();
				echo TTi18n::gettext('ERROR: Employee TimeSheet(s) not available!') . "<br>\n";
				exit;
			}

		} elseif ( $action == 'display_detailed_timesheet' ) {
                    
			if ( isset($rows) ) {
				$pdf_created_date = time();

				//Page width: 205mm
				$pdf = new TTPDF('P','mm','Letter');
				$pdf->setMargins(10,5);
				$pdf->SetAutoPageBreak(TRUE, 10);
				$pdf->SetFont('freeserif','',10);

				$border = 0;

				//Create PDF TimeSheet for each employee.
				foreach( $rows as $user_data ) {
					$pdf->AddPage();

					$adjust_x = 10;
					$adjust_y = 10;

					//$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(0, $adjust_y) );

					$pdf->SetFont('','B',22);
					$pdf->Cell(200,8, TTi18n::gettext('Detailed Employee TimeSheet') , $border, 0, 'C');
					$pdf->Ln();
					$pdf->SetFont('','B',12);
					$pdf->Cell(200,5, $current_company->getName() , $border, 0, 'C');
					$pdf->Ln(8);

					$pdf->Rect( $pdf->getX(), $pdf->getY()-1, 200, 14 );

					$pdf->SetFont('','',10);
					$pdf->Cell(30,4, TTi18n::gettext('Employee:') , $border, 0, 'R');
					$pdf->SetFont('','B',10);
					$pdf->Cell(70,4, $user_data['first_name'] .' '. $user_data['last_name'] .' (#'. $user_data['employee_number'] .')', $border, 0, 'L');

					$pdf->SetFont('','',10);
					$pdf->Cell(40,4, TTi18n::gettext('Pay Period:') , $border, 0, 'R');
					$pdf->SetFont('','B',10);
					$pdf->Cell(60,4, $user_data['pay_period'], $border, 0, 'L');
					$pdf->Ln();

					$pdf->SetFont('','',10);
					$pdf->Cell(30,4, TTi18n::gettext('Title:') , $border, 0, 'R');
					$pdf->Cell(70,4, $user_data['title'], $border, 0, 'L');
					$pdf->Cell(40,4, TTi18n::gettext('Branch:') , $border, 0, 'R');
					$pdf->Cell(60,4, $user_data['default_branch'], $border, 0, 'L');
					$pdf->Ln();

					$pdf->Cell(30,4, TTi18n::gettext('Group:') , $border, 0, 'R');
					$pdf->Cell(70,4, $user_data['group'], $border, 0, 'L');
					$pdf->Cell(40,4, TTi18n::gettext('Department:') , $border, 0, 'R');
					$pdf->Cell(60,4, $user_data['default_department'], $border, 0, 'L');
					$pdf->Ln(3);

					$pdf->SetFont('','',10);
					//Start displaying dates/times here. Start with header.
					$column_widths = array(
										'line' => 5,
										'date_stamp' => 20,
										'dow' => 10,
										'in_punch_time_stamp' => 20,
										'out_punch_time_stamp' => 20,
										'worked_time' => 15,
										'paid_time' => 15,
										'regular_time' => 15,
										'over_time' => 37,
										'absence_time' => 43,
										);


					if ( isset($user_data['data']) AND is_array($user_data['data']) ) {
						if ( isset($filter_data['date_type']) AND $filter_data['date_type'] == 'pay_period_ids' )  {
							//Fill in any missing days, only if they select by pay period.
							$pplf = new PayPeriodListFactory();
							$pplf->getById( $user_data['pay_period_id'] );
							if ( $pplf->getRecordCount() == 1 ) {
								$pp_obj = $pplf->getCurrent();

								for( $d=TTDate::getBeginDayEpoch($pp_obj->getStartDate()); $d <= $pp_obj->getEndDate(); $d+=86400) {
									if ( Misc::inArrayByKeyAndValue($user_data['data'], 'date_stamp', TTDate::getBeginDayEpoch($d) ) == FALSE ) {
										$user_data['data'][] = array(
																'date_stamp' => TTDate::getBeginDayEpoch($d),
																'in_punch_time' => NULL,
																'out_punch_time' => NULL,
																'worked_time' => NULL,
																'regular_time' => NULL,
																'over_time' => NULL,
																'paid_time' => NULL,
																'absence_time' => NULL
															);

									}
								}
							}
						}
						$user_data['data'] = Sort::Multisort( $user_data['data'], 'date_stamp', NULL, 'ASC' );

						$week_totals = Misc::preSetArrayValues( NULL, array( 'worked_time', 'paid_time', 'absence_time', 'regular_time', 'over_time' ), 0 );
						$totals = array();
						$totals = Misc::preSetArrayValues( $totals, array( 'worked_time', 'paid_time', 'absence_time', 'regular_time', 'over_time' ), 0 );

						$i=1;
						$x=1;
						$y=1;
						$max_i = count($user_data['data']);
						foreach( $user_data['data'] as $data) {
							//print_r($data);

							//Show Header
							if ( $i == 1 OR $x == 1 ) {
								if ( $x == 1 ) {
									$pdf->Ln();
								}

								$line_h = 5;
								$cell_h_min = $cell_h_max = $line_h * 2;

								$pdf->SetFont('','B',10);
								$pdf->setFillColor(220,220,220);
								$pdf->MultiCell( $column_widths['line'], $line_h, '#' , 1, 'C', 1, 0);
								$pdf->MultiCell( $column_widths['date_stamp'], $line_h, TTi18n::gettext('Date') , 1, 'C', 1, 0);
								$pdf->MultiCell( $column_widths['dow'], $line_h, TTi18n::gettext('DoW') , 1, 'C', 1, 0);
								$pdf->MultiCell( $column_widths['in_punch_time_stamp'], $line_h, TTi18n::gettext('In') , 1, 'C', 1, 0);
								$pdf->MultiCell( $column_widths['out_punch_time_stamp'], $line_h, TTi18n::gettext('Out') , 1, 'C', 1, 0);
								$pdf->MultiCell( $column_widths['worked_time'], $line_h, TTi18n::gettext('Worked Time') , 1, 'C', 1, 0);
								$pdf->MultiCell( $column_widths['paid_time'], $line_h, TTi18n::gettext('Paid Time') , 1, 'C', 1, 0);
								$pdf->MultiCell( $column_widths['regular_time'], $line_h, TTi18n::gettext('Regular Time') , 1, 'C', 1, 0);
								$pdf->MultiCell( $column_widths['over_time'], $line_h, TTi18n::gettext('Over Time') , 1, 'C', 1, 0);
								$pdf->MultiCell( $column_widths['absence_time'], $line_h, TTi18n::gettext('Absence Time') , 1, 'C', 1, 0);
								$pdf->Ln();
							}

							$data = Misc::preSetArrayValues( $data, array('date_stamp', 'in_punch_time_stamp', 'out_punch_time_stamp', 'worked_time', 'paid_time', 'absence_time', 'regular_time', 'over_time' ), '--' );

							if ( $x % 2 == 0 ) {
								$pdf->setFillColor(220,220,220);
							} else {
								$pdf->setFillColor(255,255,255);
							}

							if ( $data['date_stamp'] !== '' ) {
								$default_line_h = 4;
								$line_h = $default_line_h;

								$total_rows_arr = array();

								//Find out how many punches fall on this day, so we can change row height to fit.
								$total_punch_rows = 1;
								if ( isset($punch_rows[$user_data['pay_period_id']][$user_data['user_id']][$data['date_stamp']]) ) {
									$day_punch_data = $punch_rows[$user_data['pay_period_id']][$user_data['user_id']][$data['date_stamp']];
									$total_punch_rows = count($day_punch_data);
								}
								$total_rows_arr[] = $total_punch_rows;

								$total_over_time_rows = 1;
								if ( $data['over_time'] > 0 AND isset($data['categorized_time']['over_time_policy']) ) {
									$total_over_time_rows = count($data['categorized_time']['over_time_policy']);
								}
								$total_rows_arr[] = $total_over_time_rows;

								$total_absence_rows = 1;
								if ( $data['absence_time'] > 0 AND isset($data['categorized_time']['absence_policy']) ) {
									$total_absence_rows = count($data['categorized_time']['absence_policy']);
								}
								$total_rows_arr[] = $total_absence_rows;

								rsort($total_rows_arr);
								$max_rows = $total_rows_arr[0];
								$line_h = $default_line_h*$max_rows;

								$pdf->SetFont('','',9);
								$pdf->Cell( $column_widths['line'], $line_h, $x , 1, 0, 'C', 1);
								$pdf->Cell( $column_widths['date_stamp'], $line_h, TTDate::getDate('DATE', $data['date_stamp'] ), 1, 0, 'C', 1);
								$pdf->Cell( $column_widths['dow'], $line_h, date('D', $data['date_stamp']) , 1, 0, 'C', 1);

								$pre_punch_x = $pdf->getX();
								$pre_punch_y = $pdf->getY();

								//Print Punches
								if ( isset($day_punch_data) ) {
									$pdf->SetFont('','',8);

									$n=0;
									foreach( $day_punch_data as $punch_control_id => $punch_data ) {
										if ( !isset($punch_data[10]['time_stamp']) ) {
											$punch_data[10]['time_stamp'] = NULL;
											$punch_data[10]['type_code'] = NULL;
										}
										if ( !isset($punch_data[20]['time_stamp']) ) {
											$punch_data[20]['time_stamp'] = NULL;
											$punch_data[20]['type_code'] = NULL;
										}

										if ( $n > 0 ) {
											$pdf->setXY( $pre_punch_x, $punch_y+$default_line_h);
										}

										$pdf->Cell( $column_widths['in_punch_time_stamp'], $line_h/$total_punch_rows, TTDate::getDate('TIME', $punch_data[10]['time_stamp'] ) .' '. $punch_data[10]['type_code'], 1, 0, 'C', 1);
										$pdf->Cell( $column_widths['out_punch_time_stamp'], $line_h/$total_punch_rows, TTDate::getDate('TIME', $punch_data[20]['time_stamp'] ) .' '. $punch_data[20]['type_code'], 1, 0, 'C', 1);

										$punch_x = $pdf->getX();
										$punch_y = $pdf->getY();

										$n++;
									}

									$pdf->setXY( $punch_x, $pre_punch_y);

									$pdf->SetFont('','',9);
								} else {
									$pdf->Cell( $column_widths['in_punch_time_stamp'], $line_h, '', 1, 0, 'C', 1);
									$pdf->Cell( $column_widths['out_punch_time_stamp'], $line_h, '', 1, 0, 'C', 1);
								}

								$pdf->Cell( $column_widths['worked_time'], $line_h, TTDate::getTimeUnit( $data['worked_time'] ) , 1, 0, 'C', 1);
								$pdf->Cell( $column_widths['paid_time'], $line_h,  TTDate::getTimeUnit( $data['paid_time'] ), 1, 0, 'C', 1);
								$pdf->Cell( $column_widths['regular_time'], $line_h, TTDate::getTimeUnit( $data['regular_time'] ), 1, 0, 'C', 1);

								if ( $data['over_time'] > 0 AND isset($data['categorized_time']['over_time_policy']) ) {
									$pre_over_time_x = $pdf->getX();
									$pdf->SetFont('','',8);

									//Count how many absence policy rows there are.
									$over_time_policy_total_rows = count($data['categorized_time']['over_time_policy']);
									foreach( $data['categorized_time']['over_time_policy'] as $policy_id => $value ) {
										$pdf->Cell( $column_widths['over_time'], $line_h/$total_over_time_rows, $otp_columns['over_time_policy-'.$policy_id].': '.TTDate::getTimeUnit( $value ), 1, 0, 'C', 1);
										$pdf->setXY( $pre_over_time_x, $pdf->getY()+($line_h/$total_over_time_rows) );

										$over_time_x = $pdf->getX();
									}
									$pdf->setXY( $over_time_x+$column_widths['over_time'], $pre_punch_y);

									$pdf->SetFont('','',9);
								} else {
									$pdf->Cell( $column_widths['over_time'], $line_h, TTDate::getTimeUnit( $data['over_time'] ), 1, 0, 'C', 1);
								}

								if ( $data['absence_time'] > 0 AND isset($data['categorized_time']['absence_policy']) ) {
									$pre_absence_time_x = $pdf->getX();
									$pdf->SetFont('','',8);

									//Count how many absence policy rows there are.
									$absence_policy_total_rows = count($data['categorized_time']['absence_policy']);
									foreach( $data['categorized_time']['absence_policy'] as $policy_id => $value ) {
										$pdf->Cell( $column_widths['absence_time'], $line_h/$total_absence_rows, $ap_columns['absence_policy-'.$policy_id].': '.TTDate::getTimeUnit( $value ), 1, 0, 'C', 1);
										$pdf->setXY( $pre_absence_time_x, $pdf->getY()+($line_h/$total_absence_rows));
									}

									$pdf->setY( $pdf->getY()-($line_h/$total_absence_rows));

									$pdf->SetFont('','',9);
								} else {
									$pdf->Cell( $column_widths['absence_time'], $line_h, TTDate::getTimeUnit( $data['absence_time'] ), 1, 0, 'C', 1);
								}

								$pdf->Ln();

								unset($day_punch_data);
							}

							$totals['worked_time'] += $data['worked_time'];
							$totals['paid_time'] += $data['paid_time'];
							$totals['absence_time'] += $data['absence_time'];
							$totals['regular_time'] += $data['regular_time'];
							$totals['over_time'] += $data['over_time'];

							$week_totals['worked_time'] += $data['worked_time'];
							$week_totals['paid_time'] += $data['paid_time'];
							$week_totals['absence_time'] += $data['absence_time'];
							$week_totals['regular_time'] += $data['regular_time'];
							$week_totals['over_time'] += $data['over_time'];

							if ( $x % 7 == 0 OR $i == $max_i ) {
								//Show Week Total.
								$total_cell_width = $column_widths['line']+$column_widths['date_stamp']+$column_widths['dow']+$column_widths['in_punch_time_stamp']+$column_widths['out_punch_time_stamp'];
								$pdf->SetFont('','B',9);
								$pdf->Cell( $total_cell_width, 6, TTi18n::gettext('Week Total:').' ', 0, 0, 'R', 0);
								$pdf->Cell( $column_widths['worked_time'], 6, TTDate::getTimeUnit( $week_totals['worked_time'] ) , 0, 0, 'C', 0);
								$pdf->Cell( $column_widths['paid_time'], 6,  TTDate::getTimeUnit( $week_totals['paid_time'] ), 0, 0, 'C', 0);
								$pdf->Cell( $column_widths['regular_time'], 6, TTDate::getTimeUnit( $week_totals['regular_time'] ), 0, 0, 'C', 0);
								$pdf->Cell( $column_widths['over_time'], 6, TTDate::getTimeUnit( $week_totals['over_time'] ), 0, 0, 'C', 0);
								$pdf->Cell( $column_widths['absence_time'], 6, TTDate::getTimeUnit( $week_totals['absence_time'] ), 0, 0, 'C', 0);
								$pdf->Ln(1);

								unset($week_totals);
								$week_totals = Misc::preSetArrayValues( NULL, array( 'worked_time', 'paid_time', 'absence_time', 'regular_time', 'over_time' ), 0 );

								$x=0;
								$y++;

								//Force page break every 3 weeks.
								if ( $y == 4 AND $i !== $max_i ) {
									$pdf->AddPage();
								}
							}


							$i++;
							$x++;
						}
						unset($data);
					}

					if ( isset($totals) AND is_array($totals) ) {
						//Display overall totals.
						$pdf->Ln(4);
						$total_cell_width = $column_widths['line']+$column_widths['date_stamp']+$column_widths['dow']+$column_widths['in_punch_time_stamp'];
						$pdf->SetFont('','B',9);
						$pdf->Cell( $total_cell_width, 6, '' , 0, 0, 'R', 0);
						$pdf->Cell( $column_widths['out_punch_time_stamp'], 6, TTi18n::gettext('Overall Total:').' ', 'T', 0, 'R', 0);
						$pdf->Cell( $column_widths['worked_time'], 6, TTDate::getTimeUnit( $totals['worked_time'] ) , 'T', 0, 'C', 0);
						$pdf->Cell( $column_widths['paid_time'], 6,  TTDate::getTimeUnit( $totals['paid_time'] ), 'T', 0, 'C', 0);
						$pdf->Cell( $column_widths['regular_time'], 6, TTDate::getTimeUnit( $totals['regular_time'] ), 'T', 0, 'C', 0);
						$pdf->Cell( $column_widths['over_time'], 6, TTDate::getTimeUnit( $totals['over_time'] ), 'T', 0, 'C', 0);
						$pdf->Cell( $column_widths['absence_time'], 6, TTDate::getTimeUnit( $totals['absence_time'] ), 'T', 0, 'C', 0);
						$pdf->Ln();
						unset($totals);
					}

					$pdf->SetFont('','',10);
					$pdf->setFillColor(255,255,255);
					$pdf->Ln();

					//Signature lines
					$pdf->MultiCell(200,5, TTi18n::gettext('By signing this timesheet I hereby certify that the above time accurately and fully reflects the time that').' '. $user_data['first_name'] .' '. $user_data['last_name'] .' '.TTi18n::gettext('worked during the designated period.'), $border, 'L');
					$pdf->Ln(5);

					$border = 0;
					$pdf->Cell(40,5, TTi18n::gettext('Employee Signature:'), $border, 0, 'L');
					$pdf->Cell(60,5, '_____________________________' , $border, 0, 'C');
					$pdf->Cell(40,5, TTi18n::gettext('Supervisor Signature:'), $border, 0, 'R');
					$pdf->Cell(60,5, '_____________________________' , $border, 0, 'C');

					$pdf->Ln();
					$pdf->Cell(40,5, '', $border, 0, 'R');
					$pdf->Cell(60,5, $user_data['first_name'] .' '. $user_data['last_name'] , $border, 0, 'C');

					$pdf->Ln();
					$pdf->Cell(140,5, '', $border, 0, 'R');
					$pdf->Cell(60,5, '_____________________________' , $border, 0, 'C');

					$pdf->Ln();
					$pdf->Cell(140,5, '', $border, 0, 'R');
					$pdf->Cell(60,5, TTi18n::gettext('(print name)'), $border, 0, 'C');

					if ( $user_data['verified_time_sheet_date'] != FALSE ) {
						$pdf->Ln();
						$pdf->SetFont('','B',10);
						$pdf->Cell(200,5, TTi18n::gettext('TimeSheet electronically signed by').' '. $user_data['first_name'] .' '. $user_data['last_name'] .' '. TTi18n::gettext('on') .' '. TTDate::getDate('DATE+TIME', $user_data['verified_time_sheet_date'] ), $border, 0, 'C');
						$pdf->SetFont('','',10);
					}


					//Add generated date/time at the bottom.
					$pdf->SetFont('','I',8);
					$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(245, $adjust_y) );
					$pdf->Cell(200,5, TTi18n::gettext('Generated:') .' '. TTDate::getDate('DATE+TIME', $pdf_created_date ), $border, 0, 'C');
				}

				$output = $pdf->Output('','S');
			}

			if ( $output !== FALSE AND Debug::getVerbosity() < 11 ) {
				Misc::FileDownloadHeader('detailed_timesheet.pdf', 'application/pdf', strlen($output));
				echo $output;
				exit;
			} else {
				//Debug::Display();
				echo TTi18n::gettext('ERROR: Employee TimeSheet(s) not available!') . "<br>\n";
				exit;
			}
		
                        
                    }elseif ($action == 'export') {

                           if (isset($rows) AND isset($filter_columns)) {

                               //Add the basic identifing columns.
                               $export_filter_columns = array(
                                   'emp_id' => TTi18n::gettext(' '),
                                   'emp_name' => TTi18n::gettext(' '),
                                   'emp_1' => TTi18n::gettext(' '),
                                   'emp_2' => TTi18n::gettext(' '),
                                   'emp_3' => TTi18n::gettext(' '),
                                   'emp_4' => TTi18n::gettext(' '),
                                   'emp_5' => TTi18n::gettext(' '),
                                   'emp_6' => TTi18n::gettext(' '),
                                   'emp_7' => TTi18n::gettext('Present'), 
                                   'emp_8' => TTi18n::gettext('Leaves'), 
                                   'emp_9' => TTi18n::gettext('Short Leave'), 
                                   'emp_10' => TTi18n::gettext('NoPay'), 
                                   'emp_11' => TTi18n::gettext('Late Attendance'),
                                   'emp_12' => TTi18n::gettext('Early Departure'), 
                                   'emp_13' => TTi18n::gettext('OT'), 
                                   'emp_14' => TTi18n::gettext(' '), 
                                   'emp_15' => TTi18n::gettext(' '),
                                   'emp_16' => TTi18n::gettext(' '), 
                                   'emp_17' => TTi18n::gettext(' '), 
                                   'emp_18' => TTi18n::gettext(' '), 
                                   'emp_19' => TTi18n::gettext(' '), 
                                   'emp_20' => TTi18n::gettext(' '), 
                                   'emp_21' => TTi18n::gettext(' '),
                                   'emp_22' => TTi18n::gettext(' '), 
                                   'emp_23' => TTi18n::gettext(' '),
                                   'emp_24' => TTi18n::gettext(' '), 
                                   'emp_25' => TTi18n::gettext(' '),
                                   'emp_26' => TTi18n::gettext(' '),
                                   'emp_27' => TTi18n::gettext(' '), 
                                   'emp_28' => TTi18n::gettext(' '),
                                   'emp_29' => TTi18n::gettext(' '),
                                   'emp_30' => TTi18n::gettext(' '),
                                   'emp_31' => TTi18n::gettext(' '),
                               );


                               $filter_header_data = array(
                                   'group_ids' => $filter_data['group_ids'],
                                   'branch_ids' => $filter_data['branch_ids'],
                                   'department_ids' => $filter_data['department_ids'],
                                   'pay_period_ids' => $filter_data['pay_period_ids']
                               );

                               foreach ($filter_header_data as $fh_key => $filter_header) {
                                   $dlf = new DepartmentListFactory();
                                   if ($fh_key == 'department_ids') {
                                       foreach ($filter_header as $dep_id) {
                                           $department_list[] = $dlf->getNameById($dep_id);
                                       }
                                       $dep_strng = implode(', ', $department_list);
                                   }

                                   $blf = new BranchListFactory();
                                   if ($fh_key == 'branch_ids') {
                                       foreach ($filter_header as $br_id) {
                                           $branch_list[] = $blf->getNameById($br_id);
                                       }
                                       $br_strng = implode(', ', $branch_list);
                                   }

                                   $br_strng = $blf->getNameById($br_id); //eranda add code dynamic header data report

                                   if ($br_strng == null) {
                                       $company_name = $current_company->getName();
                                       $addrss1 = $current_company->getAddress1();
                                       $address2 = $current_company->getAddress2();
                                       $city = $current_company->getCity();
                                       $postalcode = $current_company->getPostalCode();
                                   } else {
                                       $company_name = $blf->getNameById($br_id);
                                       $addrss1 = $blf->getAddress1ById($br_id);
                                       $address2 = $blf->getAddress2ById($br_id);
                                       $city = $blf->getCityById($br_id);
                                       $postalcode = $blf->getPostCodeById($br_id);
                                   }
                                   //    echo "<pre>"; print_r($blf->getNameById($br_id)); die;
                                   $uglf = new UserGroupListFactory();
                                   if ($fh_key == 'group_ids') {
                                       foreach ($filter_header as $gr_id) {
                                           $group_list[] = $uglf->getNameById($gr_id);
                                       }
                                       $gr_strng = implode(', ', $group_list);
                                   }
                               }
                               if ($dep_strng == '') {
                                   $dep_strng = 'All';
                               }

                               $pplf = new PayPeriodListFactory();
                               if (isset($filter_data['pay_period_ids'][0])) {
                                   $pay_period_start = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getStartDate();
                                   $pay_period_end = $pplf->getById($filter_data['pay_period_ids'][0])->getCurrent()->getEndDate();
                               } else {
                                   $pay_period_start = $filter_data['start_date'];
                                   $pay_period_end = $filter_data['end_date'];
                               }

                               $date_month = date('m-Y', $pay_period_start);
                               $nof_days_for_month = cal_days_in_month(CAL_GREGORIAN, date('m', $pay_period_start), date('Y', $pay_period_start));

                               $dates = array();
                               $current = $pay_period_start;
                               $last = $pay_period_end;

                               $list_start_date = date('d', $pay_period_start);
                               $list_end_date = date('d', $pay_period_end);
                               
                               while ($current <= $last) {

                                   $dates[] = date('d', $current);
                                   $current = strtotime('+1 day', $current);
                               }

                               //Sort array by employee_number
                               foreach ($rows as $key => $row) {
                                   $employee_number[$key] = $row['employee_number'];
                               }

                               array_multisort($employee_number, SORT_ASC, $rows); /**/

                               $row_data_day_key = array();
                               $j1 = 0;

                               $fulldata = array();
                               $temp_data = array();

                               foreach ($rows as $row) {

                                   $present_days = 0;
                                   $absent_days = 0;
                                   $leave_days = 0;
                                   $week_off = 0;
                                   $holidays = 0;

                                   $nof_presence = 0;
                                   $nof_leaves = 0;
                                   $nof_weekoffs = 0;
                                   $nof_holidays = 0;
                                   $nof_ot = 0;
                                   $no_of_late_attendance = 0;
                                   $no_of_early_dep = 0;
                                   $no_of_short_leaves = 0;
                                   $nopay = 0;

                                   $temp_row2 = array();
                                   $temp_row3 = array();
                                   $temp_row4 = array();
                                   $temp_row5 = array();
                                   $temp_row6 = array();
                                   $temp_row7 = array();
                                   $temp_row8 = array();
                                   $temp_rows = array();

                                   foreach ($row['data'] as $row1) {

                                       if ($row1['date_stamp'] != '') {
                                           $row_dt = str_replace('/', '-', $row1['date_stamp']);

                                           $dat_day = date('d', strtotime($row_dt));
                                           //echo '<br><pre>'.$dat_day;
                                           $row_data_day_key[$dat_day] = $row1;

                                           //                        $row_data_day_key[$dat_day]['total_OT'] = $tot_ot_hours;                             
                                       } else {
                                           $tot_ot_hours_data = $row1['over_time'];
                                           $tot_worked_actual_hours_data = $row1['actual_time'];
                                           $tot_worked_hours_data = explode(':', $row1['worked_time']);
                                           $tot_worked_sec_data = ($tot_worked_hours_data[0] * 60 * 60) + ($tot_worked_hours_data[1] * 60);
                                       }
                                   }

                                   //$nof_presence=0; $nof_leaves=0; $nof_weekoffs=0; $nof_holidays=0; $nof_ot=0; $no_of_late_attendance=0; $no_of_early_dep=0; $no_of_short_leaves=0; $nopay=0;

                                   $day_row = '';
                                   $shift_id_row = '';
                                   $shift_in_row = '';
                                   $shift_out_row = '';
                                   $late_row = '';
                                   $early_row = '';
                                   $status1_row = '';
                                   $status2_row = '';
                                   $nof_half_days = 0;

                                   $earlySec = $lateSec = 0;

                                   for ($i1 = $list_start_date; $i1 <= $list_end_date; $i1++) {

                                       //---Get Total values
                                       $status1 = '';

                                       $lateSec = strtotime($row_data_day_key[sprintf("%02d", $i1)]['shedule_start_time']) - strtotime($row_data_day_key[sprintf("%02d", $i1)]['min_punch_time_stamp']);
                                       $earlySec = strtotime($row_data_day_key[sprintf("%02d", $i1)]['shedule_end_time']) - strtotime($row_data_day_key[sprintf("%02d", $i1)]['max_punch_time_stamp']);

                                       $udlf = new UserDateListFactory();
                                       $pclf = new PunchControlListFactory();

                                       $udlf->getByUserIdAndDate($row['user_id'], date('Y-m-d', strtotime($i1 . '-' . $date_month)));
                                       $udlf_obj = $udlf->getCurrent();

                                       $pclf->getByUserDateId($udlf_obj->getId()); //par - user_date_id
                                       $pc_obj_arr = $pclf->getCurrent()->data;


                                       //if punch exists
                                       if (!empty($pc_obj_arr)) {
                                           $status1 = 'P';
                                           //check late come and early departure
                                           $elf = new ExceptionListFactory();
                                           $elf->getByUserDateIdAndExceptionPolicyId($udlf_obj->getId(), 4); //par - user_date_id, 4 - late exception
                                           $ex_obj_arr = $elf->getCurrent()->data;
                                           if (!empty($ex_obj_arr)) {
                                               $status1 = 'LP';
                                           }
                                       } else {
                                           $status1 = 'WO';

                                           $aluelf = new AbsenceLeaveUserEntryRecordListFactory();
                                           $aluelf->getAbsencePolicyByUserDateId($udlf_obj->getId());
                                           $absLeave_obj_arr = $aluelf->getCurrent()->data;
                                           if (!empty($absLeave_obj_arr)) {
                                               $leaveName_arr = explode(' ', $absLeave_obj_arr['absence_name']);
                                               $status1 = substr($leaveName_arr[0], 0, 1) . substr($leaveName_arr[1], 0, 1);


                                               if ($status1 != 'WO') {
                                                   //$tot_array['L'][]=$i1;
                                                   if ($absLeave_obj_arr['absence_leave_id'] == 2) {//Half day leave
                                                       $tot_array['L'] += 0.5;
                                                       $tot_array['P'] += 0.5;
                                                       $tot_array['H'] += 1;
                                                   } else if ($absLeave_obj_arr['absence_leave_id'] == 1) {//Full day leave
                                                       $tot_array['L'] += 1;
                                                   } else if ($absLeave_obj_arr['absence_leave_id'] == 3) {//Short leave
                                                       $tot_array['SH'] += 1;
                                                   }
                                               }
                                           }
                                       }


                                       $hlf = new HolidayListFactory();
                                       $hlf->getByPolicyGroupUserIdAndDate($row['user_id'], date('Y-m-d', strtotime($i1 . '-' . $date_month)));
                                       $hday_obj_arr = $hlf->getCurrent()->data;

                                       if (!empty($hday_obj_arr)) {
                                           $status1 = 'HLD';
                                       }
                                       $tot_array[$status1] += 1;
                                       //---End Get Total values
                                       //---Day row value
                                       $day_row = $day_row . '<td>' . $i1 . '</td>';
                                       array_push($temp_row2, $i1);

                                       //---Shift ID row value
                                       //$udlf = new UserDateListFactory();
                                       $slf = new ScheduleListFactory();

                                       //$udlf->getByUserIdAndDate($row['user_id'],date('Y-m-d',  strtotime($i1.'-'.$date_month)));
                                       //$udlf_obj = $udlf->getCurrent();

                                       $slf->getByUserDateId($udlf_obj->getId()); //par - user_date_id
                                       $sp_obj_arr = $slf->getCurrent()->data;

                                       $schedule_name_arr = explode('-', $sp_obj_arr['shedule_policy_name']);
                                       $status_id = $schedule_name_arr[1];
                                       // $shift_id_row = $shift_id_row.'<td>'.$status_id.'</td>';
                                       //---Shift In row value
                                       $shift_in_row = $shift_in_row . '<td>' . $row_data_day_key[sprintf("%02d", $i1)]['min_punch_time_stamp'] . '</td>';
                                       array_push($temp_row3, $row_data_day_key[sprintf("%02d", $i1)]['min_punch_time_stamp']);

                                       //---Shift Out row value
                                       $shift_out_row = $shift_out_row . '<td>' . $row_data_day_key[sprintf("%02d", $i1)]['max_punch_time_stamp'] . '</td>';
                                       array_push($temp_row4, $row_data_day_key[sprintf("%02d", $i1)]['min_punch_time_stamp']);

                                       //---Late row value
                                       //$udlf = new UserDateListFactory();
                                       //$slf = new ScheduleListFactory();
                                       //$udlf->getByUserIdAndDate($row['user_id'],date('Y-m-d',  strtotime($i1.'-'.$date_month)));
                                       //$udlf_obj = $udlf->getCurrent();
                                       //$slf->getByUserDateId($udlf_obj->getId()); //par - user_date_id
                                       //$sp_obj_arr = $slf->getCurrent()->data;

                                       $late = '';

                                       if (!empty($sp_obj_arr) && $row_data_day_key[sprintf("%02d", $i1)]['min_punch_time_stamp'] != '') {
                                           $lateSec = strtotime($row_data_day_key[sprintf("%02d", $i1)]['shedule_start_time']) - strtotime($row_data_day_key[sprintf("%02d", $i1)]['min_punch_time_stamp']);
                                           if ($lateSec < 0) {
                                               $late = gmdate("H:i", abs($lateSec));
                                               $tot_array['MLA'] += 1;
                                           }
                                       }
                                       $late_row = $late_row . '<td>' . $late . '</td>';
                                       array_push($temp_row5, $late);

                                       //---Early row value
                                       //$udlf = new UserDateListFactory();
                                       //$slf = new ScheduleListFactory();
                                       //$udlf->getByUserIdAndDate($row['user_id'],date('Y-m-d',  strtotime($i1.'-'.$date_month)));
                                       //$udlf_obj = $udlf->getCurrent();
                                       //$slf->getByUserDateId($udlf_obj->getId()); //par - user_date_id
                                       //$sp_obj_arr = $slf->getCurrent()->data;

                                       $early = '';
                                       if (!empty($sp_obj_arr) && $row_data_day_key[sprintf("%02d", $i1)]['max_punch_time_stamp'] != '') {
                                           $earlySec = strtotime($row_data_day_key[sprintf("%02d", $i1)]['shedule_end_time']) - strtotime($row_data_day_key[sprintf("%02d", $i1)]['max_punch_time_stamp']);

                                           if ($earlySec > 0) {
                                               $early = gmdate("H:i", abs($earlySec));
                                               $tot_array['ELD'] += 1;
                                           }
                                       }
                                       $early_row = $early_row . '<td>' . $early . '</td>';
                                       array_push($temp_row6, $early);


                                       //---Status 1 row value
                                       $status1 = '';
                                       $lateSec = strtotime($row_data_day_key[sprintf("%02d", $i1)]['shedule_start_time']) - strtotime($row_data_day_key[sprintf("%02d", $i1)]['min_punch_time_stamp']);
                                       $earlySec = strtotime($row_data_day_key[sprintf("%02d", $i1)]['shedule_end_time']) - strtotime($row_data_day_key[sprintf("%02d", $i1)]['max_punch_time_stamp']);

                                       //$udlf = new UserDateListFactory();
                                       //$pclf = new PunchControlListFactory();
                                       $elf = new ExceptionListFactory(); //--Add code eranda
                                       //$udlf->getByUserIdAndDate($row['user_id'],date('Y-m-d',  strtotime($i1.'-'.$date_month)));
                                       //$udlf_obj = $udlf->getCurrent();

                                       $pclf->getByUserDateId($udlf_obj->getId()); //par - user_date_id
                                       $pc_obj_arr = $pclf->getCurrent()->data;

                                       $elf->getByUserDateId($udlf_obj->getId());
                                       $elf_obj = $elf->getCurrent();

                                       //if punch exists
                                       if (!empty($pc_obj_arr)) {

                                           $status1 = 'P';

                                           if (!empty($elf_obj->data)) {
                                               //  if($epclf_obj->getExceptionPolicyControlID()) {
                                               foreach ($elf as $elf_obj) {
                                                   if ($elf_obj->getExceptionPolicyID() == '29' || $elf_obj->getExceptionPolicyID() == '5') {
                                                       $status1 = 'ED'; //Early Departure
                                                   }
                                                   if ($elf_obj->getExceptionPolicyID() == '28' || $elf_obj->getExceptionPolicyID() == '4') {
                                                       $status1 = 'LP'; //Late Presents
                                                   }
                                                   if ($elf_obj->getExceptionPolicyID() == '36' || $elf_obj->getExceptionPolicyId() == '37' || $elf_obj->getExceptionPolicyID() == '12' || $elf_obj->getExceptionPolicyID() == '13') {
                                                       $status1 = 'MIS'; //Missed Punch
                                                   }
                                                   if ($elf_obj->getExceptionPolicyID() == '25' || $elf_obj->getExceptionPolicyID() == '1') {
                                                       $status1 = 'P'; //Unscheduled absent
                                                   }
                                               }
                                           }
                                       } else {
                                           $status1 = 'WO';

                                           //Check user leaves
                                           $aluelf = new AbsenceLeaveUserEntryRecordListFactory();
                                           $aluelf->getAbsencePolicyByUserDateId($udlf_obj->getId());
                                           $absLeave_obj_arr = $aluelf->getCurrent()->data;
                                           if (!empty($absLeave_obj_arr)) {
                                               $leaveName_arr = explode(' ', $absLeave_obj_arr['absence_name']);
                                               $status1 = substr($leaveName_arr[0], 0, 1) . substr($leaveName_arr[1], 0, 1);
                                           } else {

                                               //Check Holidays
                                               //$hlf = new HolidayListFactory();
                                               $hlf->getByPolicyGroupUserIdAndDate($row['user_id'], date('Y-m-d', strtotime($i1 . '-' . $date_month)));
                                               $hday_obj_arr = $hlf->getCurrent()->data;

                                               if (!empty($hday_obj_arr)) {
                                                   $status1 = 'HLD';
                                               } else {
                                                   //Schedule shifts
                                                   //$slf = new ScheduleListFactory();
                                                   $slf->getByUserDateId($udlf_obj->getId()); //par - user_date_id
                                                   $sp_obj_arr = $slf->getCurrent()->data;

                                                   if (!empty($sp_obj_arr)) {
                                                       $status1 = 'A';
               //                                        $tot_array['NP'] += 1;
                                                   } else {
                                                       $date_name = date('l', strtotime($i1 . '-' . $date_month));

                                                       if ($date_name == 'Saturday' || $date_name == 'Sunday') {

                                                       } else {
                                                           $status1 = 'AB';
                                                           $tot_array['NP'] += 1;
                                                       }
                                                   }
                                               }
                                           }
                                       }
                                       $status1_row = $status1_row . '<td>' . $status1 . '</td>';
                                       array_push($temp_row7, $status1);
                                       //---End Status 1 row value  
                                       //---Status 2 row value
                                       $status2_row = $status2_row . '<td>' . date('D', strtotime($i1 . '-' . $date_month)) . '</td>';
                                       array_push($temp_row8, date('D', strtotime($i1 . '-' . $date_month)));
                                       unset($row_data_day_key[sprintf("%02d", $i1)]);
                                   }

                                   $udtlf = new UserDateTotalListFactory();
                                   $udtlf->getByCompanyIDAndUserIdAndStatusAndStartDateAndEndDate($current_company->getId(), $row['user_id'], 10, date('Y-m-d', $pay_period_start), date('Y-m-d', $pay_period_end));
                                   if ($udtlf->getRecordCount() > 0) {
                                       foreach ($udtlf as $udt_obj) {
                                           if ($udt_obj->getOverTimePolicyID() != 0) {
                                               $tot_array['OT'] += 1;
                                           }
                                       }
                                   }

                                   if (isset($tot_array['P'])) {
                                       $nof_presence += $tot_array['P'];
                                   }

                                   if (isset($tot_array['LP'])) {
                                       $nof_presence += $tot_array['LP'];
                                   }

                                   if (isset($tot_array['WO'])) {
                                       $nof_weekoffs = $tot_array['WO'];
                                   }

                                   if (isset($tot_array['HLD'])) {
                                       $nof_holidays = $tot_array['HLD'];
                                   }

                                   if (isset($tot_array['L'])) {
                                       $nof_leaves = $tot_array['L'];
                                   }

                                   if (isset($tot_array['OT'])) {
                                       $nof_ot = $tot_array['OT'];
                                   }

                                   if (isset($tot_array['H'])) {
                                       $nof_half_days = $tot_array['H'];
                                   }

                                   if (isset($tot_array['MLA'])) {
                                       $no_of_late_attendance = $tot_array['MLA'];
                                   }

                                   if (isset($tot_array['ELD'])) {
                                       $no_of_early_dep = $tot_array['ELD'];
                                   }
                                   if (isset($tot_array['SH'])) {
                                       $no_of_short_leaves = $tot_array['SH'];
                                   }
                                   if (isset($tot_array['NP'])) {
                                       $nopay = $tot_array['NP'];
                                   }

                                   unset($tot_array);

                                   for ($t = 0; $t <= 9; $t++) {

                                       if ($t == 0) {
                                            
                                           if($j1 == 0){
                                               continue;
                                           }else{
                                                $temp_data['emp_7'] = 'Present';
                                                $temp_data['emp_8'] = 'Leaves';
                                                $temp_data['emp_9'] = 'Short Leave';
                                                $temp_data['emp_10'] = 'NoPay';
                                                $temp_data['emp_11'] = 'Late Attendance';
                                                $temp_data['emp_12'] = 'Early Departure';
                                                $temp_data['emp_13'] = 'OT';
                                           }
                                           
                                       }else if ($t == 1) {
                                          
                                           $temp_data['emp_id'] = $row['employee_number'];
                                           $temp_data['emp_name'] = $row['first_name'] . ' ' . $row['last_name'];
                                           $temp_data['emp_7'] = $nof_presence;
                                           $temp_data['emp_8'] = $nof_leaves;
                                           $temp_data['emp_9'] = $no_of_short_leaves;
                                           $temp_data['emp_10'] = $nopay;
                                           $temp_data['emp_11'] = $no_of_late_attendance;
                                           $temp_data['emp_12'] = $no_of_early_dep;
                                           $temp_data['emp_13'] = $nof_ot;
                                           
                                       } else if ($t == 2) {
                                           $temp_data['emp_id'] = 'Day';

                                           for ($temp2 = 1; $temp2 <= count($temp_row2); $temp2++) {
                                               $temp_data['emp_' . $temp2] = $temp_row2[$temp2 - 1];
                                           }

                                           unset($temp_row2);
                                       } else if ($t == 3) {
                                           $temp_data['emp_id'] = 'Shift In';

                                           for ($temp3 = 1; $temp3 <= count($temp_row3); $temp3++) {
                                               $temp_data['emp_' . $temp3] = $temp_row3[$temp3 - 1];
                                           }

                                           unset($temp_row3);
                                       } else if ($t == 4) {
                                           $temp_data['emp_id'] = 'Shift Out';

                                           for ($temp4 = 1; $temp4 <= count($temp_row4); $temp4++) {
                                               $temp_data['emp_' . $temp4] = $temp_row4[$temp4 - 1];
                                           }

                                           unset($temp_row4);
                                       } else if ($t == 5) {
                                           $temp_data['emp_id'] = 'Late Attendance';

                                           for ($temp5 = 1; $temp5 <= count($temp_row5); $temp5++) {
                                               $temp_data['emp_' . $temp5] = $temp_row5[$temp5 - 1];
                                           }

                                           unset($temp_row5);
                                       } else if ($t == 6) {
                                           $temp_data['emp_id'] = 'Early Departure';

                                           for ($temp6 = 1; $temp6 <= count($temp_row6); $temp6++) {
                                               $temp_data['emp_' . $temp6] = $temp_row6[$temp6 - 1];
                                           }

                                           unset($temp_row4);
                                       } else if ($t == 7) {
                                           $temp_data['emp_id'] = 'Status 1';

                                           for ($temp7 = 1; $temp7 <= count($temp_row7); $temp7++) {
                                               $temp_data['emp_' . $temp7] = $temp_row7[$temp7 - 1];
                                           }

                                           unset($temp_row7);
                                       } else if ($t == 8) {
                                           $temp_data['emp_id'] = 'Status 2';

                                           for ($temp8 = 1; $temp8 <= count($temp_row8); $temp8++) {
                                               $temp_data['emp_' . $temp8] = $temp_row8[$temp8 - 1];
                                           }

                                           unset($temp_row8);
                                       }

                                       $fulldata[$j1] = $temp_data;
                                       $j1++;
                                       unset($temp_data);
                                   }
                               }

                               Debug::Text('Exporting as CSV', __FILE__, __LINE__, __METHOD__, 10);
                               $data = Misc::Array2CSV($fulldata, $export_filter_columns);
                               Misc::FileDownloadHeader('MonthlyAttendancePerUser.csv', 'application/csv', strlen($data));
                               echo $data;
                               
                           } else {
                               echo TTi18n::gettext("No Data To Export!") . "<br>\n";
                           }

                    } else if ($action == 'display_report'){
                        if ( isset($rows) AND isset($filter_columns) ) {
                            
                        if( $filter_data['export_type'] == 'pdfMonthlyDetailAttendance'){
                        Debug::Text('Exporting as PDF', __FILE__, __LINE__, __METHOD__,10);
                                
                                $tsdr= new TimesheetDetailReport();//new code         
                               
                                $output = $tsdr->MonthlyAttendanceDetailed($rows, $filter_columns, $filter_data, $current_user, $current_company);//new code                               
                                                                                              
                                if ( Debug::getVerbosity() < 11 ) {                                    
                                    Misc::FileDownloadHeader('MonthlyAttendanceReport.pdf', 'application/pdf', strlen($output));
                                    echo $output;
                                    exit;                           
                                }
                            }
                        elseif ($filter_data['export_type'] == 'excelMonthlyDetailAttendance') { //new code  added by Thusitha
                                
                                $tsdr= new TimesheetDetailReport();   
                                
                                $output = $tsdr->MonthlyAttendanceDetailedExcelExport($rows, $filter_columns, $filter_data, $current_user, $current_company);//new code 
                            
                            }
                    
                    }else {
			$smarty->assign_by_ref('generated_time', TTDate::getTime() );
			$smarty->assign_by_ref('pay_period_options', $pay_period_options );
			$smarty->assign_by_ref('filter_data', $filter_data );
			$smarty->assign_by_ref('columns', $filter_columns );
			$smarty->assign_by_ref('rows', $rows);

			$smarty->display('report/TimesheetDetailReport.tpl');
		}
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
				//$filter_data['user_ids'] = array_keys( UserListFactory::getByCompanyIdArray( $current_company->getId(), FALSE, FALSE ) );

				$filter_data['user_status_ids'] = array( -1 );
				$filter_data['branch_ids'] = array( -1 );
				$filter_data['department_ids'] = array( -1 );
				$filter_data['user_title_ids'] = array( -1 );
				$filter_data['pay_period_ids'] = array( '-0000-'.@array_shift(array_keys((array)$pay_period_options)) );
				$filter_data['start_date'] = $default_start_date;
				$filter_data['end_date'] = $default_end_date; //TTDate::getDate( strtotime(time) );
				$filter_data['group_ids'] = array( -1 );

				//$filter_data['user_ids'] = array_keys( UserListFactory::getByCompanyIdArray( $current_company->getId(), FALSE, FALSE ) );
				if ( !isset($filter_data['column_ids']) ) {
					$filter_data['column_ids']	= array();
				}

				$filter_data['column_ids'] = array_merge( $filter_data['column_ids'],
										array(
											'-1000-date_stamp',
											'-1090-worked_time',
											'-1130-paid_time',
											'-1140-regular_time'
												) );

				$filter_data['primary_sort'] = '-1000-date_stamp';
				$filter_data['secondary_sort'] = '-1090-worked_time';
/*
				$filter_data['column_ids'] = array(
											'date_stamp',
											'worked_time',
											'paid_time',
											'regular_time'
												);
*/

			}
		}
		$filter_data = Misc::preSetArrayValues( $filter_data, array('include_user_ids', 'exclude_user_ids', 'user_status_ids', 'group_ids', 'branch_ids', 'department_ids', 'punch_branch_ids', 'punch_department_ids', 'user_title_ids', 'pay_period_ids', 'column_ids' ), NULL);

		$ulf = new UserListFactory();
		$all_array_option = array('-1' => TTi18n::gettext('-- All --'));

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
                
                //FL ADDED FOR HIDE BUTTON
                $hidden_elements = Misc::prependArray( array( 'displayReport' => '', 'displayTimeSheet' => 'hidden', 'displayDetailedTimeSheet' => 'hidden', 'export' => 'hidden') );
                
                $smarty->assign('hidden_elements',$hidden_elements); // See index.php
              
                //FL ADDED FOR EXPORT TYPE
                $filter_data['export_type_options'] = Misc::prependArray( array(  'pdfMonthlyDetailAttendance' => TTi18n::gettext('Monthly Attendance Report'),'excelMonthlyDetailAttendance' => TTi18n::gettext('Monthly Attendance Report Excel')) );
                //$filter_data['export_type_options'] = Misc::prependArray( array(  'excelMonthlyDetailAttendance' => TTi18n::gettext('Monthly Attendance Report Excel')) ); 
//                $filter_data['export_type_options'] = Misc::prependArray( array(  'pdfMonthlyDetailAttendance' => TTi18n::gettext('Monthly Attendance Report'),'csv' => TTi18n::gettext('CSV (Excel)')) );
//              $filter_data['export_type_options'] = Misc::prependArray( array( 'csv' => TTi18n::gettext('CSV (Excel)'), 'pdfOTDetails' => TTi18n::gettext('OT Daily Monthly Report'), 'pdfDailyLate' => TTi18n::gettext('Daily Attendance / Late'), 'pdfMonthlyDetailAttendance' => TTi18n::gettext('Monthly Attendance Report'), 'pdfMonthlyDetailLate' => TTi18n::gettext('Monthly Late Report')) );
	
                
/*
		//Get employee list
		$filter_data['user_options'] = UserListFactory::getByCompanyIdArray( $current_company->getId(), FALSE );

		//Get column list
		$filter_data['column_options'] = $columns;

		$filter_data['pay_period_options'] = $pay_period_options;

		//Get primary/secondary order list
		$filter_data['sort_options'] = $columns;
		$filter_data['sort_direction_options'] = Misc::getSortDirectionArray();

		$filter_data['group_by_options'] = array(
												'0' => 'No Grouping',
												'title' => 'Title',
												'province' => 'Province',
												'country' => 'Country',
												'default_branch' => 'Default Branch',
												'default_department' => 'Default Department'
											);
*/
		$saved_report_options = $ugdlf->getByUserIdAndScriptArray( $current_user->getId(), $_SERVER['SCRIPT_NAME']);
		$generic_data['saved_report_options'] = $saved_report_options;
		$smarty->assign_by_ref('generic_data', $generic_data);

		$smarty->assign_by_ref('filter_data', $filter_data);

		$smarty->assign_by_ref('ugdf', $ugdf);

		$smarty->display('report/TimesheetDetail.tpl');

		break;
}
?>