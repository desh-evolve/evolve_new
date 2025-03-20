<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 5459 $
 * $Id: EditCompanyDeduction.php 5459 2011-11-04 21:40:55Z ipso $
 * $Date: 2011-11-04 14:40:55 -0700 (Fri, 04 Nov 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

/*
if ( !$permission->Check('accrual','view')
		OR (  $permission->Check('accrual','view_own') ) ) {
	$permission->Redirect( FALSE ); //Redirect
}
*/

$smarty->assign('title', TTi18n::gettext($title = 'Apply Employee Leaves')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'data',
                                                                                                'filter_data'
												) ) );




$header_leave = array();
$total_asign_leave = array();
$total_taken_leave = array();
$total_balance_leave = array();

$alf = new AccrualListFactory();

$lrlf = new LeaveRequestListFactory();
$lrlf->getById($id);

if($lrlf->getRecordCount() >0){

    $arf = $lrlf->getCurrent();
    
   
    
$aplf = new AccrualPolicyListFactory();
$aplf->getByCompanyIdAndTypeId($current_company->getId(),20);



foreach($aplf as $apf){
 
    $alf->getByCompanyIdAndUserIdAndAccrualPolicyIdAndStatusForLeave($current_company->getId(),$arf->getUser(),$apf->getId(),30);
    
    
    $header_leave[]['name']=$apf->getName();
            
    if($alf->getRecordCount() > 0)
    {
       $af= $alf->getCurrent();
       $total_asign_leave[]['asign'] =  $af->getAmount()/28800;
    }
    else{
        $total_asign_leave[]['asign'] = 0;
    }


    
  $ttotal =  $alf->getSumByUserIdAndAccrualPolicyId($arf->getUser(),$apf->getId());
  
     
    if($alf->getRecordCount() > 0)
    {
       $af= $alf->getCurrent();
       $total_taken_leave[]['taken'] =   ($af->getAmount()/28800)-($ttotal/28800);
       $total_balance_leave[]['balance'] = ($ttotal/28800);
    }
    else{
        $total_taken_leave[]['taken'] = 0;
         $total_balance_leave[]['balance'] = 0;
    }
   //  print_r($ttotal);
  // print_r('rr');
    
}

}
//print_r($total_taken_leave);
//print_r($total_balance_leave);

//$data['no_days'] = '';
$smarty->assign_by_ref('total_asign_leave', $total_asign_leave);
$smarty->assign_by_ref('total_taken_leave', $total_taken_leave);
$smarty->assign_by_ref('total_balance_leave', $total_balance_leave);
$smarty->assign_by_ref('header_leave', $header_leave);

$smarty->assign_by_ref('data', $data);
$smarty->assign_by_ref('user', $current_user);

//$current_user->getId()

$smarty->display('leaves/VIewNumberOfLeave.tpl');

?>