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
    
    if ( isset($data['dob']) AND $data['dob'] != '') {
        $data['dob'] = TTDate::parseDateTime($data['dob']);
    }
    
    
}


$ucif = new UserCensusInformationFactory();

$action = Misc::findSubmitButton();
$action = strtolower($action);

switch ($action) {
	case 'submit':
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);
            
               // print_r($data);
              //  exit();
                
		$ucif->setId( $data['id'] );
		$ucif->setUser( $data['user_id'] );
		$ucif->setDependant( $data['dependant'] );
		$ucif->setName( $data['name'] );
		$ucif->setRelationship( $data['relationship']);
                $ucif->setNic( $data['nic'] );
		$ucif->setBirthDate( $data['dob'] );
		$ucif->setGender( $data['gender'] );

		if ( $ucif->isValid() ) {
			$ucif->Save();

			Redirect::Page( URLBuilder::getURL( array('filter_user_id' => $data['user_id']) , 'censusinfo.php') );

			break;
		}
                
            
                break;
            
            default:
		if ( isset($id) ) {
                    
                   BreadCrumb::setCrumb($title);
                   
                   $ucilf = new UserCensusInformationListFactory();
		   $ucilf->getById($id);
                   
                   foreach ($ucilf as $ucif_obj) {
                       
                       $data = array(
                           'id' => $ucif_obj->getId(),
                           'user_id' => $ucif_obj->getUser(),
                           'dependant' => $ucif_obj->getDependant(),
                           'name' => $ucif_obj->getName(),
                           'relationship' => $ucif_obj->getRelationship(),
                           'dob' => $ucif_obj->getBirthDate(),
                           'nic' => $ucif_obj->getNic(),
                           'gender' => $ucif_obj->getGender(),
                       );
                       
                   }
                        
                    
                }
                else{
                    $data['user_id']= $filter_user_id;
                }
                
                $ulf = new UserListFactory();
		$user_options = $ulf->getByCompanyIDArray( $current_company->getId(), TRUE );
                
                
                $data['user_options'] = $user_options;
                $data['gender_options'] = $ucif->getOptions('gender');
                        
                $smarty->assign_by_ref('data', $data);
                
               

		break;
}

$smarty->assign_by_ref('ucif', $ucif);
$smarty->display('users/editCensus.tpl');