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


$smarty->assign('title', TTi18n::gettext($title = 'Bonus List ')); // See index.php
BreadCrumb::setCrumb($title);

extract	(FormVariables::GetVariables(
										array	(
												'action',
                                                                                                'att_bo_id'
												
												) ) );

//echo $dec_bo_id;

$action = Misc::findSubmitButton();
$action = strtolower($action);

  switch ($action) {

        case 'submit':

                  break;
            default:
              
                $data = array();
                
             if(isset($att_bo_id)){
                 
                 
                $abulf = new AttendanceBonusUserListFactory();
                $abulf->getByBonusAttendanceId($att_bo_id);
                
                foreach($abulf as $bau_obj){
                    
                    $data[] = array(              'id'=>$bau_obj->getId(),
                                                'empno'=>$bau_obj->getUserObject()->getEmployeeNumber(),
                                                'name'=>$bau_obj->getUserObject()->getFullName(),
                                                'amount'=> number_format($bau_obj->getBonusAmount(),2),
													
							);
                    
                }
             }
             
            
                $smarty->assign_by_ref('data', $data);
                
                break;

    }

    
    
    $smarty->display('users/AttendanceBonusList.tpl');
    