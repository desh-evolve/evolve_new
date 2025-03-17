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
                                                                                                'dec_bo_id'
												
												) ) );

//echo $dec_bo_id;

$action = Misc::findSubmitButton();
$action = strtolower($action);

    switch ($action) {

        case 'submit':

                  break;
            default:
              
                $data = array();
                
             if(isset($dec_bo_id)){
                 
                 
                $bdulf = new BonusDecemberUserListFactory();
                $bdulf->getByBonusDecemberId($dec_bo_id);
                
                foreach($bdulf as $bdu_obj){
                    
                    $data[] = array(              'id'=>$bdu_obj->getId(),
                                                'empno'=>$bdu_obj->getUserObject()->getEmployeeNumber(),
                                                'name'=>$bdu_obj->getUserObject()->getFullName(),
                                                'amount'=> number_format($bdu_obj->getBonusAmount(),2),
													
							);
                    
                }
             }
             
            
                $smarty->assign_by_ref('data', $data);
                
                break;

    }

    
    
    $smarty->display('users/BonusList.tpl');