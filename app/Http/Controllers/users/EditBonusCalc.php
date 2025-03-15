<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('company','enabled')
		OR !( $permission->Check('company','view') OR $permission->Check('company','view_own') OR $permission->Check('company','view_child') ) ) {
	$permission->Redirect( FALSE ); //Redirect
}

//Debug::setVerbosity( 11 );

$smarty->assign('title', TTi18n::gettext($title = 'Bonus ')); // See index.php
BreadCrumb::setCrumb($title);

extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
                                                                                                'view',
												'data'
												) ) );


if ( isset($data) ) {
	if ( isset($data['start_date']) ) {
		$data['start_date'] = TTDate::parseDateTime( $data['start_date'] );
	}
	if ( isset($data['end_date']) ) {
		$data['end_date'] = TTDate::parseDateTime( $data['end_date'] );
                
        }
        
}

$bdf = TTnew( 'BonusDecemberFactory' );

$action = Misc::findSubmitButton();
$action = strtolower($action);

switch ($action) {
	case 'submit':
		//Debug::setVerbosity(11);
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$bdf->StartTransaction();
                
                if ( $data['id'] == '' ) {
			$bdf->setCompany( $current_company->getId() );
			
		} else {
			$bdf->setId($data['id']);
		}

		$bdf->setStartDate($data['start_date']);
		$bdf->setEndDate($data['end_date']+59);
                $bdf->setYNumber($data['y_number']);

		if ( $bdf->isValid() ) {
			$bdf->Save();

			$bdf->CommitTransaction();
			Redirect::Page( URLBuilder::getURL( NULL, 'BonusCalc.php') );
			break;
		}

		$bdf->FailTransaction();
                
        case 'generate_december_bonuses':
            
                Debug::Text('Generate Bonus!', __FILE__, __LINE__, __METHOD__,10);

		Redirect::Page( URLBuilder::getURL( array('action' => 'generate_december_bonuses', 'filter_user_id' => $data['id'], 'next_page' => URLBuilder::getURL( array('dec_bo_id' => $data['id'] ), '../users/BonusList.php') ), '../progress_bar/ProgressBarControl.php') );

            break;
	default:
		if ( isset($id) ) {
			BreadCrumb::setCrumb($title);

			$bdlf = TTnew( 'BonusDecemberListFactory' );
			$bdlf->getByIdAndCompanyId($id, $current_company->getId() );

			foreach ($bdlf as $bd_obj) {
				//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);

				$data = array(
													'id' => $bd_obj->getId(),
													'company_id' => $bd_obj->getCompany(),
													'start_date' => $bd_obj->getStartDate(),
													'end_date' => $bd_obj->getEndDate(),
													'y_number' => $bd_obj->getYNumber(),
													'deleted' => $bd_obj->getDeleted(),
													'created_date' => $bd_obj->getCreatedDate(),
													'created_by' => $bd_obj->getCreatedBy(),
													'updated_date' => $bd_obj->getUpdatedDate(),
													'updated_by' => $bd_obj->getUpdatedBy(),
													'deleted_date' => $bd_obj->getDeletedDate(),
													'deleted_by' => $bd_obj->getDeletedBy()
												);
			}
		}  

		$smarty->assign_by_ref('data', $data);

		break;
}



$smarty->assign_by_ref('bdf', $bdf);
$smarty->assign_by_ref('view', $view);


$smarty->display('users/EditBonusCalc.tpl');