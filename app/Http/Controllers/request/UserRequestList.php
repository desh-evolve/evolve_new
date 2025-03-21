<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4207 $
 * $Id: UserRequestList.php 4207 2011-02-02 00:54:08Z ipso $
 * $Date: 2011-02-01 16:54:08 -0800 (Tue, 01 Feb 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('request','enabled')
		OR !( $permission->Check('request','view') OR $permission->Check('request','view_own') OR $permission->Check('request','view_child') ) ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', __($title = 'Request List')); // See index.php
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
												'filter_user_id',
												'filter_start_date',
												'filter_end_date',
												'ids',
												) ) );

URLBuilder::setURL($_SERVER['SCRIPT_NAME'],
											array(
													'filter_user_id' => $filter_user_id,
													'filter_start_date' => $filter_start_date,
													'filter_end_date' => $filter_end_date,
													'sort_column' => $sort_column,
													'sort_order' => $sort_order,
													'page' => $page
												) );

$sort_array = NULL;
if ( $sort_column != '' ) {
	$sort_array = array($sort_column => $sort_order);
}

$filter_data = array();
//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
$permission_children_ids = array();
if ( $permission->Check('request','view') == FALSE ) {
	$hlf = new HierarchyListFactory();
	$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );

	if ( $permission->Check('request','view_child') == FALSE ) {
		$permission_children_ids = array();
	}
	if ( $permission->Check('request','view_own') ) {
		$permission_children_ids[] = $current_user->getId();
	}

	$filter_data['permission_children_ids'] = $permission_children_ids;
}

Debug::Arr($ids,'Selected Objects', __FILE__, __LINE__, __METHOD__,10);

$action = Misc::findSubmitButton();
switch ($action) {
	case 'add':
		//Should have a pop-up
		//Redirect::Page( URLBuilder::getURL( NULL, 'EditRequest.php', FALSE) );

		break;
	case 'delete':
	case 'undelete':
		//Debug::setVerbosity(11);
		if ( strtolower($action) == 'delete' ) {
			$delete = TRUE;
		} else {
			$delete = FALSE;
		}

		$rlf = new RequestListFactory();

		foreach ($ids as $id) {
			$rlf->getByIdAndCompanyId( $id, $current_company->getId() );
			foreach ($rlf as $r_obj) {
				$r_obj->setDeleted($delete);
				$r_obj->Save();
			}
		}

		Redirect::Page( URLBuilder::getURL( array( 'filter_user_id' => $filter_user_id ), 'UserRequestList.php') );

		break;

	default:
		if ( !isset($filter_user_id) ) {
			$filter_user_id = $user_id = $current_user->getId();
		}

		if ( isset($filter_user_id) ) {
			$filter_data['user_id'] = $filter_user_id;
		}

		if ( isset($filter_start_date) AND $filter_start_date != '' AND isset($filter_end_date) AND $filter_end_date != '') {
			$filter_data['start_date'] = $filter_start_date;
			$filter_data['end_date'] = $filter_end_date;
		}

		$rlf = new RequestListFactory();
		$rlf->getByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data, $current_user_prefs->getItemsPerPage(), $page, NULL, $sort_array );

		/*
		if ( isset($filter_start_date) AND $filter_start_date != '' AND isset($filter_end_date) AND $filter_end_date != '') {
			$rlf->getByUserIdAndCompanyIdAndStartDateAndEndDate( $user_id, $current_company->getId(), $filter_start_date, $filter_end_date, $current_user_prefs->getItemsPerPage(), $page, NULL, $sort_array );
		} else {
			$rlf->getByUserIDAndCompanyId( $user_id, $current_company->getId(), $current_user_prefs->getItemsPerPage(), $page, NULL, $sort_array );
		}
		*/

		$pager = new Pager($rlf);

		$status_options = $rlf->getOptions('status');
		$type_options = $rlf->getOptions('type');

		foreach ($rlf as $r_obj) {
			Debug::Text('Status ID: '. $r_obj->getStatus() .' Status: '. $status_options[$r_obj->getStatus()], __FILE__, __LINE__, __METHOD__,10);
			$requests[] = array(
								'id' => $r_obj->getId(),
								'user_date_id' => $r_obj->getUserDateID(),
								'date_stamp' => TTDate::strtotime($r_obj->getColumn('date_stamp')),
								'status_id' => $r_obj->getStatus(),
								'status' => $status_options[$r_obj->getStatus()],
								'type_id' => $r_obj->getType(),
								'type' => $type_options[$r_obj->getType()],
								'created_date' => $r_obj->getCreatedDate(),
								'deleted' => $r_obj->getDeleted()
							);

		}

		$ulf = new UserListFactory();
		$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
		$user_options = UserListFactory::getArrayByListFactory( $ulf, FALSE, FALSE );

		$smarty->assign_by_ref('user_options', $user_options);
		$smarty->assign_by_ref('requests', $requests);

		$smarty->assign_by_ref('filter_user_id', $filter_user_id);

		$smarty->assign_by_ref('sort_column', $sort_column );
		$smarty->assign_by_ref('sort_order', $sort_order );

		$smarty->assign_by_ref('paging_data', $pager->getPageVariables() );

		break;
}
$smarty->display('request/UserRequestList.tpl');
?>