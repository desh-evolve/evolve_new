<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 1919 $
 * $Id: UserTitleList.php 1919 2008-06-13 18:17:17Z ipso $
 * $Date: 2008-06-13 11:17:17 -0700 (Fri, 13 Jun 2008) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('wage','enabled')
		OR !( $permission->Check('wage','view') ) ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'Wage Group List')); // See index.php
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

Debug::Arr($ids,'Selected Objects', __FILE__, __LINE__, __METHOD__,10);

$action = Misc::findSubmitButton();
switch ($action) {
	case 'add':

		Redirect::Page( URLBuilder::getURL(NULL, 'EditWageGroup.php') );

		break;
	case 'delete':
	case 'undelete':
		if ( strtolower($action) == 'delete' ) {
			$delete = TRUE;
		} else {
			$delete = FALSE;
		}

		$wglf = new WageGroupListFactory();
		foreach ($ids as $id) {
			$wglf->getByIdAndCompanyId($id, $current_company->getId() );
			foreach ($wglf as $wg_obj) {
				$wg_obj->setDeleted($delete);
				if ( $wg_obj->isValid() == TRUE ) {
					$wg_obj->Save();
				}
			}
		}

		Redirect::Page( URLBuilder::getURL(NULL, 'WageGroupList.php') );

		break;

	default:
		$sort_array = NULL;
		if ( $sort_column != '' ) {
			$sort_array = array(Misc::trimSortPrefix($sort_column) => $sort_order);
		}

		$wglf = new WageGroupListFactory();
		$wglf->getByCompanyId($current_company->getId(), $current_user_prefs->getItemsPerPage(),$page, NULL, $sort_array );

		$pager = new Pager($wglf);

		foreach ($wglf as $group_obj) {

			$groups[] = array(
								'id' => $group_obj->getId(),
								'name' => $group_obj->getName(),
								'deleted' => $group_obj->getDeleted()
							);

		}
		$smarty->assign_by_ref('groups', $groups);

		$smarty->assign_by_ref('sort_column', $sort_column );
		$smarty->assign_by_ref('sort_order', $sort_order );

		$smarty->assign_by_ref('paging_data', $pager->getPageVariables() );

		break;
}
$smarty->display('company/WageGroupList.tpl');
?>