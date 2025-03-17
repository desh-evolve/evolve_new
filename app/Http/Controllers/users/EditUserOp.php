<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: EditUserDeduction.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('user_tax_deduction','enabled')
		OR !( $permission->Check('user_tax_deduction','edit') OR $permission->Check('user_tax_deduction','edit_own') OR $permission->Check('user_tax_deduction','add') ) ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'Edit Employee OP /OT Hours')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'start_date',
                                                                                                'filter_data',
                                                                                                'data'
												
												) ) );

$udf = new UserDeductionFactory();
$cdf = new CompanyDeductionFactory();
$ulf = new UserListFactory();

$action = Misc::findSubmitButton();
$action = strtolower($action);

print_r($action);
switch ($action) {
    
        case 'search_ot':
            
            
             $date = DateTime::createFromFormat('d/m/Y', $start_date);
             $date_formated = $date->format('Y-m-d');
            
            
             $udtlf = new UserDateTotalListFactory();
             $udtlf->getUserOPorOTValuesByDateAndType($date_formated,30);
             //$udtlf->getById(40);
             
             $data = array();
             foreach ($udtlf as $udtf){
                 $data_temp['user_id'] = $udtf->getUserDateObject()->getUserObject()->getPunchMachineUserID();
                 $data_temp['user_date_id'] = $udtf->getId();
                 $data_temp['last_name'] = $udtf->getUserDateObject()->getUserObject()->getFullName();
                 $data_temp['total_time'] = $udtf->getTotalTime() ;
                 $data_temp['actual_time'] = $udtf->getActualTotalTime()  ;
                 
                 $data[] = $data_temp;
             }
             
            // print_r($data);
             
             
                 
                   $date = DateTime::createFromFormat('d/m/Y', $start_date);
                  $date_formated = $date->format('d/m/Y');
                 
                    $smarty->assign_by_ref('udtlf', $data);
                    $smarty->assign_by_ref('start_date', $date_formated);

                    $smarty->display('users/EditUserOp.tpl' ,$page_type == 'mass_user');
                 
             
             
              break;
            
        case 'search_op':
            
          
          
            $date = DateTime::createFromFormat('d/m/Y', $start_date);
            $date_formated = $date->format('Y-m-d');
            
            
             $udtlf = new UserDateTotalListFactory();
             $udtlf->getUserOPorOTValuesByDateAndType($date_formated,40);
             //$udtlf->getById(40);
             
             $data = array();
             foreach ($udtlf as $udtf){
                 $data_temp['user_id'] = $udtf->getUserDateObject()->getUserObject()->getPunchMachineUserID();
                 $data_temp['user_date_id'] = $udtf->getId();
                 $data_temp['last_name'] = $udtf->getUserDateObject()->getUserObject()->getFullName();
                 $data_temp['total_time'] = $udtf->getTotalTime() ;
                 $data_temp['actual_time'] = $udtf->getActualTotalTime() ;
                 $data[] = $data_temp;
             }
             
            // print_r($data);
             
                    $date = DateTime::createFromFormat('d/m/Y', $start_date);
                    $date_formated = $date->format('d/m/Y');
                 
                    $smarty->assign_by_ref('udtlf', $data);
                    $smarty->assign_by_ref('start_date', $date_formated);

                    $smarty->display('users/EditUserOp.tpl' ,$page_type == 'mass_user');
                 
            
             
            
             
           //  print_r($udtlf);
            
           
            break;
	case 'submit':
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);
		//Debug::setVerbosity(11);  user_date_total
            
               // print_r($data);
                $udtlf = new UserDateTotalListFactory();
                $udtlf->StartTransaction();
                
                foreach($data['user_date_total']  as $key => $value){
                    
                   $udtlf->getById($key);
                   $udtf = $udtlf->getCurrent();
                   
                  $hrTime = explode(":", $value['total_time']);
                  
                  $hours = $hrTime[0];
                  $minite = $hrTime[1];
                  
                  $total_time = ($hours * 3600) + ($minite * 60) ;
                  

                   $udtf->setTotalTime($total_time);
                   $udtf->save();
                  
                 
                    
               // $udtlf->getById()

		
		
		
                }
                $udtlf->FailTransaction();
                
	default:
            
            $company_deduction_id =1;

//ARSP EDIT --> ADD NEW CODE FOR TOTAL AMOUNT OF THE DEDUCTION OR EARNING
$smarty->assign_by_ref('total_amount', $total_amount);

$smarty->assign_by_ref('udf', $udf);
$smarty->assign_by_ref('company_deduction_id', $start_date);

$smarty->display('users/EditUserOp.tpl');
		
}


?>
