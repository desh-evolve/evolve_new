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
    '-1000-pay_period' => 'Pay Periods',
    '-1010-date_stamp' => TTi18n::gettext('Date'),
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

		if ( isset($filter_data['print_timesheet']) AND $filter_data['print_timesheet'] >= 1 ) { echo 'madara';
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
                                $temp_pay_period_id = $pay_period_id;
                               // echo $temp_pay_period_id.'<br>';
                              
                                $date_stamp = TTDate::strtotime( $udt_obj->getColumn('date_stamp') );
                                $tmp_rows[$user_id][$pay_period_id][$date_stamp]['pay_period']=$udt_obj->getColumn('pay_period_id');
                              /*  
                                if(isset($prev_records[$user_id])){
                                    $pay_period_id = $prev_records[$user_id];
                                }else{
                                    $prev_records[$user_id] = $pay_period_id;
                                }
                              */
                               
                              // echo $pay_period_id.'<br>';
				
                                
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
					if ( isset($tmp_rows[$user_id][$pay_period_id][$date_stamp][$column]) ) {
						$tmp_rows[$user_id][$pay_period_id][$date_stamp][$column] += (int)$udt_obj->getColumn('total_time');
					} else {
						$tmp_rows[$user_id][$pay_period_id][$date_stamp][$column] = (int)$udt_obj->getColumn('total_time');
					}
					if ( isset($tmp_rows[$user_id][$pay_period_id][$date_stamp]['actual_time']) ) {
						$tmp_rows[$user_id][$pay_period_id][$date_stamp]['actual_time'] += $udt_obj->getColumn('actual_total_time');
					} else {
						$tmp_rows[$user_id][$pay_period_id][$date_stamp]['actual_time'] = $udt_obj->getColumn('actual_total_time');
					}

					$actual_time_diff = bcsub($udt_obj->getColumn('actual_total_time'), $udt_obj->getColumn('total_time') );
					if ( isset($tmp_rows[$user_id][$pay_period_id][$date_stamp]['actual_time_diff']) ) {
						$tmp_rows[$user_id][$pay_period_id][$date_stamp]['actual_time_diff'] += $actual_time_diff;
					} else {
						$tmp_rows[$user_id][$pay_period_id][$date_stamp]['actual_time_diff'] = $actual_time_diff;
					}

					if ( isset($user_wage[$user_id]) ) {
						$tmp_rows[$user_id][$pay_period_id][$date_stamp]['actual_time_diff_wage'] = Misc::MoneyFormat( bcmul( TTDate::getHours($actual_time_diff), $user_wage[$user_id]), FALSE );
					} else {
						$tmp_rows[$user_id][$pay_period_id][$date_stamp]['actual_time_diff_wage'] = Misc::MoneyFormat( 0, FALSE );
					}
					unset($actual_time_diff);
				} elseif ( $column != NULL ) {
					if ( $udt_obj->getColumn('total_time') > 0 ) {

						//Total up all absence time.
						if ($status_id == 30 AND $type_id == 10) {
							if ( isset($tmp_rows[$user_id][$pay_period_id][$date_stamp]['absence_time']) ) {
								$tmp_rows[$user_id][$pay_period_id][$date_stamp]['absence_time'] += $udt_obj->getColumn('total_time');
							} else {
								$tmp_rows[$user_id][$pay_period_id][$date_stamp]['absence_time'] = $udt_obj->getColumn('total_time');
							}
						}

						if ($status_id == 10 AND $type_id == 30) {
							if ( isset($tmp_rows[$user_id][$pay_period_id][$date_stamp]['over_time']) ) {
								$tmp_rows[$user_id][$pay_period_id][$date_stamp]['over_time'] += $udt_obj->getColumn('total_time');
                                                                $tmp_rows[$pay_period_id][$user_id][$date_stamp]['over_time_policy_id'] = $policy_id;
							} else {
								$tmp_rows[$user_id][$pay_period_id][$date_stamp]['over_time'] = $udt_obj->getColumn('total_time');
                                                                $tmp_rows[$pay_period_id][$user_id][$date_stamp]['over_time_policy_id'] = $policy_id;
							}
						}

						if ( isset($tmp_rows[$user_id][$pay_period_id][$date_stamp][$column]) ) {
							$tmp_rows[$user_id][$pay_period_id][$date_stamp][$column] += $udt_obj->getColumn('total_time');
						} else {
							$tmp_rows[$user_id][$pay_period_id][$date_stamp][$column] = $udt_obj->getColumn('total_time');
						}

						//This messes with the ArraySum'ing, so only include it when we're generating a PDF timesheet.
						if ( $action == 'display_timesheet' OR $action == 'display_detailed_timesheet' ) {
							if ( isset($tmp_rows[$user_id][$pay_period_id][$date_stamp]['categorized_time'][$category][$policy_id]) ) {
								$tmp_rows[$user_id][$pay_period_id][$date_stamp]['categorized_time'][$category][$policy_id] += $udt_obj->getColumn('total_time');
							} else {
								$tmp_rows[$user_id][$pay_period_id][$date_stamp]['categorized_time'][$category][$policy_id] = $udt_obj->getColumn('total_time');
							}
						}
					}
				}
                                
//                                echo '<pre>'; print_r($schedule_rows[$pay_period_id][$user_id][$date_stamp]['start_time']); echo '<pre>'; die;
        
				if ( isset($schedule_rows[$pay_period_id][$user_id][$date_stamp]['working']) ) {
					$tmp_rows[$user_id][$pay_period_id][$date_stamp]['schedule_working'] = $schedule_rows[$pay_period_id][$user_id][$date_stamp]['working'];
				} else {
					$tmp_rows[$user_id][$pay_period_id][$date_stamp]['schedule_working'] = NULL;
				}

				if ( isset($schedule_rows[$pay_period_id][$user_id][$date_stamp]['absence']) ) {
					$tmp_rows[$user_id][$pay_period_id][$date_stamp]['schedule_absence'] = $schedule_rows[$pay_period_id][$user_id][$date_stamp]['absence'];
				} else {
					$tmp_rows[$user_id][$pay_period_id][$date_stamp]['schedule_absence'] = NULL;
				}

				if ( isset($schedule_rows[$pay_period_id][$user_id][$date_stamp]['start_time']) ) {
                                    
					$tmp_rows[$user_id][$pay_period_id][$date_stamp]['shedule_start_time'] = $schedule_rows[$pay_period_id][$user_id][$date_stamp]['start_time'];
				} else {
					$tmp_rows[$user_id][$pay_period_id][$date_stamp]['shedule_start_time'] = NULL;
				}

				if ( isset($schedule_rows[$pay_period_id][$user_id][$date_stamp]['end_time']) ) {
					$tmp_rows[$user_id][$pay_period_id][$date_stamp]['shedule_end_time'] = $schedule_rows[$pay_period_id][$user_id][$date_stamp]['end_time'];
				} else {
					$tmp_rows[$user_id][$pay_period_id][$date_stamp]['shedule_end_time'] = NULL;
				}

				$tmp_rows[$user_id][$pay_period_id][$date_stamp]['min_punch_time_stamp'] = TTDate::strtotime( $udt_obj->getColumn('min_punch_time_stamp') );
				$tmp_rows[$user_id][$pay_period_id][$date_stamp]['max_punch_time_stamp'] = TTDate::strtotime( $udt_obj->getColumn('max_punch_time_stamp') );

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
                                
                                foreach($filter_data['pay_period_ids'] as $payperiod_id){
				$pptsvlf->getByPayPeriodIdAndCompanyId( $payperiod_id, $current_company->getId() );
				if ( $pptsvlf->getRecordCount() > 0 ) {
					foreach( $pptsvlf as $pptsv_obj ) {
						$verified_time_sheets[$pptsv_obj->getUser()][$pptsv_obj->getPayPeriod()] = array(
								 'status_id' => $pptsv_obj->getStatus(),
								 'created_date' => $pptsv_obj->getCreatedDate(),
							);
					}
				}
                                }
			}

                       
                        
			if ( isset($tmp_rows) ) {
				$i=0;
				foreach($tmp_rows as $user_id => $data_a ) {
                                            $user_obj = $ulf->getById( $user_id )->getCurrent();
                                    
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

                                   
                                   
					foreach($data_a as $pay_period_id => $data_b ) {
						
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
							/*foreach($sub_rows as $sub_row) {
								$tmp_sub_rows[] = $sub_row;
                                                                
							}*/
                                                    $tmp_sub_rows= $sub_rows;
                                                    
 
							$sub_rows = Sort::Multisort($tmp_sub_rows, Misc::trimSortPrefix($filter_data['primary_sort']), Misc::trimSortPrefix($filter_data['secondary_sort']), $filter_data['primary_sort_dir'], $filter_data['secondary_sort_dir']);
                                                        
                                                        unset($tmp_sub_rows);
                                                       
                                                        
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
                                                                                   
											$column_data = substr($column_data,11,5 ); 
                                                                                        
										}elseif ( $column == 'date_stamp' ) {
											$column_data = TTDate::getDate('DATE', $column_data);
										} elseif ( $column == 'min_punch_time_stamp' OR $column == 'max_punch_time_stamp' ) {
											$column_data = TTDate::getDate('TIME', $column_data);
										} elseif ( $column == 'over_time_policy_id') {
											$column_data = $column_data;
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
                                               
                                                
                                               
						
                                                $all_sub_rows[$pay_period_id] = $sub_rows;
                                                                                            
						unset($sub_rows);
					}// end of PAY PERIODS array
                                     
                                       //  echo '<pre>'; print_r($all_sub_rows); echo '<pre>'; die;
                                      
                                        $rows[$i]['data'] = $all_sub_rows;
					unset($sub_rows, $tmp_sub_rows);
					$i++;
                                                
                                        
				}
			}
                            
			unset($tmp_rows);
		}
		 if ($action == 'display_report'){
                        Debug::Text('Exporting as PDF', __FILE__, __LINE__, __METHOD__,10);
                                
                                $tsdr= new TimesheetDetailReport();//new code         
                               
                                $output = $tsdr->DepartmentAttendanceDetailed($rows, $filter_columns, $filter_data, $current_user, $current_company);//new code                               
                                                                                              
                                if ( Debug::getVerbosity() < 11 ) {                                    
                                    Misc::FileDownloadHeader('DepartmentAttendanceReport.pdf', 'application/pdf', strlen($output));
                                    echo $output;
                                    exit;                           
                                }  
                    
                    }else {
			$smarty->assign_by_ref('generated_time', TTDate::getTime() );
			$smarty->assign_by_ref('pay_period_options', $pay_period_options );
			$smarty->assign_by_ref('filter_data', $filter_data );
			$smarty->assign_by_ref('columns', $filter_columns );
			$smarty->assign_by_ref('rows', $rows);

			$smarty->display('report/DepartmentAttendanceReport.tpl');
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
                $hidden_elements = Misc::prependArray( array( 'displayReport' => '', 'displayTimeSheet' => 'hidden', 'displayDetailedTimeSheet' => 'hidden', 'export' => '') );
                
                $smarty->assign('hidden_elements',$hidden_elements); // See index.php
                
                //FL ADDED FOR EXPORT TYPE
                $filter_data['export_type_options'] = Misc::prependArray( array(  'pdfDepartmentDetailAttendance' => TTi18n::gettext('Department Attendance Report')) );
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