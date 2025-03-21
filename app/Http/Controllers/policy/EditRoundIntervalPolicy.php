<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: EditRoundIntervalPolicy.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('round_policy','enabled')
		OR !( $permission->Check('round_policy','edit') OR $permission->Check('round_policy','edit_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect

}

$smarty->assign('title', __($title = 'Edit Rounding Policy')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'data'
												) ) );

if ( isset($data['interval'] ) ) {
	$data['interval'] = TTDate::parseTimeUnit($data['interval']);
	$data['grace'] = TTDate::parseTimeUnit($data['grace']);
}


$ripf = new RoundIntervalPolicyFactory();

$action = Misc::findSubmitButton();
$action = strtolower($action);
switch ($action) {
	case 'submit':
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$ripf->setId( $data['id'] );
		$ripf->setCompany( $current_company->getId() );
		$ripf->setName( $data['name'] );
		$ripf->setPunchType( $data['punch_type_id'] );
		$ripf->setRoundType( $data['round_type_id'] );
		$ripf->setInterval( $data['interval'] );
		$ripf->setGrace( $data['grace'] );
		if ( isset($data['strict'] ) ) {
			$ripf->setStrict( TRUE );
		} else {
			$ripf->setStrict( FALSE );
		}

		if ( $ripf->isValid() ) {
			$ripf->Save();

			Redirect::Page( URLBuilder::getURL( NULL, 'RoundIntervalPolicyList.php') );

			break;
		}

	default:
		if ( isset($id) ) {
			BreadCrumb::setCrumb($title);

			$riplf = new RoundIntervalPolicyListFactory();
			$riplf->getByIdAndCompanyID( $id, $current_company->getID() );

			foreach ($riplf as $rip_obj) {
				//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);

				$data = array(
									'id' => $rip_obj->getId(),
									'name' => $rip_obj->getName(),
									'punch_type_id' => $rip_obj->getPunchType(),
									'round_type_id' => $rip_obj->getRoundType(),
									'interval' => $rip_obj->getInterval(),
									'grace' => $rip_obj->getGrace(),
									'strict' => $rip_obj->getStrict(),
									'created_date' => $rip_obj->getCreatedDate(),
									'created_by' => $rip_obj->getCreatedBy(),
									'updated_date' => $rip_obj->getUpdatedDate(),
									'updated_by' => $rip_obj->getUpdatedBy(),
									'deleted_date' => $rip_obj->getDeletedDate(),
									'deleted_by' => $rip_obj->getDeletedBy()
								);
			}
		} elseif ( $action != 'submit' ) {
			$data = array(
							'interval' => 900,
							'grace' => 0
							);
		}

		//Select box options;
		$data['punch_type_options'] = $ripf->getOptions('punch_type');
		$data['round_type_options'] = $ripf->getOptions('round_type');

		$smarty->assign_by_ref('data', $data);

		break;
}

$smarty->assign_by_ref('ripf', $ripf);

$smarty->display('policy/EditRoundIntervalPolicy.tpl');
?>