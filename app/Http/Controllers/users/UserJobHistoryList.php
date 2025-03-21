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
 * $Revision: 5457 $
 * $Id: UserWageList.php 5457 2011-11-04 20:49:58Z ipso $
 * $Date: 2011-11-04 13:49:58 -0700 (Fri, 04 Nov 2011) $
 */



/*******************************************************************************
 * 
 * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
 * I COPIED THIS CODE FROM PATH:- evolvepayroll\interface\users\UserWageList.php
 * THIS CODE ADDED BY ME
 * CREATE USERES JOB HISTORY
 * 
 *******************************************************************************/

require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('wage','enabled')
		OR !( $permission->Check('wage','view') OR $permission->Check('wage','view_child') OR $permission->Check('wage','view_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect

}

$smarty->assign('title', __($title = 'Employee Job History')); // See index.php
BreadCrumb::setCrumb($title);

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'page',
												'sort_column',
												'sort_order',
												'saved_search_id',
												'ids',
												'user_id'
												) ) );

$ulf = new UserListFactory();
//$ulf->getByIdAndCompanyId( $user_id, $current_company->getId() );
//$user_data = $ulf->getCurrent();
//$smarty->assign('title', $user_data->getFullName().'\'s Wage List' );

URLBuilder::setURL($_SERVER['SCRIPT_NAME'],
											array(
													'user_id' => $user_id,
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

		Redirect::Page( URLBuilder::getURL(array('user_id' => $user_id, 'saved_search_id' => $saved_search_id ), 'EditUserJobHistory.php', FALSE) );

		break;
	case 'delete' OR 'undelete':
		if ( strtolower($action) == 'delete' ) {
			$delete = TRUE;
		} else {
			$delete = FALSE;
		}
                
                $ujlf = new UserJobListFactory();

		if ( $ids != '' ) {
			foreach ($ids as $id) {
				$ujlf->getByIdAndCompanyId($id, $current_company->getId() );
				foreach ($ujlf as $job) {
					$job->setDeleted($delete);
					$job->Save();
				}
			}
		}

		Redirect::Page( URLBuilder::getURL(array('user_id' => $user_id), 'UserJobHistoryList.php') );

		break;

	default:
		//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
		$user_has_default_wage = FALSE;

		$hlf = new HierarchyListFactory();
		$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );
		Debug::Arr($permission_children_ids,'Permission Children Ids:', __FILE__, __LINE__, __METHOD__,10);

                $ujlf = new UserJobListFactory();
		$ujlf->GetByUserIdAndCompanyId($user_id, $current_company->getId(), $current_user_prefs->getItemsPerPage(), $page, NULL, $sort_array );

		$pager = new Pager($ujlf);

                
                //ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON 
		//Select box options;
		$blf = new BranchListFactory();
		$branch_options = $blf->getByCompanyIdArray( $current_company->getId() );
                
                //ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON 
                //Select box options;
		$dlf = new DepartmentListFactory();
		$department_options = $dlf->getByCompanyIdArray( $current_company->getId() );  
                
                //ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON 
                //Select box options;
		$utlf = new UserTitleListFactory();
		$title_options = $utlf->getByCompanyIdArray( $current_company->getId() );
                

		$user_obj = $ulf->getByIdAndCompanyId( $user_id, $current_company->getId() )->getCurrent();
		if ( is_object($user_obj) ) {
			$is_owner = $permission->isOwner( $user_obj->getCreatedBy(), $user_obj->getID() );
			$is_child = $permission->isChild( $user_obj->getId(), $permission_children_ids );


			if ( $permission->Check('wage','view')
					OR ( $permission->Check('wage','view_own') AND $is_owner === TRUE )
					OR ( $permission->Check('wage','view_child') AND $is_child === TRUE ) ) {

				foreach ($ujlf as $job) {
					$job_history[] = array(
										'id' => $job->getId(),
										'user_id' => $job->getUser(),                                            
                       /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'default_branch_id' => Option::getByKey($job->getDefaultBranch(), $branch_options ), $job->getDefaultBranch(),     
                       /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'default_department_id' => Option::getByKey($job->getDefaultDepartment(), $department_options ),$job->getDefaultDepartment(), 
                       /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'title_id' => Option::getByKey($job->getTitle(), $title_options ), $job->getTitle(), 
                       /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'first_worked_date' => TTDate::getDate( 'DATE', $job->getFirstWorkedDate() ),
                       /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'last_worked_date' => TTDate::getDate( 'DATE', $job->getLastWorkedDate() ),                                                                                                 
                       ///* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'note' => $job->getNote(),                                              
										'is_owner' => $is_owner,
										'is_child' => $is_child,
										'deleted' => $job->getDeleted()
									);

				}
//                                print_r($wages);                         
         
			}
		}

		$ulf = new UserListFactory();

		$filter_data = NULL;
		extract( UserGenericDataFactory::getSearchFormData( $saved_search_id, NULL ) );

		if ( $permission->Check('wage','view') == FALSE ) {
			if ( $permission->Check('wage','view_child') ) {
				$filter_data['permission_children_ids'] = $permission_children_ids;
			}
			if ( $permission->Check('wage','view_own') ) {
				$filter_data['permission_children_ids'][] = $current_user->getId();
			}
		}

		$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );

		$user_options = UserListFactory::getArrayByListFactory( $ulf, FALSE, TRUE );

		$smarty->assign_by_ref('user_options', $user_options);

		$smarty->assign_by_ref('job_history', $job_history);
		$smarty->assign_by_ref('user_id', $user_id );

		$smarty->assign_by_ref('saved_search_id', $saved_search_id );
		$smarty->assign_by_ref('sort_column', $sort_column );
		$smarty->assign_by_ref('sort_order', $sort_order );

		$smarty->assign_by_ref('paging_data', $pager->getPageVariables() );

		break;
}
$smarty->display('users/UserJobHistoryList.tpl');
?>