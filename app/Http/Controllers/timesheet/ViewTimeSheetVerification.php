<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4351 $
 * $Id: ViewTimeSheetVerification.php 4351 2011-03-09 20:08:13Z ipso $
 * $Date: 2011-03-09 12:08:13 -0800 (Wed, 09 Mar 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

//Debug::setVerbosity(11);

if ( !$permission->Check('punch','enabled')
		OR !( $permission->Check('punch','edit')
				OR $permission->Check('punch','edit_own')
				OR $permission->Check('punch','edit_child')) ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'View TimeSheet Verification')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'timesheet_id',
												'timesheet_queue_ids',
												'selected_level'
												) ) );

if ( isset($timesheet_queue_ids) ) {
	$timesheet_queue_ids = unserialize( base64_decode( urldecode($timesheet_queue_ids) ) );
	Debug::Arr($timesheet_queue_ids, ' Input TimeSheet Queue IDs '. $action, __FILE__, __LINE__, __METHOD__,10);
}
if ( isset($data) ) {
	$data['date_stamp'] = TTDate::parseDateTime($data['date_stamp']);
}

$pptsvf = new PayPeriodTimeSheetVerifyFactory();

$action = Misc::findSubmitButton();
switch ($action) {
	case 'pass':
		if ( count($timesheet_queue_ids) > 1 ) {
			//Remove the authorized/declined timesheet from the stack.
			array_shift($timesheet_queue_ids);
			Redirect::Page( URLBuilder::getURL( array('id' => $timesheet_queue_ids[0], 'selected_level' => $selected_level, 'timesheet_queue_ids' => base64_encode( serialize($timesheet_queue_ids) ) ), 'ViewTimeSheetVerification.php') );
		} else {
			Redirect::Page( URLBuilder::getURL( array('refresh' => TRUE ), '../CloseWindow.php') );
		}
	case 'decline':
	case 'authorize':
		//Debug::setVerbosity(11);
		Debug::text(' Authorizing TimeSheet: Action: '. $action, __FILE__, __LINE__, __METHOD__,10);
		if ( !empty($timesheet_id) ) {
			Debug::text(' Authorizing TimeSheet ID: '. $timesheet_id, __FILE__, __LINE__, __METHOD__,10);

			$af = new AuthorizationFactory();
			$af->setObjectType('timesheet');
			$af->setObject( $timesheet_id );

			if ( $action == 'authorize' ) {
				Debug::text(' Approving Authorization: ', __FILE__, __LINE__, __METHOD__,10);
				$af->setAuthorized(TRUE);
			} else {
				Debug::text(' Declining Authorization: ', __FILE__, __LINE__, __METHOD__,10);
				$af->setAuthorized(FALSE);
			}

			if ( $af->isValid() ) {
				$af->Save();

				if ( count($timesheet_queue_ids) > 1 ) {
					//Remove the authorized/declined timesheet from the stack.
					array_shift($timesheet_queue_ids);
					Redirect::Page( URLBuilder::getURL( array('id' => $timesheet_queue_ids[0], 'selected_level' => $selected_level, 'timesheet_queue_ids' => base64_encode( serialize($timesheet_queue_ids) ) ), 'ViewTimeSheetVerification.php') );
				} else {
					Redirect::Page( URLBuilder::getURL( array('refresh' => TRUE ), '../CloseWindow.php') );
				}

				break;
			}
		}
	default:
		if ( (int)$id > 0 ) {
			Debug::Text(' ID was passed: '. $id, __FILE__, __LINE__, __METHOD__,10);

			$pptsvlf = new PayPeriodTimeSheetVerifyListFactory();
			$pptsvlf->getByIDAndCompanyID( $id, $current_company->getId() );

			$status_options = $pptsvlf->getOptions('type');
			foreach ($pptsvlf as $pptsv_obj) {
				//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);

				$data = array(
					'id' => $pptsv_obj->getId(),
					'pay_period_id' => $pptsv_obj->getPayPeriod(),
					'user_id' => $pptsv_obj->getUser(),
					'user_full_name' => $pptsv_obj->getUserObject()->getFullName(),
					'pay_period_start_date' => $pptsv_obj->getPayPeriodObject()->getStartDate(),
					'pay_period_end_date' => $pptsv_obj->getPayPeriodObject()->getEndDate(),
					'status_id' => $pptsv_obj->getStatus(),
					'status' => $status_options[$pptsv_obj->getStatus()],
					'created_date' => $pptsv_obj->getCreatedDate(),
					'created_by' => $pptsv_obj->getCreatedBy(),
					'updated_date' => $pptsv_obj->getUpdatedDate(),
					'updated_by' => $pptsv_obj->getUpdatedBy(),
					'deleted_date' => $pptsv_obj->getDeletedDate(),
					'deleted_by' => $pptsv_obj->getDeletedBy()
				);
			}

			//Get Next TimeSheet to authorize:
			if ( $permission->Check('punch','authorize')
					AND $selected_level != NULL
					AND count($timesheet_queue_ids) <= 1 ) {

				Debug::Text('Get TimeSheet Queue: ', __FILE__, __LINE__, __METHOD__,10);
				$hllf = new HierarchyLevelListFactory();
				$timesheet_levels = $hllf->getLevelsAndHierarchyControlIDsByUserIdAndObjectTypeID( $current_user->getId(), 90 );
				//Debug::Arr( $timesheet_levels, 'timesheet Levels', __FILE__, __LINE__, __METHOD__,10);

				if ( isset($selected_level) AND isset($timesheet_levels[$selected_level]) ) {
					$timesheet_selected_level = $timesheet_levels[$selected_level];
					Debug::Text(' Switching Levels to Level: '. key($timesheet_selected_level), __FILE__, __LINE__, __METHOD__,10);
				} elseif ( isset($timesheet_levels[1]) ) {
					$timesheet_selected_level = $timesheet_levels[1];
				} else {
					Debug::Text( 'No timesheet Levels... Not in hierarchy?', __FILE__, __LINE__, __METHOD__,10);
					$timesheet_selected_level = 0;
				}

				if ( is_array($timesheet_selected_level) ) {
					Debug::Text( 'Hierarchy information found...', __FILE__, __LINE__, __METHOD__,10);
					$pptsvlf = new PayPeriodTimeSheetVerifyListFactory();
					$pptsvlf->getByHierarchyLevelMapAndStatusAndNotAuthorized($timesheet_selected_level, 30 );

					//Get all IDs that need authorizing.
					//Only do 25 at a time, then grab more.
					$i=0;
					$start=FALSE;
					foreach( $pptsvlf as $pptsv_obj) {
						if ( $id == $pptsv_obj->getId() ) {
							$start = TRUE;
						}

						if ( $start == TRUE ) {
							$timesheet_queue_ids[] = $pptsv_obj->getId();
						}

						if ( $i > 25 ) {
							break;
						}
						$i++;
					}

					if ( isset($timesheet_queue_ids) ) {
						$timesheet_queue_ids = array_unique($timesheet_queue_ids);
					}
				} else {
					Debug::Text( 'No hierarchy information found...', __FILE__, __LINE__, __METHOD__,10);
				}
			}
		}

		//Select box options;
		$data['status_options'] = $pptsvf->getOptions('status');

		if ( isset($timesheet_queue_ids) ) {
			Debug::Arr($timesheet_queue_ids, ' Output TimeSheet Queue IDs '. $action, __FILE__, __LINE__, __METHOD__,10);
			$smarty->assign_by_ref('timesheet_queue_ids', urlencode( base64_encode( serialize($timesheet_queue_ids) ) ) );
		}

		$smarty->assign_by_ref('selected_level', $selected_level);
		$smarty->assign_by_ref('data', $data);

		break;
}

$smarty->assign_by_ref('pptsvf', $pptsvf);

$smarty->display('timesheet/ViewTimeSheetVerification.tpl');
?>