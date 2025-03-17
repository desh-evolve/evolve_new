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

$smarty->assign('title', TTi18n::gettext($title = 'Attendance Bonus Calculation')); // See index.php
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
		Redirect::Page( URLBuilder::getURL( NULL, 'EditAttendanceBonusCalc.php') );
		break;
       
	
	default:
            
            $ablf = TTnew('AttendanceBonusListFactory');
            $ablf->getByCompanyId($current_company->getId());
            $bonuses = array();
            
            foreach($ablf as $ab_obj){
             
                $bonuses[] = array( 
                                    
					'id' => $ab_obj->getId(),
					'company' => $ab_obj->getCompanyObject()->getName(),
                                        'year' => $ab_obj->getYear(),
                    		
					
                    
                                    );
                
            }
            
           // print_r($bonuses);            exit();
            $smarty->assign_by_ref('bonuses', $bonuses);
            
            break;
}

            
$smarty->display('users/AttendanceBonusCalc.tpl');