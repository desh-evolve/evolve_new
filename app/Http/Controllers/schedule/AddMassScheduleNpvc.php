<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: AddMassSchedule.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');

//Debug::setVerbosity(11);

$skip_message_check = TRUE;
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('schedule','enabled')
		OR !( $permission->Check('schedule','edit')
				OR $permission->Check('schedule','edit_own')
				OR $permission->Check('schedule','edit_child')
				 ) OR !$permission->Check('schedule','add_rosters')) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', __($title = 'Mass Schedule')); // See index.php
BreadCrumb::setCrumb($title);

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'data',
												'filter_user_id'
												) ) );

$data = Misc::preSetArrayValues( $data, array( 'start_date_stamp', 'end_date_stamp', 'start_time', 'end_time' ), NULL );

if ( isset($data) ) {
	if ( $data['start_date_stamp'] != ''
			AND !is_numeric($data['start_date_stamp'])
			AND $data['end_date_stamp'] != ''
			AND !is_numeric($data['end_date_stamp'])
			AND $data['start_time'] != ''
			AND !is_numeric($data['end_time'])
			AND $data['end_time'] != ''
			AND !is_numeric($data['end_time'])
			) {
		$data['start_full_time_stamp'] = TTDate::parseDateTime($data['start_date_stamp'].' '.$data['start_time']);
		$data['end_full_time_stamp'] = TTDate::parseDateTime($data['end_date_stamp'].' '.$data['end_time']);
	} else {
		$data['start_full_time_stamp'] = NULL;
		$data['end_full_time_stamp'] = NULL;
	}

	if ( $data['start_date_stamp'] != '') {
		$data['start_date_stamp'] = TTDate::parseDateTime($data['start_date_stamp']);
	}
	if ( $data['end_date_stamp'] != '') {
		$data['end_date_stamp'] = TTDate::parseDateTime($data['end_date_stamp']);
	}

	if ( $data['start_time'] != '') {
		$data['parsed_start_time'] = TTDate::strtotime( $data['start_time'], $data['start_date_stamp'] ) ;
	}
	if ( $data['end_time'] != '') {
		Debug::Text('End Time: '. $data['end_time'] .' Date Stamp: '. $data['start_date_stamp'] , __FILE__, __LINE__, __METHOD__,10);
		$data['parsed_end_time'] = strtotime( $data['end_time'], $data['start_date_stamp'] ) ;
		Debug::Text('bEnd Time: '. $data['end_time'] .' - '. TTDate::getDate('DATE+TIME',$data['end_time']) , __FILE__, __LINE__, __METHOD__,10);
	}
}

//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
$hlf = new HierarchyListFactory();
$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );
//Debug::Arr($permission_children_ids,'Permission Children Ids:', __FILE__, __LINE__, __METHOD__,10);
$filter_data = array();
if ( $permission->Check('schedule','edit') == FALSE ) {
	if ( $permission->Check('schedule','edit_child') ) {
		$filter_data['permission_children_ids'] = $permission_children_ids;
	}
	if ( $permission->Check('schedule','edit_own') ) {
		$filter_data['permission_children_ids'][] = $current_user->getId();
	}
}

$sf = new ScheduleFactory();
$ulf = new UserListFactory();

$action = Misc::findSubmitButton();
$action = strtolower($action);
switch ($action) {
	case 'submit':
		//Debug::setVerbosity(11);
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);
		$fail_transaction = FALSE;

//		if ( TTDate::getDayDifference( $data['start_date_stamp'], $data['end_date_stamp']) > 31 ) {
//			Debug::Text('Date Range Exceeds 31 days, truncating', __FILE__, __LINE__, __METHOD__,10);
//			$sf->Validator->isTrue('date_stamp', FALSE, _('Date range exceeds the maximum of 31 days') );
//		}
                //echo '<pre>'; print_r($data);         die;

                $shifts_array = array();
                if(isset($data['shifts'])){ 
                    foreach ($data['shifts'] as $shift_id =>$shift){ 
                        $data_shifts = array(); 
                        $data_shifts['status_id'] = $data['status_id'];
                        if($shift_id == '0'){ //Shift A Time => 6:00 - 14:00 
                            $data_shifts['start_time'] = '06:00';
                            $data_shifts['end_time'] = '14:00'; 
                        }
                        if($shift_id == '1'){ //Shift B Time => 14:00 - 21:00 
                                $data_shifts['start_time'] = '14:00';
                                $data_shifts['end_time'] = '21:00';
                        }
                        if($shift_id == '2'){//Shift C Time => 21:00 - 06:00 
                                $data_shifts['start_time'] = '21:00';
                                $data_shifts['end_time'] = '06:00';
                        }
        
                                
                        $data_shifts['start_date_stamp'] = TTDate::parseDateTime($data['start_date'][$shift_id]);
                        $data_shifts['end_date_stamp'] = TTDate::parseDateTime($data['end_date'][$shift_id]);
                        $data_shifts['dow'] = array(0=>1, 1=>1, 2=>1, 3=>1, 4=>1, 5=>1, 6=>1);
                        
                        $data_shifts['schedule_policy_id'] = $data['schedule_policy_id'];
                        $data_shifts['absence_policy_id'] = $data['absence_policy_id'];
                        $data_shifts['branch_id'] = $data['branch_id'];
                        $data_shifts['department_id'] = $data['department_id'];
                        $data_shifts['overwrite'] = $data['overwrite'];
                        

                        $date_dif_days = ($data_shifts['end_date_stamp'] - $data_shifts['start_date_stamp'])/(60*60*24);
                        $rolling_length = $data['days_rec_'.$shift_id] + $data['days_gap_'.$shift_id];

                        $date_unit = round($date_dif_days/$rolling_length);
						// echo '<pre>'; print_r($date_unit);         die;

						// echo (date('Y-m-d',$data_shifts['end_date_stamp'])); die;
                        $day_count = 0;
                        $roll_date = $data_shifts['start_date_stamp'];
                        
                        while ($roll_date <= TTDate::parseDateTime($data['end_date'][$shift_id])){
                            
                            for($i=0; $i<$rolling_length; $i++){
                                
                                
                                if($i < $data['days_rec_'.$shift_id] && $roll_date <= TTDate::parseDateTime($data['end_date'][$shift_id])){
									// echo '---'.date('Y-m-d',$roll_date);
                                    
                                    $data_shifts['start_date_stamp'] = $roll_date;
                                    $data_shifts['end_date_stamp'] = $roll_date;
                                    
                                    $data_shifts['start_full_time_stamp'] = TTDate::parseDateTime($data['start_date'][$shift_id].' '.$data_shifts['start_time']);
                                    $data_shifts['end_full_time_stamp'] = TTDate::parseDateTime($data['end_date'][$shift_id].' '.$data_shifts['end_time']);
                                    
                                    $data_shifts['parsed_start_time'] = TTDate::strtotime( $data_shifts['start_time'], $data_shifts['start_date_stamp'] ) ;
                                    $data_shifts['parsed_end_time'] = TTDate::strtotime( $data_shifts['end_time'], $data_shifts['start_date_stamp'] ) ;
                                                      
                                    $shifts_array[] = $data_shifts;


                                }
                                $roll_date = strtotime('+1 day', $roll_date); 
                            }
                            $day_count = $day_count + $i;
                        } 
						//echo ($data_shifts['end_date_stamp'] - $data_shifts['start_date_stamp'])/(60*60*24); die;

                              

                    }

                }
                //echo '<pre>';print_r($shifts_array);die;

                if ( !( isset($filter_user_id) AND is_array($filter_user_id) AND count($filter_user_id) > 0 )  ) {
                            $sf->Validator->isTrue('user_id', FALSE, _('Please select at least one employee') );
                    }
                    foreach($data_shifts['start_date'] as $date_key=>$start_date){
    //                    echo '----'.$start_date;
                        if ( !( $start_date != '' AND $data_shifts['end_date'][$date_key] != ''
                                    AND $start_date <= $data_shifts['end_date'][$date_key] ) ) {
                            $sf->Validator->isTrue('date_stamp', FALSE, _('Start or End dates are invalid') );
                        }
                    }

                    if ( $sf->Validator->isValid() ) {

    			Redirect::Page( URLBuilder::getURL( array('action' => 'add_mass_schedule_npvc', 'filter_user_id' => $filter_user_id, 'data' => $data ), '../progress_bar/ProgressBarControl.php') );
                    }

	default:
		if ( $action != 'submit' AND !is_array($data) ) {
			Debug::Text(' ID was NOT passed: '. $id, __FILE__, __LINE__, __METHOD__,10);

			$user_id = NULL;
			$user_date_id = NULL;
			$user_full_name = NULL;
			$user_default_branch = NULL;
			$user_default_department = NULL;
			$pay_period_is_locked = FALSE;

			$time_stamp = $start_date_stamp = $end_date_stamp = TTDate::getBeginDayEpoch( TTDate::getTime() ) + (3600*12); //Noon

			$data = array(
								//'user_id' => $user_id,
								'start_date_stamp' => $start_date_stamp,
								'end_date_stamp' => $end_date_stamp,
								//'user_date_id' => $user_date_id,
								//'user_full_name' => $user_full_name,
								'start_time' => strtotime('08:00 AM'),
								'parsed_start_time' => strtotime('08:00 AM'),
								'end_time' => strtotime('05:00 PM'),
								'parsed_end_time' => strtotime('05:00 PM'),
								'total_time' => 3600*9,
								'branch_id' => $user_default_branch,
								'department_id' => $user_default_department,
								//'pay_period_is_locked' => $pay_period_is_locked
								'dow' => array(1 => TRUE, 2 => TRUE, 3 => TRUE, 4 => TRUE, 5 => TRUE)

							);
		}
		//var_dump($data);

		$ulf = new UserListFactory();
		$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
		$src_user_options = UserListFactory::getArrayByListFactory( $ulf, FALSE, FALSE );

		$user_options = Misc::arrayDiffByKey( (array)$filter_user_id, $src_user_options );
		$filter_user_options = Misc::arrayIntersectByKey( (array)$filter_user_id, $src_user_options );

		$prepend_array_option = array( 0 => '--', -1 => _('-- Default --') );

		$splf = new SchedulePolicyListFactory();
		$schedule_policy_options = $splf->getByCompanyIdArray( $current_company->getId() );

		$aplf = new AbsencePolicyListFactory();
		$absence_policy_options = $aplf->getByCompanyIdArray( $current_company->getId() );

		$blf = new BranchListFactory();
		$blf->getByCompanyId( $current_company->getId() );
		$branch_options = Misc::prependArray( $prepend_array_option,  $blf->getArrayByListFactory( $blf, FALSE, TRUE ) );

		$dlf = new DepartmentListFactory();
		$dlf->getByCompanyId( $current_company->getId() );
		$department_options = Misc::prependArray( $prepend_array_option,  $dlf->getArrayByListFactory( $dlf, FALSE, TRUE ) );

		if ( $current_company->getProductEdition() == 20 ) {
			$jlf = new JobListFactory();
			$jlf->getByStatusIdAndCompanyId( array(10,20,30,40), $current_company->getId() );
			//$jlf->getByCompanyIdAndUserIdAndStatus( $current_company->getId(),  $data['user_id'], array(10,20,30,40) );
			$data['job_options'] = $jlf->getArrayByListFactory( $jlf, TRUE, TRUE );
			$data['job_manual_id_options'] = $jlf->getManualIDArrayByListFactory($jlf, TRUE);

			$jilf = new JobItemListFactory();
			$jilf->getByCompanyId( $current_company->getId() );
			$data['job_item_options'] = $jilf->getArrayByListFactory( $jilf, TRUE );
			$data['job_item_manual_id_options'] = $jilf->getManualIdArrayByListFactory( $jilf, TRUE );
		}

		//Select box options;
		$smarty->assign_by_ref('user_options', $user_options);
		$smarty->assign_by_ref('filter_user_options', $filter_user_options);

		$data['status_options'] = $sf->getOptions('status');
		$data['schedule_policy_options'] = $schedule_policy_options;
		$data['absence_policy_options'] = $absence_policy_options;

		//$data['type_options'] = $pf->getOptions('type');
		$data['branch_options'] = $branch_options;
		$data['department_options'] = $department_options;

		$smarty->assign_by_ref('data', $data);

		break;
}

$smarty->assign_by_ref('sf', $sf);

$smarty->display('schedule/AddMassScheduleNpvc.tpl');
?>