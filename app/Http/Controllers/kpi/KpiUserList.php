<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 5457 $
 * $Id: UserWageList.php 5457 2011-11-04 20:49:58Z ipso $
 * $Date: 2011-11-04 13:49:58 -0700 (Fri, 04 Nov 2011) $
 */



/*******************************************************************************
 * 
 * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
 * I COPIED SOME THIS CODE FROM PATH:- evolvepayroll\interface\users\UserWageList.php
 * THIS CODE ADDED BY ME
 * CREATE USERES KPI
 * 
 *******************************************************************************/

require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('wage','enabled')
		OR !( $permission->Check('wage','view') OR $permission->Check('wage','view_child') OR $permission->Check('wage','view_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect

}

$smarty->assign('title', TTi18n::gettext($title = 'Employee KPI List')); // See index.php
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

		Redirect::Page( URLBuilder::getURL(array('user_id' => $user_id, 'saved_search_id' => $saved_search_id ), 'EditUserKpiOld.php', FALSE) );

		break;
	case 'delete' OR 'undelete':
		if ( strtolower($action) == 'delete' ) {
			$delete = TRUE;
		} else {
			$delete = FALSE;
		}

		//$uwlf = new UserWageListFactory();
                $uklf = new UserKpiListFactory();

		if ( $ids != '' ) {
			foreach ($ids as $id) {
				$uklf->getByIdAndCompanyId($id, $current_company->getId() );
				foreach ($uklf as $kpi) {
					$kpi->setDeleted($delete);
					$kpi->Save();
				}
			}
		}

		Redirect::Page( URLBuilder::getURL(array('user_id' => $user_id), 'KpiUserList.php') );

		break;

	default:
		//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
		$user_has_default_wage = FALSE;

		$hlf = new HierarchyListFactory();
		$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );
		Debug::Arr($permission_children_ids,'Permission Children Ids:', __FILE__, __LINE__, __METHOD__,10);

		//$uwlf = new UserWageListFactory();
                //$ujlf = new UserJobListFactory();
                $uklf = new UserKpiListFactory();
		$uklf->GetByUserIdAndCompanyId($user_id, $current_company->getId(), $current_user_prefs->getItemsPerPage(), $page, NULL, $sort_array );

		$pager = new Pager($uklf);

		//$wglf = new WageGroupListFactory();
		//$wage_group_options = $wglf->getArrayByListFactory( $wglf->getByCompanyId( $current_company->getId() ), TRUE );
                
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

				foreach ($uklf as $kpi) {
					$kpi_history[] = array(
										'id' => $kpi->getId(),
										'user_id' => $kpi->getUser(),
										//'wage_group_id' => $wage->getWageGroup(),
										//'wage_group' => Option::getByKey($wage->getWageGroup(), $wage_group_options ),
										//'type' => Option::getByKey($wage->getType(), $wage->getOptions('type') ),
										//'wage' => Misc::MoneyFormat( Misc::removeTrailingZeros($wage->getWage()), TRUE ),
										//'currency_symbol' => $currency_symbol,
										//'effective_date' => TTDate::getDate( 'DATE', $wage->getEffectiveDate() ),
                                            
                       ///* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'default_branch_id' => Option::getByKey($job->getDefaultBranch(), $branch_options ), $job->getDefaultBranch(),     
                       ///* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'default_department_id' => Option::getByKey($job->getDefaultDepartment(), $department_options ),$job->getDefaultDepartment(), 
                       /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'title_id' => Option::getByKey($kpi->getTitle(), $title_options ), $kpi->getTitle(), 
                       /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'start_date' => TTDate::getDate( 'DATE', $kpi->getStartDate() ),
                       /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'end_date' => TTDate::getDate( 'DATE', $kpi->getEndDate() ),  
                       /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'review_date' => TTDate::getDate( 'DATE', $kpi->getReviewDate() ),                                                                                                 
                                            
                       ///* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'note' => $job->getNote(),                                              
										'is_owner' => $is_owner,
										'is_child' => $is_child,
										'deleted' => $kpi->getDeleted()
									);
                                         

					//if ( $wage->getWageGroup() == 0 ) {
					//	$user_has_default_wage = TRUE;
					//}
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

		$smarty->assign_by_ref('kpi_history', $kpi_history);
		$smarty->assign_by_ref('user_id', $user_id );
		//$smarty->assign_by_ref('user_has_default_wage', $user_has_default_wage );

		$smarty->assign_by_ref('saved_search_id', $saved_search_id );
		$smarty->assign_by_ref('sort_column', $sort_column );
		$smarty->assign_by_ref('sort_order', $sort_order );

		$smarty->assign_by_ref('paging_data', $pager->getPageVariables() );

		break;
}
$smarty->display('kpi/KpiUserList.tpl');
?>