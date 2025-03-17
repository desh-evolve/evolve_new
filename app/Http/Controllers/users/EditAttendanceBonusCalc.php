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



$abf = TTnew( 'AttendanceBonusFactory' );

$action = Misc::findSubmitButton();
$action = strtolower($action);


switch ($action) {
	case 'submit':
            
            $abf->StartTransaction();
            
            
                if ( $data['id'] == '' ) {
			$abf->setCompany( $current_company->getId() );
			
		} else {
			$abf->setId($data['id']);
		}
            
                $abf->setYear($data['year']);
                $abf->setBonusDecember($data['bonus_december_id']);
            
            
            	if ( $abf->isValid() ) {
			$abf->Save();

			$abf->CommitTransaction();
			Redirect::Page( URLBuilder::getURL( NULL, 'AttendanceBonusCalc.php') );
			break;
		}
            
            
            $abf->FailTransaction();
            
                        break;
                        
        case 'generate_attendance_bonuses':
            
                Debug::Text('Generate Bonus!', __FILE__, __LINE__, __METHOD__,10);

		Redirect::Page( URLBuilder::getURL( array('action' => 'generate_attendance_bonuses', 'filter_user_id' => $data['id'], 'next_page' => URLBuilder::getURL( array('att_bo_id' => $data['id'] ), '../users/AttendanceBonusList.php') ), '../progress_bar/ProgressBarControl.php') );

        
            break;
	default:
		if ( isset($id) ) {
                    
                    $ablf = TTnew( 'AttendanceBonusListFactory' );
                    $ablf->GetByIdAndCompanyId($id, $current_company->getId() );
                    
                    if($ablf->getRecordCount() > 0){
                        
                        $abf_obj = $ablf->getCurrent();
                        
                        $data = array(
                                        'id'=> $abf_obj->getId(),
                                        'year'=> $abf_obj->getYear(),
                                        'company_id'=> $abf_obj->getCompany(),
                                        'bonus_december_id'=>$abf_obj->getBonusDecember(),
                                    );
                    }
                    
                }
                
                $smarty->assign_by_ref('data', $data);

                 break;
}


$bdlf = TTnew( 'BonusDecemberListFactory' );
$bonus_december_options = $bdlf->getByCompanyIdArray( $current_company->getId() );



$smarty->assign_by_ref('abf', $abf);
$smarty->assign_by_ref('bonus_december_options', $bonus_december_options);
$smarty->assign_by_ref('view', $view);


$smarty->display('users/EditAttendanceBonusCalc.tpl');