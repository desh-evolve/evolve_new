<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 5521 $
 * $Id: EditUserAbsence.php 5521 2011-11-15 23:50:13Z ipso $
 * $Date: 2011-11-15 15:50:13 -0800 (Tue, 15 Nov 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

//Debug::setVerbosity(11);
if ( !$permission->Check('absence','enabled')
		OR !( $permission->Check('absence','edit')
				OR $permission->Check('absence','edit_own')
				OR $permission->Check('absence','edit_child')
				 ) ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', __($title = 'Edit Absence')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'user_id',
												'date_stamp',
												'udt_data'
												) ) );

if ( isset($udt_data) ) {
	if ( $udt_data['total_time'] != '') {
		$udt_data['total_time'] = TTDate::parseTimeUnit( $udt_data['total_time'] ) ;
	}
}

$udtf = new UserDateTotalFactory();

$action = Misc::findSubmitButton();
$action = strtolower($action);
switch ($action) {
	case 'delete':
		Debug::Text('Delete!', __FILE__, __LINE__, __METHOD__,10);
		//Debug::setVerbosity(11);

		$udtlf = new UserDateTotalListFactory();
		$udtlf->getById( $udt_data['id'] );
		if ( $udtlf->getRecordCount() > 0 ) {
			foreach($udtlf as $udt_obj) {
				$udt_obj->setDeleted(TRUE);
				if ( $udt_obj->isValid() ) {
					$udt_obj->setEnableCalcSystemTotalTime( TRUE );
					$udt_obj->setEnableCalcWeeklySystemTotalTime( TRUE );
					$udt_obj->setEnableCalcException( TRUE );
					$udt_obj->Save();
				}
			}
		}

		Redirect::Page( URLBuilder::getURL( array('refresh' => TRUE ), '../CloseWindow.php') );

		break;
	case 'submit':
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);
		//Debug::setVerbosity(11);

		//Limit it to 31 days.
		if ( $udt_data['repeat'] > 31 ) {
			$udt_data['repeat'] = 31;
		}
		Debug::Text('Repeating Punch For: '. $udt_data['repeat'] .' Days', __FILE__, __LINE__, __METHOD__,10);

		$udtf->StartTransaction();

		$fail_transaction = FALSE;
		for($i=0; $i <= (int)$udt_data['repeat']; $i++ ) {
			Debug::Text('Absence Repeat: '. $i, __FILE__, __LINE__, __METHOD__,10);

			if ( $i == 0 ) {
				$date_stamp = $udt_data['date_stamp'];
			} else {
				$date_stamp = $udt_data['date_stamp'] + (86400 * $i);
			}
			Debug::Text('Date Stamp: '. TTDate::getDate('DATE+TIME', $date_stamp), __FILE__, __LINE__, __METHOD__,10);

			if ( $i == 0 AND $udt_data['id'] != '' ) {
				//Because if a user modifies the type of absence, the accrual balances
				//may come out of sync. Instead of just editing the entry directly, lets
				//delete the old one, and insert it as new.
				if ( $udt_data['absence_policy_id'] == $udt_data['old_absence_policy_id'] ) {
					Debug::Text('Editing absence, absence policy DID NOT change', __FILE__, __LINE__, __METHOD__,10);
					$udtf->setId($udt_data['id']);
				} else {
					Debug::Text('Editing absence, absence policy changed, deleting old record ID: '. $udt_data['id'] , __FILE__, __LINE__, __METHOD__,10);
					$udtlf = new UserDateTotalListFactory();
					$udtlf->getById( $udt_data['id'] );
					if ( $udtlf->getRecordCount() == 1 ) {
						$udt_obj = $udtlf->getCurrent();
						$udt_obj->setDeleted(TRUE);
						if ( $udt_obj->isValid() ) {
							$udt_obj->Save();
						}
					}
					unset($udtlf, $udt_obj);
				}
			}

			$udtf->setUserDateId( UserDateFactory::findOrInsertUserDate($udt_data['user_id'], $date_stamp) );
			$udtf->setStatus( 30 ); //Absence
			$udtf->setType( 10 ); //Total
			$udtf->setAbsencePolicyID( $udt_data['absence_policy_id'] ); //Total
			if ( isset($udt_data['branch_id']) ) {
				$udtf->setBranch($udt_data['branch_id']);
			}
			if ( isset($udt_data['department_id']) ) {
				$udtf->setDepartment($udt_data['department_id']);
			}
			if ( isset($udt_data['job_id']) ) {
				$udtf->setJob($udt_data['job_id']);
			}
			if ( isset($udt_data['job_item_id']) ) {
				$udtf->setJobItem($udt_data['job_item_id']);
			}

			$udtf->setTotalTime($udt_data['total_time']);
			if ( isset($udt_data['override']) ) {
				$udtf->setOverride(TRUE);
			} else {
				$udtf->setOverride(FALSE);
			}

			if ( $udtf->isValid() ) {
				//FIXME: In some cases TimeSheet Not Verified exceptions are enabled, and an employee has no time on their timesheet
				//and absences are entered, we need to recalculate exceptions on the last day of the pay period to trigger the V1 exception.
				$udtf->setEnableCalcSystemTotalTime(TRUE);
				$udtf->setEnableCalcWeeklySystemTotalTime( TRUE );
				$udtf->setEnableCalcException( TRUE );

				if ( $udtf->Save() != TRUE ) {
					$fail_transaction = TRUE;
					break;
				}
			} else {
				$fail_transaction = TRUE;
				break;
			}
		}

		if ( $fail_transaction == FALSE ) {
			//$udtf->FailTransaction();
			$udtf->CommitTransaction();

			Redirect::Page( URLBuilder::getURL( array('refresh' => TRUE ), '../CloseWindow.php') );
			break;
		} else {
			$udtf->FailTransaction();
		}

	default:
		/*

		Don't allow editing System time. If they want to force a bank time
		they can just add that to the accrual, and either set a time pair to 0
		or enter a Absense Dock (only for salary) employees.

		However when you do a Absense dock, what hours is it docking from,
		total, regular,overtime?

		*/
		if ( $id != '' ) {
			Debug::Text(' ID was passed: '. $id, __FILE__, __LINE__, __METHOD__,10);

			$udtlf = new UserDateTotalListFactory();
			$udtlf->getById( $id );

			foreach ($udtlf as $udt_obj) {
				//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);
				$user_id = $udt_obj->getUserDateObject()->getUser();
				$udt_data = array(
									'id' => $udt_obj->getId(),
									'user_date_id' => $udt_obj->getUserDateId(),
									'date_stamp' => $udt_obj->getUserDateObject()->getDateStamp(),
									'user_id' => $udt_obj->getUserDateObject()->getUser(),
									'user_full_name' => $udt_obj->getUserDateObject()->getUserObject()->getFullName(),
									'status_id' => $udt_obj->getStatus(),
									'type_id' => $udt_obj->getType(),
									'total_time' => $udt_obj->getTotalTime(),
									'absence_policy_id' => $udt_obj->getAbsencePolicyID(),
									'branch_id' => $udt_obj->getBranch(),
									'department_id' => $udt_obj->getDepartment(),
									'job_id' => $udt_obj->getJob(),
									'job_item_id' => $udt_obj->getJobItem(),
									'override' => $udt_obj->getOverride(),
									'created_date' => $udt_obj->getCreatedDate(),
									'created_by' => $udt_obj->getCreatedBy(),
									'updated_date' => $udt_obj->getUpdatedDate(),
									'updated_by' => $udt_obj->getUpdatedBy(),
									'deleted_date' => $udt_obj->getDeletedDate(),
									'deleted_by' => $udt_obj->getDeletedBy()
								);
			}
		} elseif ( $action != 'submit' ) {
			Debug::Text(' ID was NOT passed: '. $id, __FILE__, __LINE__, __METHOD__,10);

			//Get user full name
			$ulf = new UserListFactory();
			$user_obj = $ulf->getById( $user_id )->getCurrent();
			$user_date_id = UserDateFactory::getUserDateID($user_id, $date_stamp);

			$udt_data = array(
								'user_id' => $user_id,
								'date_stamp' => $date_stamp,
								'user_date_id' => $user_date_id,
								'user_full_name' => $user_obj->getFullName(),
								'branch_id' => $user_obj->getDefaultBranch(),
								'department_id' => $user_obj->getDefaultDepartment(),
								'total_time' => 0,
								'override' => TRUE
							);
		}

		$aplf = new AbsencePolicyListFactory();
		$absence_policy_options = Misc::prependArray( array( 0 => _('-- Please Choose --') ), $aplf->getByCompanyIdArray( $current_company->getId() ) );

		$blf = new BranchListFactory();
		$branch_options = $blf->getByCompanyIdArray( $current_company->getId() );

		$dlf = new DepartmentListFactory();
		$department_options = $dlf->getByCompanyIdArray( $current_company->getId() );

		if ( $current_company->getProductEdition() == 20 ) {
			$jlf = new JobListFactory();
			$jlf->getByCompanyIdAndUserIdAndStatus( $current_company->getId(), $user_id, array(10,20,30,40) );
			$udt_data['job_options'] = $jlf->getArrayByListFactory( $jlf, TRUE, TRUE );
			$udt_data['job_manual_id_options'] = $jlf->getManualIDArrayByListFactory($jlf, TRUE);

			$jilf = new JobItemListFactory();
			$jilf->getByCompanyId( $current_company->getId() );
			$udt_data['job_item_options'] = $jilf->getArrayByListFactory( $jilf, TRUE, TRUE );
			$udt_data['job_item_manual_id_options'] = $jilf->getManualIdArrayByListFactory( $jilf, TRUE );
		}

		//Select box options;
		//$udt_data['status_options'] = $udtf->getOptions('status');
		//$udt_data['type_options'] = $udtf->getOptions('type');
		$udt_data['absence_policy_options'] = $absence_policy_options;
		$udt_data['branch_options'] = $branch_options;
		$udt_data['department_options'] = $department_options;

		$smarty->assign_by_ref('udt_data', $udt_data);

		break;
}

$smarty->assign_by_ref('udtf', $udtf);

$smarty->display('punch/EditUserAbsence.tpl');
?>