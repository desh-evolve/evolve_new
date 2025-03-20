<?php
/*********************************************************************************
 * TimeTrex is a Payroll and Time Management program developed by
 * TimeTrex Payroll Services Copyright (C) 2003 - 2012 TimeTrex Payroll Services.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by
 * the Free Software Foundation with the addition of the following permission
 * added to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED
 * WORK IN WHICH THE COPYRIGHT IS OWNED BY TIMETREX, TIMETREX DISCLAIMS THE
 * WARRANTY OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License along
 * with this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 *
 * You can contact TimeTrex headquarters at Unit 22 - 2475 Dobbin Rd. Suite
 * #292 Westbank, BC V4T 2E9, Canada or at email address info@timetrex.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU Affero General Public License
 * version 3, these Appropriate Legal Notices must retain the display of the
 * "Powered by TimeTrex" logo. If the display of the logo is not reasonably
 * feasible for technical reasons, the Appropriate Legal Notices must display
 * the words "Powered by TimeTrex".
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: EditUserWage.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */


/*******************************************************************************
 * 
 * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
 * I COPIED THIS CODE FROM PATH:- evolvepayroll\interface\users\EditUserWage.php
 * THIS CODE ADDED BY ME
 * CREATE USERES JOB HISTORY
 * 
 *******************************************************************************/



require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

//Debug::setVerbosity( 11 );

if ( !$permission->Check('wage','enabled')
		OR !( $permission->Check('wage','edit') OR $permission->Check('wage','edit_child') OR $permission->Check('wage','edit_own') OR $permission->Check('wage','add') ) ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'Edit Employee Job History')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'user_id',
												'saved_search_id',
												'job_history_data'
												) ) );

//ARSP NOTE --> I MODIFIED THIS CODE FOR THUNDER & NEON 
if ( isset($job_history_data) ) {
	if ( $job_history_data['first_worked_date'] != '' ) {
		$job_history_data['first_worked_date'] = TTDate::parseDateTime($job_history_data['first_worked_date']);
	}
}

//ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON 
if ( isset($job_history_data) ) {
	if ( $job_history_data['last_worked_date'] != '' ) {
		$job_history_data['last_worked_date'] = TTDate::parseDateTime($job_history_data['last_worked_date']);
	}
}

//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
$hlf = new HierarchyListFactory();
$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );

$ujf = new UserJobFactory();

$ulf = new UserListFactory();

$action = Misc::findSubmitButton();
$action = strtolower($action);
switch ($action) {
	case 'submit':
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$ulf->getByIdAndCompanyId($user_id, $current_company->getId() );
		if ( $ulf->getRecordCount() > 0 ) {
			$user_obj = $ulf->getCurrent();

			$is_owner = $permission->isOwner( $user_obj->getCreatedBy(), $user_obj->getID() );
			$is_child = $permission->isChild( $user_obj->getId(), $permission_children_ids );
			if ( $permission->Check('wage','edit')
					OR ( $permission->Check('wage','edit_own') AND $is_owner === TRUE )
					OR ( $permission->Check('wage','edit_child') AND $is_child === TRUE ) ) {
                            
                                $ujf->setFirstWorkedDate($job_history_data['first_worked_date']);
                                $ujf->setLastWorkedDate($job_history_data['last_worked_date']);
                            
				$ujf->setId($job_history_data['id']);
				$ujf->setUser($user_id);        
                                $ujf->setDefaultBranch($job_history_data['default_branch_id']);
                                $ujf->setDefaultDepartment($job_history_data['default_department_id']);
                                $ujf->setTitle($job_history_data['title_id']);
				$ujf->setNote( $job_history_data['note'] );
                                
				if ( $ujf->isValid() ) {
					$ujf->Save();

					Redirect::Page( URLBuilder::getURL( array('user_id' => $user_id, 'saved_search_id' => $saved_search_id), 'UserJobHistoryList.php') );

					break;
				}
			} else {
				$permission->Redirect( FALSE ); //Redirect
				exit;
			}
		}
	default:
		if ( isset($id) ) {
			BreadCrumb::setCrumb($title);
                        
                        $uwlf = new UserJobListFactory();
                        
			$uwlf->getByIdAndCompanyId($id, $current_company->getId() );

			foreach ($uwlf as $wage) {
                            
				$user_obj = $ulf->getByIdAndCompanyId( $wage->getUser(), $current_company->getId() )->getCurrent();
                                //print_r($user_obj);
				if ( is_object($user_obj) ) {
					$is_owner = $permission->isOwner( $user_obj->getCreatedBy(), $user_obj->getID() );
                                        //echo $is_owner;
					$is_child = $permission->isChild( $user_obj->getId(), $permission_children_ids );
                                        //echo $is_owner;

					if ( $permission->Check('wage','edit')
							OR ( $permission->Check('wage','edit_own') AND $is_owner === TRUE )
							OR ( $permission->Check('wage','edit_child') AND $is_child === TRUE ) ) {

						$user_id = $wage->getUser();

						//Debug::Text('Labor Burden Hourly Rate: '. $wage->getLaborBurdenHourlyRate( $wage->getHourlyRate() ), __FILE__, __LINE__, __METHOD__,10);
						$job_history_data = array(       
                                                    
											'id' => $wage->getId(),
											'user_id' => $wage->getUser(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'default_branch_id' => $wage->getDefaultBranch(),     
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'default_department_id' => $wage->getDefaultDepartment(), 
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'title_id' => $wage->getTitle(), 
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'first_worked_date' => $wage->getFirstWorkedDate(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'last_worked_date' => $wage->getLastWorkedDate(),                                                                                                 
											'note' => $wage->getNote(),
											'created_date' => $wage->getCreatedDate(),
											'created_by' => $wage->getCreatedBy(),
											'updated_date' => $wage->getUpdatedDate(),
											'updated_by' => $wage->getUpdatedBy(),
											'deleted_date' => $wage->getDeletedDate(),
											'deleted_by' => $wage->getDeletedBy()
										);
                                                //print_r($wage_data);
					} else {
						$permission->Redirect( FALSE ); //Redirect
						exit;
					}
				}
			}
		} else {
			if ( $action != 'submit' ) {                        
                            
                            $ulf = new UserListFactory();
                            $temp_default_branch_id  = $ulf->getByIdAndCompanyId( $user_id, $current_company->getId() )->getCurrent()->getDefaultBranch();                            
                            $temp_default_department_id  = $ulf->getByIdAndCompanyId( $user_id, $current_company->getId() )->getCurrent()->getDefaultDepartment();
                            $temp_title_id  = $ulf->getByIdAndCompanyId( $user_id, $current_company->getId() )->getCurrent()->getTitle();
                            
                            
                            //ARSP NOTE --> I MODIFIED THISC CODE
                            $job_history_data = array( 'first_worked_date' => TTDate::getTime(), 'default_branch_id' => $temp_default_branch_id, 'default_department_id' => $temp_default_department_id, 'title_id' => $temp_title_id );
			}
		}
                
                
                //ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON 
		//Select box options;
		$blf = new BranchListFactory();
		$job_history_data['branch_options'] = $blf->getByCompanyIdArray( $current_company->getId() );
                
                //ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON 
                //Select box options;
		$dlf = new DepartmentListFactory();
		$job_history_data['department_options'] = $dlf->getByCompanyIdArray( $current_company->getId() );  
                
                //ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON 
                //Select box options;
		$utlf = new UserTitleListFactory();
		$job_history_data['title_options'] = $utlf->getByCompanyIdArray( $current_company->getId() );

                
		$ulf = new UserListFactory();
		$ulf->getByIdAndCompanyId( $user_id, $current_company->getId() );
		$user_data = $ulf->getCurrent();

                
		//Get pay period boundary dates for this user.
		//Include user hire date in the list.
		$pay_period_boundary_dates[TTDate::getDate('DATE', $user_data->getHireDate() )] = TTi18n::gettext('(Appointment Date)').' '. TTDate::getDate('DATE', $user_data->getHireDate() );
		$pay_period_boundary_dates = Misc::prependArray( array(-1 => TTi18n::gettext('(Choose Date)')), $pay_period_boundary_dates);

		$smarty->assign_by_ref('user_data', $user_data);
		$smarty->assign_by_ref('job_history_data', $job_history_data);

		$smarty->assign_by_ref('tmp_effective_date', $tmp_effective_date);
		$smarty->assign_by_ref('pay_period_boundary_date_options', $pay_period_boundary_dates);

		$smarty->assign_by_ref('saved_search_id', $saved_search_id);


		break;
}

$smarty->assign_by_ref('ujf', $ujf);

$smarty->display('users/EditUserJobHistory.tpl');
?>