<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: UserSearch.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
//Debug::setVerbosity(11);

$skip_message_check = TRUE;
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('user','enabled')
		OR !( $permission->Check('user','view') OR $permission->Check('user','view_own') OR $permission->Check('user','view_child')) ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', __($title = 'Employee Search')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'src_element_id',
												'dst_element_id',
												'data'
												) ) );

$ulf = new UserListFactory();

$action = Misc::findSubmitButton();
$action = strtolower($action);
switch ($action) {
	case 'search':
		Debug::Text('Search!', __FILE__, __LINE__, __METHOD__,10);

		$hlf = new HierarchyListFactory();
		$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );
		Debug::Arr($permission_children_ids,'Permission Children Ids:', __FILE__, __LINE__, __METHOD__,10);
		if ( $permission->Check('user','view') == FALSE ) {
			if ( $permission->Check('user','view_child') ) {
				$data['permission_children_ids'] = $permission_children_ids;
			}
			if ( $permission->Check('user','view_own') ) {
				$data['permission_children_ids'][] = $current_user->getId();
			}
		}
		$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $data );

		//$ulf->getSearchByCompanyIdAndBranchIdAndDepartmentIdAndStatusId( $current_company->getId(), $data['branch_id'], $data['department_id'], $data['status_id']);
		$data['user_options'] = $ulf->getArrayByListFactory( $ulf, FALSE );
		if ( is_array($data['user_options']) ) {
			$data['filter_user_ids'] = array_keys($data['user_options']);
			$data['total_users'] = count($data['user_options']);
		}
		//var_dump($filter_user_ids);
	default:

		if ( isset($current_company) ) {
			$uglf = new UserGroupListFactory();
			$group_options = $uglf->getArrayByNodes( FastTree::FormatArray( $uglf->getByCompanyIdArray( $current_company->getId() ), 'TEXT', TRUE) );

			$blf = new BranchListFactory();
			$branch_options = $blf->getByCompanyIdArray( $current_company->getId() );

			$dlf = new DepartmentListFactory();
			$department_options = $dlf->getByCompanyIdArray( $current_company->getId() );
		}

		//Select box options;
		$data['status_options'] = $ulf->getOptions('status');
		$data['group_options'] = $group_options;
		$data['branch_options'] = $branch_options;
		$data['department_options'] = $department_options;

		if ( $action != 'search' ) {
			$data['status_id'] = array(10);
		}

		$smarty->assign_by_ref('data', $data);
		Debug::Text('SRC Element ID: '. $src_element_id, __FILE__, __LINE__, __METHOD__,10);
		$smarty->assign_by_ref('src_element_id', $src_element_id);
		$smarty->assign_by_ref('dst_element_id', $dst_element_id);

		break;
}
$smarty->display('users/UserSearch.tpl');
?>