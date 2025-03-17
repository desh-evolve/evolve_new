<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: EditUserTitle.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('user','enabled')
		OR !( $permission->Check('user','edit') ) ) {

	$permission->Redirect( FALSE ); //Redirect

}

$smarty->assign('title',  TTi18n::gettext($title = 'Edit Employee Title')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'title_data'
												) ) );

$utf = new UserTitleFactory();

$action = Misc::findSubmitButton();
switch ($action) {
	case 'submit':
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$utf->setId($title_data['id']);
		$utf->setCompany( $current_company->getId() );
		$utf->setName($title_data['name']);
                $utf->SetClassificationId($title_data['cl_name_id']);  //FL ADDED 20160122 for epf return
		

		if ( $utf->isValid() ) {
			$utf->Save();

			Redirect::Page( URLBuilder::getURL(NULL, 'UserTitleList.php') );

			break;
		}
	default:
		if ( isset($id) ) {
			BreadCrumb::setCrumb($title);

			$utlf = new UserTitleListFactory();

			$utlf->GetByIdAndCompanyId($id, $current_company->getId() );

			foreach ($utlf as $title_obj) {
				//Debug::Arr($title_obj,'Title Object', __FILE__, __LINE__, __METHOD__,10);

				$title_data = array(
									'id' => $title_obj->getId(),
									'name' => $title_obj->getName(),
                                                                        'cl_name_id' => $title_obj->getClassificationId(), //fl added epf e return					
									'created_date' => $title_obj->getCreatedDate(),
									'created_by' => $title_obj->getCreatedBy(),
									'updated_date' => $title_obj->getUpdatedDate(),
									'updated_by' => $title_obj->getUpdatedBy(),
									'deleted_date' => $title_obj->getDeletedDate(),
									'deleted_by' => $title_obj->getDeletedBy()
								);
			}
		}

		$smarty->assign_by_ref('title_data', $title_data);

		break;
}

$smarty->assign_by_ref('utf', $utf);

$smarty->display('users/EditUserTitle.tpl');
?>