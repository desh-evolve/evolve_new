<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: AccrualBalanceSummary.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('report','enabled')
		OR !$permission->Check('report','view_accrual_balance_summary') ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'Leave Balance Summary Report')); // See index.php


/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'generic_data',
												'filter_data'

												) ) );

URLBuilder::setURL($_SERVER['SCRIPT_NAME'],
											array(
													'filter_data' => $filter_data
												) );

$static_columns = array(			'-1000-full_name' => TTi18n::gettext('Full Name'),
									'-1002-employee_number' => TTi18n::gettext('Employee #'),
									'-1010-title' => TTi18n::gettext('Title'),
									'-1020-province' => TTi18n::gettext('Province/State'),
									'-1030-country' => TTi18n::gettext('Country'),
									'-1039-group' => TTi18n::gettext('Group'),
									'-1040-default_branch' => TTi18n::gettext('Default Branch'),
									'-1050-default_department' => TTi18n::gettext('Default Department'),
									);

$columns = array(
				'-1060-total_balance' => TTi18n::gettext('Total Balance'),
				);

$columns = Misc::prependArray( $static_columns, $columns);

//Get all accrual policies.
$aplf = new AbsencePolicyListFactory();
$aplf->getByCompanyId($current_company->getId());
if ( $aplf->getRecordCount() > 0 ) {
	foreach ($aplf as $ap_obj ) {
		$ap_columns['absence_policy-'.$ap_obj->getId()] = $ap_obj->getName();
	}

	$columns = array_merge( $columns, $ap_columns);
}
echo '<pre>'; print_r($filter_data);
$filter_data = Misc::preSetArrayValues( $filter_data, array('include_user_ids', 'exclude_user_ids', 'user_status_ids', 'group_ids', 'branch_ids', 'department_ids', 'user_title_ids', 'leave_year_ids', 'pay_period_ids', 'column_ids' ), array() );
echo '<br>-------'; print_r($filter_data);

$permission_children_ids = array();
if ( $permission->Check('accrual','view') == FALSE ) {
	$hlf = new HierarchyListFactory();
	$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );
	Debug::Arr($permission_children_ids,'Permission Children Ids:', __FILE__, __LINE__, __METHOD__,10);

	if ( $permission->Check('accrual','view_child') == FALSE ) {
		$permission_children_ids = array();
	}
	if ( $permission->Check('accrual','view_own') ) {
		$permission_children_ids[] = $current_user->getId();
	}

	$filter_data['permission_children_ids'] = $permission_children_ids;
}

$ugdlf = new UserGenericDataListFactory();
$ugdf = new UserGenericDataFactory();

$action = Misc::findSubmitButton();

switch ($action) {
	case 'export':
	case 'display_report':
		//Debug::setVerbosity(11);

		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($filter_data, 'Filter Data', __FILE__, __LINE__, __METHOD__,10);
            
		$ulf = new UserListFactory();
		$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data ); 
		if ( $ulf->getRecordCount() > 0 ) {  
                    $utlf = new UserTitleListFactory();
                    $title_options = $utlf->getByCompanyIdArray( $current_company->getId() );

                    $uglf = new UserGroupListFactory();
                    $group_options = $uglf->getArrayByNodes( FastTree::FormatArray( $uglf->getByCompanyIdArray( $current_company->getId() ), 'no_tree_text', TRUE) );

                    $blf = new BranchListFactory();
                    $branch_options = $blf->getByCompanyIdArray( $current_company->getId() );

                    $dlf = new DepartmentListFactory();
                    $department_options = $dlf->getByCompanyIdArray( $current_company->getId() );

                    $x=0; 

                    foreach( $ulf as $u_obj ) {


                        $filter_data['user_id'][] = $u_obj->getId();
                        $user_id = $u_obj->getId(); 

                        $ablf = new AccrualBalanceListFactory();
                        $ablf->getByUserIdAndCompanyId( $filter_data['user_id'], $current_company->getId() );

                        $total_balance_leave_all = array('full_day'=>0, 'half_day'=>0, 'short_leave'=>0);

                        //echo '<pre>....'; print_r($filter_data);

                        foreach ($columns as $column_abs => $column_abs_vl){ 
                        //foreach ($filter_data['column_ids'] as $column_abs ){
                            //echo $column_abs.'<br>';
                            
                            $$absence_policy_id = '';
                            $colAbs_arr = explode('-', $column_abs);

                            if($colAbs_arr[0] == 'absence_policy' && $column_abs[1] != ''){
                                $absence_policy_id = $colAbs_arr[1];

                                //echo 'cccc'.$absence_policy_id; 

                                //get total leaves for particular date year 
                                $alulf = new AbsenceLeaveUserListFactory();
                                
                                $alulf->getEmployeeTotalLeaves($absence_policy_id, $user_id, $filter_data['leave_year_ids'][0]);
                                $total_assigned_leaves = 0; 

                               

                                if(count($alulf) > 0){
                                    foreach($alulf as $alulf_obj){
                                        $total_assigned_leaves = $total_assigned_leaves + $alulf_obj->getAmount();
                                    } 
                                }
                                //get used Leave for particular date year
                                 $aluerlf = new AbsenceLeaveUserEntryRecordListFactory();
                                 $aluerlf->getByAbsencePolicyIdAndUserId2($absence_policy_id,$user_id);
                                 $total_used_leaves = 0;

                                 if(count($aluerlf) > 0){ 
                                    $allf1 = new AbsenceLeaveListFactory();
                                     foreach($aluerlf as $aluerlf_obj){

                                        //$amount = $aluerlf_obj->getAmount();
                                         $amount = $allf1->getRelatedAmountTime($aluerlf_obj->getAbsenceLeaveId());
                                         $total_used_leaves = $total_used_leaves + $amount;
                                    }
                                 }  

                                $total_balance_leave = $total_assigned_leaves - $total_used_leaves;

                                $rows[$x]['total_assigned_leaves'] = $total_assigned_leaves;
                                $rows[$x]['total_used_leaves'] = $total_used_leaves;


                                $allf = new AbsenceLeaveListFactory();

                                $allf->getAll(); 

                                foreach ($allf as $allf_obj){
                                    $absence_leave[$allf_obj->getId()] = $allf_obj;  
                                }

                                //leave Balance in days
                                $absence_bal['full_day'] = floor($total_balance_leave/$absence_leave[1]->getTimeSec());
                                $absence_bal['half_day'] = floor(($total_balance_leave%$absence_leave[1]->getTimeSec())/($absence_leave[$absence_leave[2]->getRelatedLeaveId()]->getTimeSec()/$absence_leave[2]->getRelatedLeaveUnit()));
                                $absence_bal['short_leave'] = floor(($total_balance_leave%$absence_leave[1]->getTimeSec())/($absence_leave[$absence_leave[3]->getRelatedLeaveId()]->getTimeSec()/$absence_leave[3]->getRelatedLeaveUnit()));

                                $total_balance_leave_all['full_day'] = $total_balance_leave_all['full_day'] +  $absence_bal['full_day'];
                                $total_balance_leave_all['half_day'] = $total_balance_leave_all['half_day'] +  $absence_bal['half_day'];
                                $total_balance_leave_all['short_leave'] = $total_balance_leave_all['short_leave'] +  $absence_bal['short_leave']; 


                                //echo '<pre>'; print_r($absence_bal); die; 

                                //$aa =  $absence_leave[1]->getShortCode().' - '.$absence_bal['full_day'].'  |  '.$absence_leave[2]->getShortCode().' - '.$absence_bal['half_day'].' or '.$absence_leave[3]->getShortCode().' - '.$absence_bal['short_leave'];

                                $rows[$x][$column_abs] =  $absence_leave[1]->getShortCode().' - '.$absence_bal['full_day'].'  |  '.$absence_leave[2]->getShortCode().' - '.$absence_bal['half_day'].' or '.$absence_leave[3]->getShortCode().' - '.$absence_bal['short_leave'];



                                } 
                                //echo '<pre>'; print_r($aa); die; 

                            }       

                            $user_obj = $ulf->getById( $user_id )->getCurrent();
                            

                            $allf = new AbsenceLeaveListFactory();

                            $allf->getAll(); 

                            foreach ($allf as $allf_obj){
                                $absence_leave[$allf_obj->getId()] = $allf_obj;  
                            }

                           $rows[$x]['total_balance'] =  $absence_leave[1]->getShortCode()
                                    .' - '.$total_balance_leave_all['full_day']
                                    .'  |  '.$absence_leave[2]->getShortCode()
                                    .' - '.$total_balance_leave_all['half_day']
                                    .' or '.$absence_leave[3]->getShortCode()
                                    .' - '.$total_balance_leave_all['short_leave'];


                            $rows[$x]['user_id'] = $user_id;
                            $rows[$x]['full_name'] = $user_obj->getFullName(TRUE);
                            $rows[$x]['employee_number'] = $user_obj->getEmployeeNumber();
                            $rows[$x]['province'] = $user_obj->getProvince();
                            $rows[$x]['country'] = $user_obj->getCountry();
                            $rows[$x]['title'] = Option::getByKey($user_obj->getTitle(), $title_options );
                            $rows[$x]['group'] = Option::getByKey($user_obj->getGroup(), $group_options );
                            $rows[$x]['default_branch'] = Option::getByKey($user_obj->getDefaultBranch(), $branch_options );
                            $rows[$x]['default_department'] = Option::getByKey($user_obj->getDefaultDepartment(), $department_options );

                            /*
                            $rows[$x]['title'] = $title_options[$user_obj->getTitle()];
                            $rows[$x]['group'] = $branch_options[$user_obj->getDefaultBranch()];
                            $rows[$x]['default_branch'] = $branch_options[$user_obj->getDefaultBranch()];
                            $rows[$x]['default_department'] = $department_options[$user_obj->getDefaultDepartment()];
                            */
                            $x++;
                            
                          

                        } 

			             // print_r($filter_data['user_id']);
                        //echo '<pre>'; var_dump($rows);
                        unset($tmp_rows);

                        if ( isset($filter_data['primary_group_by']) AND $filter_data['primary_group_by'] != '0' ) {
                                Debug::Text('Primary Grouping Data By: '. $filter_data['primary_group_by'], __FILE__, __LINE__, __METHOD__,10);

                                $ignore_elements = array_keys($static_columns);

                                $filter_data['column_ids'] = array_diff( $filter_data['column_ids'], $ignore_elements );

                                //Add the group by element back in
                                if ( isset($filter_data['secondary_group_by']) AND $filter_data['secondary_group_by'] != 0 ) {
                                        array_unshift( $filter_data['column_ids'], $filter_data['primary_group_by'], $filter_data['secondary_group_by'] );
                                } else {
                                        array_unshift( $filter_data['column_ids'], $filter_data['primary_group_by'] );
                                }

                                $rows = Misc::ArrayGroupBy( $rows, array(Misc::trimSortPrefix($filter_data['primary_group_by']),Misc::trimSortPrefix($filter_data['secondary_group_by'])), Misc::trimSortPrefix($ignore_elements) );
                        }

                         //echo '<pre>'; print_r( $filter_data['column_ids']);

                        if ( isset($rows) ) {
                            foreach($rows as $row) {
                                    $tmp_rows[] = $row;
                            }

                            //$rows = Sort::Multisort($tmp_rows, $filter_data['primary_sort'], NULL, 'ASC');
                            $rows = Sort::Multisort($tmp_rows, Misc::trimSortPrefix($filter_data['primary_sort']), Misc::trimSortPrefix($filter_data['secondary_sort']), $filter_data['primary_sort_dir'], $filter_data['secondary_sort_dir']);

                            $total_row = Misc::ArrayAssocSum($rows, NULL, 2);

                            $last_row = count($rows);
                            $rows[$last_row] = $total_row;
                            foreach ($static_columns as $static_column_key => $static_column_val) {
                                    $rows[$last_row][Misc::trimSortPrefix($static_column_key)] = NULL;
                            }
                            unset($static_column_key, $static_column_val);

                            //Convert units
                            $tmp_rows = $rows;
                            unset($rows);

                            $trimmed_static_columns = array_keys( Misc::trimSortPrefix($static_columns) );
                            foreach($tmp_rows as $row ) {

                                foreach($row as $column => $column_data) {
                                        //if ( $column != 'full_name' AND $column_data != '' ) {
                                        if ( !in_array( $column, $trimmed_static_columns ) ) {
    										//$column_data = TTDate::getTimeUnit( $column_data );
                                        }

                                        $row_columns[$column] = $column_data;
                                        unset($column, $column_data);
                                }

                                $rows[] = $row_columns;
                                unset($row_columns);
                            }

                              //var_dump($rows);
                    }
		}
                             
        //echo '<pre>columns';print_r($columns);

        //echo '<br>......filter_data<br>';print_r($filter_data);

		foreach( $filter_data['column_ids'] as $column_key ) { 
			$filter_columns[Misc::trimSortPrefix($column_key)] = $columns[$column_key];
                        
		}
        //echo '<br>......<br>';print_r($filter_columns);
        // print_r($columns); 
		//echo '<pre>'; print_r($rows); 
        //die; 


		if ( $action == 'display_report' ) {
                    Debug::Text('Exporting as PDF', __FILE__, __LINE__, __METHOD__,10);

                    //echo '<pre>'; print_r($rows); echo '----'; print_r($filter_columns); die;

                   $tssr = new TimesheetDetailReport();//new code   
                                                
                   $output = $tssr->EmployeeLeaveBalance($rows, $filter_columns, $filter_data, $current_user, $current_company);//new code   

                   echo 'ffffbb';                        

                   if ( Debug::getVerbosity() < 11 ) {                                    
                       Misc::FileDownloadHeader('EmployeeTimeSheetReport.pdf', 'application/pdf', strlen($output));
                       echo $output;
                       exit;                           
                    } 
		} else {
			$smarty->assign_by_ref('generated_time', TTDate::getTime() );
			$smarty->assign_by_ref('pay_period_options', $pay_period_options );
			$smarty->assign_by_ref('filter_data', $filter_data );
			$smarty->assign_by_ref('columns', $filter_columns );
			$smarty->assign_by_ref('rows', $rows);

			$smarty->display('report/AccrualBalanceSummaryReport.tpl');
		}

		break;
	case 'delete':
	case 'save':
		Debug::Text('Action: '. $action, __FILE__, __LINE__, __METHOD__,10);

		$generic_data['id'] = UserGenericDataFactory::reportFormDataHandler( $action, $filter_data, $generic_data, URLBuilder::getURL(NULL, $_SERVER['SCRIPT_NAME']) );
		unset($generic_data['name']);
	default:
		BreadCrumb::setCrumb($title);
		if ( $action == 'load' ) {
			Debug::Text('Loading Report!', __FILE__, __LINE__, __METHOD__,10);
			extract( UserGenericDataFactory::getReportFormData( $generic_data['id'] ) );
		} elseif ( $action == '' ) {
			//Check for default saved report first.
			$ugdlf->getByUserIdAndScriptAndDefault( $current_user->getId(), $_SERVER['SCRIPT_NAME'] );
			if ( $ugdlf->getRecordCount() > 0 ) {
				Debug::Text('Found Default Report!', __FILE__, __LINE__, __METHOD__,10);

				$ugd_obj = $ugdlf->getCurrent();
				$filter_data = $ugd_obj->getData();
				$generic_data['id'] = $ugd_obj->getId();
			} else {
				Debug::Text('Default Settings!', __FILE__, __LINE__, __METHOD__,10);
				//Default selections
				$filter_data['user_status_ids'] = array( -1 );
				$filter_data['branch_ids'] = array( -1 );
				$filter_data['department_ids'] = array( -1 );
				$filter_data['user_title_ids'] = array( -1 );
				$filter_data['leave_year_ids'] = array( -1 );
				$filter_data['group_ids'] = array( -1 );

				if ( !isset($filter_data['column_ids']) ) {
					$filter_data['column_ids']	= array();
				}
				$filter_data['column_ids'] = array_merge( $filter_data['column_ids'],
										array(
											'-100022-full_name',
											'-106022-total_balance',
												) );

				$filter_data['primary_sort'] = '-1000-full_name';
				$filter_data['secondary_sort'] = '-1060-total_balance';

			}
		}
		$filter_data = Misc::preSetArrayValues( $filter_data, array('include_user_ids', 'exclude_user_ids', 'user_status_ids', 'group_ids', 'branch_ids', 'department_ids', 'user_title_ids', 'leave_year_ids', 'pay_period_ids', 'column_ids' ), NULL );

		$ulf = new UserListFactory();
		$all_array_option = array('-1' => TTi18n::gettext('-- All --'));

		//Get include employee list.
		$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), array('permission_children_ids' => $permission_children_ids ) );
		$user_options = $ulf->getArrayByListFactory( $ulf, FALSE, TRUE );

		$filter_data['src_include_user_options'] = Misc::arrayDiffByKey( (array)$filter_data['include_user_ids'], $user_options );
		$filter_data['selected_include_user_options'] = Misc::arrayIntersectByKey( (array)$filter_data['include_user_ids'], $user_options );

		//Get exclude employee list
		$exclude_user_options = Misc::prependArray( $all_array_option, $ulf->getArrayByListFactory( $ulf, FALSE, TRUE ) );
		$filter_data['src_exclude_user_options'] = Misc::arrayDiffByKey( (array)$filter_data['exclude_user_ids'], $user_options );
		$filter_data['selected_exclude_user_options'] = Misc::arrayIntersectByKey( (array)$filter_data['exclude_user_ids'], $user_options );

		//Get employee status list.
		$user_status_options = Misc::prependArray( $all_array_option, $ulf->getOptions('status') );
		$filter_data['src_user_status_options'] = Misc::arrayDiffByKey( (array)$filter_data['user_status_ids'], $user_status_options );
		$filter_data['selected_user_status_options'] = Misc::arrayIntersectByKey( (array)$filter_data['user_status_ids'], $user_status_options );

		//Get Employee Groups
		$uglf = new UserGroupListFactory();
		$group_options = Misc::prependArray( $all_array_option, $uglf->getArrayByNodes( FastTree::FormatArray( $uglf->getByCompanyIdArray( $current_company->getId() ), 'TEXT', TRUE) ) );
		$filter_data['src_group_options'] = Misc::arrayDiffByKey( (array)$filter_data['group_ids'], $group_options );
		$filter_data['selected_group_options'] = Misc::arrayIntersectByKey( (array)$filter_data['group_ids'], $group_options );

		//Get branches
		$blf = new BranchListFactory();
		$blf->getByCompanyId( $current_company->getId() );
		$branch_options = Misc::prependArray( $all_array_option, $blf->getArrayByListFactory( $blf, FALSE, TRUE ) );
		$filter_data['src_branch_options'] = Misc::arrayDiffByKey( (array)$filter_data['branch_ids'], $branch_options );
		$filter_data['selected_branch_options'] = Misc::arrayIntersectByKey( (array)$filter_data['branch_ids'], $branch_options );

		//Get departments
		$dlf = new DepartmentListFactory();
		$dlf->getByCompanyId( $current_company->getId() );
		$department_options = Misc::prependArray( $all_array_option, $dlf->getArrayByListFactory( $dlf, FALSE, TRUE ) );
		$filter_data['src_department_options'] = Misc::arrayDiffByKey( (array)$filter_data['department_ids'], $department_options );
		$filter_data['selected_department_options'] = Misc::arrayIntersectByKey( (array)$filter_data['department_ids'], $department_options );

		//Get employee titles
		$utlf = new UserTitleListFactory();
		$utlf->getByCompanyId( $current_company->getId() );
		$user_title_options = Misc::prependArray( $all_array_option, $utlf->getArrayByListFactory( $utlf, FALSE, TRUE ) );
		$filter_data['src_user_title_options'] = Misc::arrayDiffByKey( (array)$filter_data['user_title_ids'], $user_title_options );
		$filter_data['selected_user_title_options'] = Misc::arrayIntersectByKey( (array)$filter_data['user_title_ids'], $user_title_options );
		
        //Get Leave Years absence_leave_user
		$alulf = new AbsenceLeaveUserListFactory();
		$alulf->getAllLeaveYear();
		$leave_year_options = Misc::prependArray( $all_array_option, $alulf->getArrayByListFactory( $alulf, FALSE, TRUE ) );
		$filter_data['src_leave_year_options'] = Misc::arrayDiffByKey( (array)$filter_data['leave_year_ids'], $leave_year_options );
		$filter_data['selected_leave_year_options'] = Misc::arrayIntersectByKey( (array)$filter_data['leave_year_ids'], $leave_year_options );
//                echo '<pre>'; print_r($filter_data['selected_leave_year_options']); die;

		//Get column list
		$filter_data['src_column_options'] = Misc::arrayDiffByKey( (array)$filter_data['column_ids'], $columns );
		$filter_data['selected_column_options'] = Misc::arrayIntersectByKey( (array)$filter_data['column_ids'], $columns );


		//Get primary/secondary order list
		$filter_data['sort_options'] = $columns;
		$filter_data['sort_direction_options'] = Misc::getSortDirectionArray();

		$filter_data['group_by_options'] = Misc::prependArray( array('0' => TTi18n::gettext('No Grouping')), $static_columns );

		$saved_report_options = $ugdlf->getByUserIdAndScriptArray( $current_user->getId(), $_SERVER['SCRIPT_NAME']);
		$generic_data['saved_report_options'] = $saved_report_options;
		$smarty->assign_by_ref('generic_data', $generic_data);


		$smarty->assign_by_ref('filter_data', $filter_data);

		$smarty->assign_by_ref('ugdf', $ugdf);

		$smarty->display('report/LeaveBalanceSummary.tpl');

		break;
}
?>