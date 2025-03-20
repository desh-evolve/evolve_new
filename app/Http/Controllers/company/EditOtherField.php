<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: EditOtherField.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('other_field','enabled')
		OR !( $permission->Check('other_field','edit') OR $permission->Check('other_field','edit_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'Edit Other Field')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'data'
												) ) );

$off = new OtherFieldFactory();

$action = Misc::findSubmitButton();
switch ($action) {
	case 'submit':
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$off->setId( $data['id'] );
		$off->setCompany( $current_company->getId() );
		$off->setType( $data['type_id'] );
		$off->setOtherID1( $data['other_id1'] );
		$off->setOtherID2( $data['other_id2'] );
		$off->setOtherID3( $data['other_id3'] );
		$off->setOtherID4( $data['other_id4'] );
		$off->setOtherID5( $data['other_id5'] );

		if ( $off->isValid() ) {
			$off->Save();

			Redirect::Page( URLBuilder::getURL( array('type_id' => $data['type_id']), 'OtherFieldList.php') );

			break;
		}
	default:
		if ( isset($id) ) {
			BreadCrumb::setCrumb($title);

			$oflf = new OtherFieldListFactory();

			//$uwlf->GetByUserIdAndCompanyId($current_user->getId(), $current_company->getId() );
			$oflf->getById($id);

			foreach ($oflf as $obj) {
				$data = array(
									'id' => $obj->getId(),
									'company_id' => $obj->getCompany(),
									'type_id' => $obj->getType(),
									'other_id1' => $obj->getOtherID1(),
									'other_id2' => $obj->getOtherID2(),
									'other_id3' => $obj->getOtherID3(),
									'other_id4' => $obj->getOtherID4(),
									'other_id5' => $obj->getOtherID5(),
									'created_date' => $obj->getCreatedDate(),
									'created_by' => $obj->getCreatedBy(),
									'updated_date' => $obj->getUpdatedDate(),
									'updated_by' => $obj->getUpdatedBy(),
									'deleted_date' => $obj->getDeletedDate(),
									'deleted_by' => $obj->getDeletedBy()
								);
			}
		}
		//Select box options;
		//$jif = new JobItemFactory();
		$data['type_options'] = $off->getOptions('type');

		$smarty->assign_by_ref('data', $data);

		break;
}

$smarty->assign_by_ref('off', $off);

$smarty->display('company/EditOtherField.tpl');
?>