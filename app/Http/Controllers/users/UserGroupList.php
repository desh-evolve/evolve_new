<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: UserGroupList.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('user','enabled')
		OR !( $permission->Check('user','view') ) ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'Employee Group List')); // See index.php
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
												'ids'
												) ) );

URLBuilder::setURL($_SERVER['SCRIPT_NAME'],
											array(
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

		Redirect::Page( URLBuilder::getURL( NULL, 'EditUserGroup.php', FALSE) );

		break;
	case 'delete' OR 'undelete':
		if ( strtolower($action) == 'delete' ) {
			$delete = TRUE;
		} else {
			$delete = FALSE;
		}

		$uglf = TTnew( 'UserGroupListFactory' );

		foreach ($ids as $id) {
			$uglf->getById($id );
			foreach ($uglf as $obj) {
				$obj->setDeleted($delete);
				$obj->Save();
			}
		}

		Redirect::Page( URLBuilder::getURL( NULL, 'UserGroupList.php') );

		break;

	default:
		$uglf = TTnew( 'UserGroupListFactory' );

		$nodes = FastTree::FormatArray( $uglf->getByCompanyIdArray( $current_company->getId() ), 'HTML' );

		//For some reason smarty prints out a blank row if nodes is false.
		if ( $nodes !== FALSE ) {
			$smarty->assign_by_ref('rows', $nodes);
		}

		$smarty->assign_by_ref('sort_column', $sort_column );
		$smarty->assign_by_ref('sort_order', $sort_order );

		//$smarty->assign_by_ref('paging_data', $pager->getPageVariables() );

		break;
}
$smarty->display('users/UserGroupList.tpl');
?>