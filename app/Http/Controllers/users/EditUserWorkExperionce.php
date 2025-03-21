<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

$smarty->assign('title', __($title = 'Edit Census')); // See index.php

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
                                                                                                'filter_user_id',
												
                                                                                    
												) ) );




if ( isset($data) ) {
    
    if ( isset($data['from_date']) AND $data['from_date'] != '') {
        $data['from_date'] = TTDate::parseDateTime($data['from_date']);
    }
    
    if ( isset($data['to_date']) AND $data['to_date'] != '') {
        $data['to_date'] = TTDate::parseDateTime($data['to_date']);
    }
    
    
}


$uwef = new UserWorkExperionceFactory();


$action = Misc::findSubmitButton();
$action = strtolower($action);

switch ($action) {
	case 'submit':
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);
            
               // print_r($data);
              //  exit();
                
		$uwef->setId( $data['id'] );
		$uwef->setUser( $data['user_id'] );
		$uwef->setCompanyName( $data['company_name'] );
		$uwef->setFromDate( $data['from_date'] );
		$uwef->setToDate( $data['to_date']);
                $uwef->setDepartment( $data['department'] );
		$uwef->setDesignation( $data['designation'] );
                $uwef->setRemarks( $data['remaks'] );

		if ( $uwef->isValid() ) {
			$uwef->Save();

			Redirect::Page( URLBuilder::getURL( array('filter_user_id' => $data['user_id']) , 'UserWorkExperionce.php') );

			break;
		}
                
            
                break;
            
            default:
		if ( isset($id) ) {
                    
                   BreadCrumb::setCrumb($title);
                   
                   $uwelf = new UserWorkExperionceListFactory();
		   $uwelf->getById($id);
                   
                   foreach ($uwelf as $uwef_obj) {
                       
                       $data = array(
                           'id' => $uwef_obj->getId(),
                           'user_id' => $uwef_obj->getUser(),
                           'company_name' => $uwef_obj->getCompanyName(),
                           'from_date' => $uwef_obj->getFromDate(),
                           'to_date' => $uwef_obj->getToDate(),
                           'department' => $uwef_obj->getDepartment(),
                           'designation' => $uwef_obj->getDesignation(),
                           'remaks' => $uwef_obj->getRemarks(),
                          
                       );
                       
                   }
                        
                    
                }
                else{
                    $data['user_id']= $filter_user_id;
                }
                
                $ulf = new UserListFactory();
		$user_options = $ulf->getByCompanyIDArray( $current_company->getId(), TRUE );
                
                
                $data['user_options'] = $user_options;
               
                        
                $smarty->assign_by_ref('data', $data);
                
               

		break;
}

$smarty->assign_by_ref('uwef', $uwef);
$smarty->display('users/EditUserWorkExperionce.tpl');