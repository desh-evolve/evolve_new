<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');



$smarty->assign('title', TTi18n::gettext($title = 'Employee Leaves Supervisor Aprooval'));


extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'data',
                                                                                                'filter_data'
												) ) );


//$lrlf = new LeaveRequestListFactory();
$data = array();
//$msg = "";
$lrlf = new LeaveRequestListFactory();




$lrlf->getByHrEmployeeId($current_user->getId());


 //echo $current_user->getRecordCount();
$leave= array();
if($lrlf->getRecordCount() >0){
    
   
foreach($lrlf as $lrf_obj) {

    $leave['id'] = $lrf_obj->getId();
    $leave['user'] = $lrf_obj->getUserObject()->getFullName();
    $leave['user_id'] = $lrf_obj->getUser();
    $leave['leave_name'] = $lrf_obj->getAccuralPolicyObject()->getName();
    $leave['start_date'] = $lrf_obj->getLeaveFrom();
    $leave['end_date'] = $lrf_obj->getLeaveTo();
    $leave['amount'] = $lrf_obj->getAmount();
    $leave['is_hr_approved'] = $lrf_obj->getHrApproved();
    
    $data['leaves'][] =$leave;
}
}
 
$data['msg'] = $msg;

$smarty->assign_by_ref('data', $data);        
        
$smarty->display('leaves/ApprovedHrBy.tpl');



function returnBetweenDates( $startDate, $endDate ){
    $startStamp = strtotime(  $startDate );
    $endStamp   = strtotime(  $endDate );

    if( $endStamp > $startStamp ){
        while( $endStamp >= $startStamp ){

            $dateArr[] = date( 'Y-m-d', $startStamp );

            $startStamp = strtotime( ' +1 day ', $startStamp );

        }
        return $dateArr;    
    }else{
        return $startDate;
    }

}