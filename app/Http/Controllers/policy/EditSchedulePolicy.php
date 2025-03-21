<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: EditSchedulePolicy.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('schedule_policy','enabled')
		OR !( $permission->Check('schedule_policy','edit') OR $permission->Check('schedule_policy','edit_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect

}

$smarty->assign('title', __($title = 'Edit Schedule Policy')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'data'
												) ) );

if ( isset($data['start_stop_window'] ) ) {
	$data['start_stop_window'] = TTDate::parseTimeUnit($data['start_stop_window']);
}

$spf = new SchedulePolicyFactory();

$action = Misc::findSubmitButton();
$action = strtolower($action);
switch ($action) {
	case 'submit':
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$spf->setId( $data['id'] );
		$spf->setCompany( $current_company->getId() );
		$spf->setName( $data['name'] );
		$spf->setMealPolicyID( $data['meal_policy_id'] );
		$spf->setOverTimePolicyID( $data['over_time_policy_id'] );
		$spf->setAbsencePolicyID( $data['absence_policy_id'] );
		$spf->setStartStopWindow( $data['start_stop_window'] );

		if ( $spf->isValid() ) {
			$spf->Save(FALSE);

			if ( isset($data['break_policy_ids']) ) {
				$spf->setBreakPolicy( $data['break_policy_ids'] );
			} else {
				$spf->setBreakPolicy( array() );
			}

			Redirect::Page( URLBuilder::getURL( NULL, 'SchedulePolicyList.php') );

			break;
		}

	default:
		if ( isset($id) ) {
			BreadCrumb::setCrumb($title);

			$splf = new SchedulePolicyListFactory();
			$splf->getByIdAndCompanyID( $id, $current_company->getID() );

			foreach ($splf as $sp_obj) {
				//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);

				$data = array(
									'id' => $sp_obj->getId(),
									'name' => $sp_obj->getName(),
									'over_time_policy_id' => $sp_obj->getOverTimePolicyID(),
									'absence_policy_id' => $sp_obj->getAbsencePolicyID(),
									'meal_policy_id' => $sp_obj->getMealPolicyID(),
									'break_policy_ids' => $sp_obj->getBreakPolicy(),
									'start_stop_window' => $sp_obj->getStartStopWindow(),
									'created_date' => $sp_obj->getCreatedDate(),
									'created_by' => $sp_obj->getCreatedBy(),
									'updated_date' => $sp_obj->getUpdatedDate(),
									'updated_by' => $sp_obj->getUpdatedBy(),
									'deleted_date' => $sp_obj->getDeletedDate(),
									'deleted_by' => $sp_obj->getDeletedBy()
								);
			}
		} elseif ( $action != 'submit' ) {
			$data = array(
							'start_stop_window' => 3600
							);
		}

		$aplf = new AbsencePolicyListFactory();
		$absence_options = $aplf->getByCompanyIDArray( $current_company->getId(), TRUE );

		$otplf = new OverTimePolicyListFactory();
//		$over_time_options = $otplf->getByCompanyIDArray( $current_company->getId(), TRUE );
		$over_time_options = $otplf->getByCompanyIDArray( $current_company->getId(), TRUE, array('type_id' => '= 200') );

		$mplf = new MealPolicyListFactory();
		$meal_options = $mplf->getByCompanyIDArray( $current_company->getId(), TRUE );

		$bplf = new BreakPolicyListFactory();
		$break_options = $bplf->getByCompanyIdArray( $current_company->getId(), TRUE );

		//Select box options;
		$data['over_time_options'] = $over_time_options;
		$data['absence_options'] = $absence_options;
		$data['meal_options'] = $meal_options;
		$data['break_options'] = $break_options;

		$smarty->assign_by_ref('data', $data);

		break;
}

$smarty->assign_by_ref('spf', $spf);

$smarty->display('policy/EditSchedulePolicy.tpl');
?>