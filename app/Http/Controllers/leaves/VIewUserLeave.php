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

$smarty->assign('title', __($title = 'Apply Employee Leaves')); // See index.php

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




$lrlf = new LeaveRequestListFactory();
$lrlf->getById($id);

if($lrlf->getRecordCount() >0){
    
  $lrf =  $lrlf->getCurrent();




//Select box options; getName() no_days


$aplf = new AccrualPolicyListFactory();
$aplf->getByCompanyIdAndTypeId($current_company->getId(),20);

$leave_options = array();
foreach($aplf as $apf){
    $leave_options[$apf->getId()]=$apf->getName();
}
$leave_options = Misc::prependArray( array( 0 => _('-- Please Choose --') ), $leave_options );
$data['leave_options'] = $leave_options;
        
        
        
$ulf = new UserListFactory();
//$filter_data['default_branch_id'] = $current_user->getDefaultBranch();
$filter_data['exclude_id'] = 1;

$ulf->getAPISearchByCompanyIdAndArrayCriteria( $current_company->getId(),$filter_data);
//$ulf->getAll();

$user_options = array();

foreach($ulf as $uf){
    
  $user_options[$uf->getId()] = $uf->getPunchMachineUserID().'-'.$uf->getFullName() ; 
}


$leaves =$lrf->getLeaveDates();

 $date_array = explode(',', $leaves);
 $date_string = '';
 foreach($date_array as $date){
   $date_string .= "'".trim($date)."'," ; 
 }


$user_options = Misc::prependArray( array( 0 => _('-- Please Choose --') ), $user_options );
$data['users_cover_options'] = $user_options;
//$data['users_cover_options'] = $ulf;
$data['name'] =$lrf->getUserObject()->getFullName();
$data['title'] = $lrf->getDesignationObject()->getName();
$data['leave_type'] = $lrf->getAccuralPolicyObject()->getId();

$method_options = $lrf->getOptions('leave_method');
$method_options = Misc::prependArray( array( 0 => _('-- Please Choose --') ), $method_options );

$data['method_options'] = $method_options;
$data['method_type'] = $lrf->getLeaveMethod();

$data['no_days'] = $lrf->getAmount();
$data['leave_start_date'] = $lrf->getLeaveFrom();
$data['leave_end_date'] = $lrf->getLeaveTo();
$data['reason']=$lrf->getReason();
$data['address_tel']=$lrf->getAddressTelephone();
$data['cover_duty']=$lrf->getCoveredBy();
$data['supervised_by']=$lrf->getSupervisorId();
$data['appt_time']=$lrf->getLeaveTime();
$data['end_time']=$lrf->getLeaveEndTime();
$data['leave_dates']=$date_string;




}

$smarty->assign_by_ref('data', $data);
$smarty->assign_by_ref('user', $current_user);

//$current_user->getId()

$smarty->display('leaves/VIewUserLeave.tpl');

?>