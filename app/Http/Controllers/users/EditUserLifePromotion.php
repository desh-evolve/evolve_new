<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

$smarty->assign('title', TTi18n::gettext($title = 'Edit Employee Promotions')); // See index.php

if ( !$permission->Check('user','enabled')
		OR !( $permission->Check('user','edit') OR $permission->Check('user','edit_own') OR $permission->Check('user','edit_child') OR $permission->Check('user','add')) ) {
	$permission->Redirect( FALSE ); //Redirect
}


extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'user_data',
                                                                                                'data',
												'filter_user_id'
                                                                                    
												) ) );




if ( isset($data) ) {
    
    if ( isset($data['effective_date']) AND $data['effective_date'] != '') {
        $data['effective_date'] = TTDate::parseDateTime($data['effective_date']);
    }
    
    
}


$ulpf = new UserLifePromotionFactory();

$action = Misc::findSubmitButton();
$action = strtolower($action);

switch ($action) {
	case 'submit':
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);
            
               // print_r($data);
              //  exit();
                
		$ulpf->setId( $data['id'] );
		$ulpf->setUser( $data['user_id'] );
		$ulpf->setCurrentDesignation( $data['current_designation'] );
		$ulpf->setNewDesignation( $data['new_designation'] );
		$ulpf->setCurrentSalary( $data['current_salary']);
                $ulpf->setNewSalary( $data['new_salary'] );
                $ulpf->setEffectiveDate( $data['effective_date'] );
		

		if ( $ulpf->isValid() ) {
			$ulpf->Save();

			Redirect::Page( URLBuilder::getURL( array('filter_user_id' => $data['user_id']) , 'UserLifePromotion.php') );

			break;
		}
                
            
                break;
            
            default:
		if ( isset($id) ) {
                    
                   BreadCrumb::setCrumb($title);
                   
                   $ulplf = new UserLifePromotionListFactory();
		   $ulplf->getById($id);
                   
                   foreach ($ulplf as $ulpf_obj) {
                       
                       $data = array(
                           'id' => $ulpf_obj->getId(),
                           'user_id' => $ulpf_obj->getUser(),
                           'current_designation' => $ulpf_obj->getCurrentDesignation(),
                           'new_designation' => $ulpf_obj->getNewDesignation(),
                           'current_salary' => $ulpf_obj->getCurrentSalary(),
                           'new_salary' => $ulpf_obj->getNewSalary(),
                           'effective_date' => $ulpf_obj->getEffectiveDate(),
                          
                       );
                       
                   }
                        
                    
                }
                
                $ulf = new UserListFactory();
		$user_options = $ulf->getByCompanyIDArray( $current_company->getId(), TRUE );
                
                
                $data['user_options'] = $user_options;
                //$data['user_id'] = $filter_user_id;
               
                        
                $smarty->assign_by_ref('data', $data);
                
               

		break;
}

//$ulpf->setUser($filter_user_id);
$smarty->assign_by_ref('ulpf', $ulpf);
$smarty->display('users/EditUserLifePromotion.tpl');