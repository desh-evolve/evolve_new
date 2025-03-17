<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4600 $
 * $Id: PayStubSummary.php 4600 2011-04-28 21:35:12Z ipso $
 * $Date: 2011-04-28 14:35:12 -0700 (Thu, 28 Apr 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('report','enabled')
		OR !$permission->Check('report','view_pay_stub_summary') ) {//ARSP NOTE --> THIS NEW PERMISSION ADDED BY ME
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'Seach Job Skills '));  // See index.php


/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'generic_data',
												'filter_data'
												) ) );





/**
 * ARSP NOTE --> MUST ADD THIS TPL FILE -> header_job_skills.tpl IN TO jobSkills.tpl
 * 
 */



/**
 * ARSP NOTE --> THIS PARAMETER AUTOMATICALLY ADD TO URL (QUERY STRING)
 */
/*
URLBuilder::setURL($_SERVER['SCRIPT_NAME'],
											array(
													'filter_data' => $filter_data,
                                                                                                        'job_skills_list' => $job_skills_list
//													'sort_column' => $sort_column,
//													'sort_order' => $sort_order,
												) );
*/











$ugdlf = new UserGenericDataListFactory();
$ugdf = new UserGenericDataFactory();

$action = Misc::findSubmitButton();
switch ($action) {
	case 'export':
            
		$ulf = new UserListFactory();
		$ulf->getSearchByJobSkills($filter_data['job_skills']);

                
		$job_skills_list = array();
		if ( $ulf->getRecordCount() > 0 ) {
                    
                    $blf = new BranchListFactory();
                    
                    
			foreach ($ulf as $job_skills) {
                            
				$job_skills_list[] = array(
									'id' => $job_skills->getId(),
									'full_name' => $job_skills->getFullName(),
									'default_branch_id' => $job_skills->getDefaultBranch(),
                                                                        'default_branch_name' => $blf->getNameById($job_skills->getDefaultBranch()),
                                                                        'mobile_phone' => $job_skills->getMobilePhone(),
                                                                        'job_skills' => $job_skills->getJobSkills()									
								);                               
			}
                        //print_r($job_skills_list);
                } 
		                
                BreadCrumb::setCrumb($title);
		$smarty->assign_by_ref('job_skills_list', $job_skills_list );
                $smarty->assign_by_ref('filter_data', $filter_data);
                $smarty->assign_by_ref('ugdf', $ugdf);
                $smarty->display('report/jobSkills.tpl');
                
                break;
	default:
		BreadCrumb::setCrumb($title);


                
		//$smarty->assign_by_ref('generic_data', $generic_data);

		$smarty->assign_by_ref('filter_data', $filter_data);

		$smarty->assign_by_ref('ugdf', $ugdf);

		$smarty->display('report/jobSkills.tpl');

		break;
}
?>