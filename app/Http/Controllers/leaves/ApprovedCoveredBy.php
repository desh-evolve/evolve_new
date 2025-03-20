<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');



$smarty->assign('title', TTi18n::gettext($title = 'Employee Leaves covered Aprooval'));


extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'data',
                                                                                                'filter_data'
												) ) );


//$lrlf = new LeaveRequestListFactory();

$lrlf = new LeaveRequestListFactory();



$lrlf->getByCoveredEmployeeId($current_user->getId());

$data = array();
 echo $current_user->getRecordCount();
$leave= array();
if($lrlf->getRecordCount() >0){
    
   
foreach($lrlf as $lrf_obj) {

    $leave['id'] = $lrf_obj->getId();
    $leave['user'] = $lrf_obj->getUserObject()->getFullName();
    
    $methord = $lrf_obj->getOptions('leave_method');
    $leave['leave_method'] = $methord[$lrf_obj->getLeaveMethod()];
    $leave['start_date'] = $lrf_obj->getLeaveFrom();
    $leave['end_date'] = $lrf_obj->getLeaveTo();
    $leave['is_covered_approved'] = $lrf_obj->getCoveredApproved();
    
    $data['leaves'][] =$leave;
}
}
 
 

$smarty->assign_by_ref('data', $data);        
        
$smarty->display('leaves/ApprovedCoveredBy.tpl');