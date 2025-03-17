<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

$smarty->assign('title', TTi18n::gettext($title = 'Edit Qualification')); // See index.php

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
    
    
    
    
}


$uef = new UserEducationFactory();

$action = Misc::findSubmitButton();
$action = strtolower($action);

switch ($action) {
	case 'submit':
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);
            
               
                
		$uef->setId( $data['id'] );
		$uef->setUser( $data['user_id'] );
		$uef->setQualificationName( $data['qualification'] );
		$uef->setInstitute( $data['institute'] );
		$uef->setYear( $data['year']);
                $uef->setRemarks( $data['remaks'] );
		

		if ( $uef->isValid() ) {
			$uef->Save();

			Redirect::Page( URLBuilder::getURL( array('filter_user_id' => $data['user_id']) , 'UserEducation.php') );

			break;
		}
                
            
                break;
            
            default:
		if ( isset($id) ) {
                    
                   BreadCrumb::setCrumb($title);
                   
                   $uelf = new UserEducationListFactory();
		   $uelf->getById($id);
                   
                   foreach ($uelf as $uef_obj) {
                       
                       $data = array(
                           'id' => $uef_obj->getId(),
                           'user_id' => $uef_obj->getUser(),
                           'qualification' => $uef_obj->getQualificationName(),
                           'institute' => $uef_obj->getInstitute(),
                           'year' => $uef_obj->getYear(),
                           'remaks' => $uef_obj->getRemarks(),
                          
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

$smarty->assign_by_ref('uef', $uef);
$smarty->display('users/EditUserEducation.tpl');