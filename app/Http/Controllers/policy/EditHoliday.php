<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: EditHoliday.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

//Debug::setVerbosity( 11 );

if ( !$permission->Check('holiday_policy','enabled')
		OR !( $permission->Check('holiday_policy','edit') OR $permission->Check('holiday_policy','edit_own') ) ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', __($title = 'Edit Holiday')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'holiday_policy_id',
												'id',
												'data'
												) ) );

if ( isset($data['date_stamp'] ) ) {
	$data['date_stamp'] = TTDate::parseDateTime($data['date_stamp']);
}

$hf = new HolidayFactory();

$action = Misc::findSubmitButton();
$action = strtolower($action);
switch ($action) {
	case 'submit':
		//Debug::setVerbosity(11);
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$hf->setId( $data['id'] );
		if ( isset($data['holiday_policy_id'] ) ) {
			$hf->setHolidayPolicyId( $data['holiday_policy_id'] );
		}
		//Set datestamp first.
		$hf->setDateStamp( $data['date_stamp'] );
		$hf->setName( $data['name'] );


		if ( $hf->isValid() ) {
			$hf->Save();

			Redirect::Page( URLBuilder::getURL( array('id' => $data['holiday_policy_id']), 'HolidayList.php') );

			break;
		}

	default:
		if ( isset($id) AND $id != '' ) {
			BreadCrumb::setCrumb($title);

			$hlf = new HolidayListFactory();
			$hlf->getByIdAndHolidayPolicyID( $id, $holiday_policy_id );
			if ( $hlf->getRecordCount() > 0 ) {
				foreach ($hlf as $h_obj) {
					//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);

					$data = array(
										'id' => $h_obj->getId(),
										'holiday_policy_id' => $h_obj->getHolidayPolicyID(),
										'date_stamp' => $h_obj->getDateStamp(),
										'name' => $h_obj->getName(),
										'created_date' => $h_obj->getCreatedDate(),
										'created_by' => $h_obj->getCreatedBy(),
										'updated_date' => $h_obj->getUpdatedDate(),
										'updated_by' => $h_obj->getUpdatedBy(),
										'deleted_date' => $h_obj->getDeletedDate(),
										'deleted_by' => $h_obj->getDeletedBy()
									);
				}
				$holiday_policy_id = $h_obj->getHolidayPolicyID();
			}
		} elseif ( $action != 'submit' ) {
			$data = array(
						'date_stamp' => TTDate::getTime(),
						'holiday_policy_id' => $holiday_policy_id
						);
		}

		$smarty->assign_by_ref('holiday_policy_id', $holiday_policy_id);
		$smarty->assign_by_ref('data', $data);

		break;
}

$smarty->assign_by_ref('hf', $hf);

$smarty->display('policy/EditHoliday.tpl');
?>