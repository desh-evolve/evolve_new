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

$smarty->assign('title', __($title = 'Bonus Calculation')); // See index.php
BreadCrumb::setCrumb($title);

extract	(FormVariables::GetVariables(
										array	(
												'action',
												'page',
												'sort_column',
												'sort_order',
												'filter_user_id',
												'ids',
												) ) );

URLBuilder::setURL($_SERVER['SCRIPT_NAME'],
											array(
													'filter_user_id' => $filter_user_id,
													'sort_column' => $sort_column,
													'sort_order' => $sort_order,
													'page' => $page
												) );


$sort_array = NULL;
if ( $sort_column != '' ) {
	$sort_array = array($sort_column => $sort_order);
}

Debug::Arr($ids,'Selected Objects', __FILE__, __LINE__, __METHOD__,10);

$action = Misc::findSubmitButton();




switch ($action) {
	case 'add':
		Redirect::Page( URLBuilder::getURL( NULL, 'EditBonusCalc.php') );
		break;
       
	
	default:
            
            $bdlf = new BonusDecemberListFactory();
            $bdlf->getByCompanyId($current_company->getId());
            $bonuses = array();
            
            foreach($bdlf as $bd_obj){
                
                $bonuses[] = array( 
                                    
					'id' => $bd_obj->getId(),
					'company_id' => $bd_obj->getCompany(),
                                        'y_number' => $bd_obj->getYNumber(),
                    			'start_date' => $bd_obj->getStartDate(),
					'end_date' => $bd_obj->getEndDate(),
                    
                                    );
                
            }
            
           // print_r($bonuses);            exit();
            $smarty->assign_by_ref('bonuses', $bonuses);
            
            break;
}

            
$smarty->display('users/bonusCalc.tpl');