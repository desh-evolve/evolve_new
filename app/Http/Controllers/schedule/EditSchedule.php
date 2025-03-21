<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4555 $
 * $Id: EditSchedule.php 4555 2011-04-20 22:52:00Z ipso $
 * $Date: 2011-04-20 15:52:00 -0700 (Wed, 20 Apr 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

//Debug::setVerbosity(11);

if ( !$permission->Check('schedule','enabled')
		OR !( $permission->Check('schedule','edit')
				OR $permission->Check('schedule','edit_own') OR $permission->Check('schedule','edit_child') ) ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', __($title = 'Edit Schedule')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'user_id',
												'date_stamp',
												'status_id',
												'start_time',
												'end_time',
												'schedule_policy_id',
												'absence_policy_id',
												'data'
												) ) );

if ( isset($data) ) {
	if ( $data['date_stamp'] != '') {
		$data['date_stamp'] = TTDate::parseDateTime( $data['date_stamp'] ) ;
	}
	if ( $data['start_time'] != '') {
		$data['parsed_start_time'] = strtotime( $data['start_time'], $data['date_stamp'] ) ;
	}
	if ( $data['end_time'] != '') {
		Debug::Text('End Time: '. $data['end_time'] .' Date Stamp: '. $data['date_stamp'] , __FILE__, __LINE__, __METHOD__,10);
		$data['parsed_end_time'] = strtotime( $data['end_time'], $data['date_stamp'] ) ;
		Debug::Text('bEnd Time: '. $data['end_time'] .' - '. TTDate::getDate('DATE+TIME',$data['end_time']) , __FILE__, __LINE__, __METHOD__,10);
	}
}


$filter_data = array();
$hlf = new HierarchyListFactory();
$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );
if ( $permission->Check('schedule','edit') == FALSE ) {
	if ( $permission->Check('schedule','edit_child') == FALSE ) {
		$permission_children_ids = array();
	}
	if ( $permission->Check('schedule','edit_own') ) {
		$permission_children_ids[] = $current_user->getId();
	}

	$filter_data['permission_children_ids'] = $permission_children_ids;
}

$sf = new ScheduleFactory();

$action = Misc::findSubmitButton();
switch ($action) {
	case 'delete':
		Debug::Text('Delete!', __FILE__, __LINE__, __METHOD__,10);

		$slf = new ScheduleListFactory();
		$slf->getById( $data['id'] );
		if ( $slf->getRecordCount() > 0 ) {
			foreach($slf as $s_obj) {
				$s_obj->setDeleted(TRUE);
				if ( $s_obj->isValid() ) {
					$s_obj->setEnableReCalculateDay(TRUE); //Need to remove absence time when deleting a schedule.
					$s_obj->Save();
				}
			}
		}

		Redirect::Page( URLBuilder::getURL( array('refresh' => TRUE ), '../CloseWindow.php') );

		break;

	case 'submit':
		//Debug::setVerbosity(11);
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$fail_transaction = FALSE;

		$sf->StartTransaction();

		//Limit it to 31 days.
		if ( $data['repeat'] > 31 ) {
			$data['repeat'] = 31;
		}
		Debug::Text('Repeating Punch For: '. $data['repeat'] .' Days', __FILE__, __LINE__, __METHOD__,10);

		for($i=0; $i <= (int)$data['repeat']; $i++ ) {
			Debug::Text('Punch Repeat: '. $i, __FILE__, __LINE__, __METHOD__,10);
			if ( $i == 0 ) {
				$date_stamp = $data['date_stamp'];
			} else {
				$date_stamp = $data['date_stamp'] + (86400 * $i);
			}

			Debug::Text('Date Stamp: '. TTDate::getDate('DATE', $date_stamp), __FILE__, __LINE__, __METHOD__,10);


			$sf = new ScheduleFactory();

			if ( $i == 0 ) {
				$sf->setID( $data['id'] );
			}
			//$sf->setUserDateId( UserDateFactory::findOrInsertUserDate($data['user_id'], $date_stamp) );
			$sf->setUserDate($data['user_id'], $date_stamp);
			$sf->setStatus( $data['status_id'] );
			$sf->setSchedulePolicyID( $data['schedule_policy_id'] );
			$sf->setAbsencePolicyID( $data['absence_policy_id'] );
			$sf->setBranch( $data['branch_id'] );
			$sf->setDepartment( $data['department_id'] );

			if ( isset($data['job_id']) ) {
				$sf->setJob( $data['job_id'] );
			}

			if ( isset($data['job_item_id'] ) ) {
				$sf->setJobItem( $data['job_item_id'] );
			}

			if ( $data['start_time'] != '') {
				$start_time = strtotime( $data['start_time'], $date_stamp ) ;
			} else {
				$start_time = NULL;
			}
			if ( $data['end_time'] != '') {
				Debug::Text('End Time: '. $data['end_time'] .' Date Stamp: '. $date_stamp , __FILE__, __LINE__, __METHOD__,10);
				$end_time = strtotime( $data['end_time'], $date_stamp ) ;

				Debug::Text('bEnd Time: '. $data['end_time'] .' - '. TTDate::getDate('DATE+TIME',$data['end_time']) , __FILE__, __LINE__, __METHOD__,10);

			} else {
				$end_time = NULL;
			}

			$sf->setStartTime( $start_time );
			$sf->setEndTime( $end_time );

			if ( $sf->isValid() ) {
				$sf->setEnableReCalculateDay(TRUE);
				if ( $sf->Save() != TRUE ) {
					$fail_transaction = TRUE;
					break;
				}
			} else {
				$fail_transaction = TRUE;
			}
		}

		if ( $fail_transaction == FALSE ) {
			//$sf->FailTransaction();
			$sf->CommitTransaction();

			Redirect::Page( URLBuilder::getURL( array('refresh' => TRUE ), '../CloseWindow.php') );
			break;
		} else {
			$sf->FailTransaction();
		}

	default:
		if ( $id != '' ) {
			Debug::Text(' ID was passed: '. $id, __FILE__, __LINE__, __METHOD__,10);

			$slf = new ScheduleListFactory();
			$slf->getById( $id );
			foreach ($slf as $s_obj) {
				//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);

				$data = array(
									'id' => $s_obj->getId(),
									'user_date_id' => $s_obj->getUserDateId(),
									'user_id' => $s_obj->getUserDateObject()->getUser(),
									'user_full_name' => $s_obj->getUserDateObject()->getUserObject()->getFullName(),
									'date_stamp' => $s_obj->getUserDateObject()->getDateStamp(),
									'status_id' => $s_obj->getStatus(),
									'start_time' => $s_obj->getStartTime(),
									'parsed_start_time' => $s_obj->getStartTime(),
									'end_time' => $s_obj->getEndTime(),
									'parsed_end_time' => $s_obj->getEndTime(),
									'total_time' => $s_obj->getTotalTime(),
									'schedule_policy_id' => $s_obj->getSchedulePolicyID(),
									'absence_policy_id' => $s_obj->getAbsencePolicyID(),
									'branch_id' => $s_obj->getBranch(),
									'department_id' => $s_obj->getDepartment(),
									'job_id' => $s_obj->getJob(),
									'job_item_id' => $s_obj->getJobItem(),
									'pay_period_is_locked' => $s_obj->getUserDateObject()->getPayPeriodObject()->getIsLocked(),
									'created_date' => $s_obj->getCreatedDate(),
									'created_by' => $s_obj->getCreatedBy(),
									'updated_date' => $s_obj->getUpdatedDate(),
									'updated_by' => $s_obj->getUpdatedBy(),
									'deleted_date' => $s_obj->getDeletedDate(),
									'deleted_by' => $s_obj->getDeletedBy(),
									'is_owner' => $permission->isOwner( $s_obj->getUserDateObject()->getUserObject()->getCreatedBy(), $s_obj->getUserDateObject()->getUserObject()->getId() ),
									'is_child' => $permission->isChild( $s_obj->getUserDateObject()->getUserObject()->getId(), $permission_children_ids ),
								);
			}
		} elseif ( $action != 'submit' ) {
			Debug::Text(' ID was NOT passed: '. $id, __FILE__, __LINE__, __METHOD__,10);

			//Get user full name
			if ( $user_id != '' ) {
				$ulf = new UserListFactory();
				$user_obj = $ulf->getById( $user_id )->getCurrent();
				$user_full_name = $user_obj->getFullName();
				$user_default_branch = $user_obj->getDefaultBranch();
				$user_default_department = $user_obj->getDefaultDepartment();

				$user_date_id = UserDateFactory::getUserDateID($user_id, $date_stamp);

				$pplf = new PayPeriodListFactory();
				$pplf->getByUserIdAndEndDate( $user_id, $date_stamp );
				if ( $pplf->getRecordCount() > 0 ) {
					$pay_period_is_locked = $pplf->getCurrent()->getIsLocked();
				} else {
					$pay_period_is_locked = FALSE;
				}

			} else {
				$user_id = NULL;
				$user_date_id = NULL;
				$user_full_name = NULL;
				$user_default_branch = NULL;
				$user_default_department = NULL;
				$pay_period_is_locked = FALSE;
			}

			if ( !is_numeric($start_time) ) {
				$start_time = strtotime('08:00 AM');
				$parsed_start_time = $start_time;
			}
			if ( !is_numeric($end_time) ) {
				$end_time = strtotime('05:00 PM');
				$parsed_end_time = $start_time;
			}

			$total_time = $end_time - $start_time;

			$data = array(
								'user_id' => $user_id,
								'status_id' => $status_id,
								'date_stamp' => $date_stamp,
								'user_date_id' => $user_date_id,
								'user_full_name' => $user_full_name,
								'start_time' => $start_time,
								'parsed_start_time' => $start_time,
								'end_time' => $end_time,
								'parsed_end_time' => $end_time,
								'total_time' => $total_time,
								'branch_id' => $user_default_branch,
								'department_id' => $user_default_department,
								'schedule_policy_id' => $schedule_policy_id,
								'absence_policy_id' => $absence_policy_id,
								'pay_period_is_locked' => $pay_period_is_locked
							);
		} else {
			//Get user full name.
			if ( $data['user_id'] != '' ) {
				$ulf = new UserListFactory();
				$user_obj = $ulf->getById( $data['user_id'] )->getCurrent();
				$user_full_name = $user_obj->getFullName();

				$data['user_id'] = $data['user_id'];
				$data['user_full_name'] = $user_full_name;
			}
		}

		$splf = new SchedulePolicyListFactory();
		$schedule_policy_options = $splf->getByCompanyIdArray( $current_company->getId() );

		$aplf = new AbsencePolicyListFactory();
		$absence_policy_options = $aplf->getByCompanyIdArray( $current_company->getId() );

		$blf = new BranchListFactory();
		$branch_options = $blf->getByCompanyIdArray( $current_company->getId() );

		$dlf = new DepartmentListFactory();
		$department_options = $dlf->getByCompanyIdArray( $current_company->getId() );

		if ( $current_company->getProductEdition() == 20 ) {
			$jlf = new JobListFactory();
			$jlf->getByCompanyIdAndUserIdAndStatus( $current_company->getId(),  $current_user->getId(), array(10) );
			$data['job_options'] = $jlf->getArrayByListFactory( $jlf, TRUE, TRUE );
			$data['job_manual_id_options'] = $jlf->getManualIDArrayByListFactory($jlf, TRUE);

			$jilf = new JobItemListFactory();
			$jilf->getByCompanyId( $current_company->getId() );
			$data['job_item_options'] = $jilf->getArrayByListFactory( $jilf, TRUE );
			$data['job_item_manual_id_options'] = $jilf->getManualIdArrayByListFactory( $jilf, TRUE );
		}

		$ulf = new UserListFactory();
		$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
		$data['user_options'] = UserListFactory::getArrayByListFactory( $ulf, FALSE, TRUE );

		//Select box options;
		$data['status_options'] = $sf->getOptions('status');
		$data['schedule_policy_options'] = $schedule_policy_options;
		$data['absence_policy_options'] = $absence_policy_options;
		$data['branch_options'] = $branch_options;
		$data['department_options'] = $department_options;

		$smarty->assign_by_ref('data', $data);
		$smarty->assign_by_ref('date_stamp', $date_stamp);

		break;
}

$smarty->assign_by_ref('sf', $sf);

$smarty->display('schedule/EditSchedule.tpl');
?>