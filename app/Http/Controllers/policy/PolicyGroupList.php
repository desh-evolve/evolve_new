<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: PolicyGroupList.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('policy_group','enabled')
		OR !( $permission->Check('policy_group','view') OR $permission->Check('policy_group','view_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect

}

$smarty->assign('title', TTi18n::gettext($title = 'Policy Group List')); // See index.php
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
												'ids',
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

		Redirect::Page( URLBuilder::getURL( NULL, 'EditPolicyGroup.php', FALSE) );

		break;
	case 'delete' OR 'undelete':
		if ( strtolower($action) == 'delete' ) {
			$delete = TRUE;
		} else {
			$delete = FALSE;
		}

		$pglf = TTnew( 'PolicyGroupListFactory' );

		foreach ($ids as $id) {
			$pglf->getByIdAndCompanyId($id, $current_company->getId() );
			foreach ($pglf as $pg_obj) {
				$pg_obj->setDeleted($delete);
				$pg_obj->Save();
			}
		}

		Redirect::Page( URLBuilder::getURL( NULL, 'PolicyGroupList.php') );

		break;

	default:
		$pglf = TTnew( 'PolicyGroupListFactory' );
		$pglf->getByCompanyId( $current_company->getId() );

		$pager = new Pager($pglf);

		foreach ($pglf as $pg_obj) {

			$policies[] = array(
								'id' => $pg_obj->getId(),
								'name' => $pg_obj->getName(),
								'deleted' => $pg_obj->getDeleted()
							);

		}
		$smarty->assign_by_ref('policies', $policies);

		$smarty->assign_by_ref('sort_column', $sort_column );
		$smarty->assign_by_ref('sort_order', $sort_order );

		$smarty->assign_by_ref('paging_data', $pager->getPageVariables() );

		break;
}
$smarty->display('policy/PolicyGroupList.tpl');
?>