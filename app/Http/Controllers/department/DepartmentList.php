<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: DepartmentList.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('department','enabled')
		OR !( $permission->Check('department','view') OR $permission->Check('department','view_own') ) ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'Department List')); // See index.php
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

		Redirect::Page( URLBuilder::getURL(NULL, 'EditDepartment.php') );

		break;
	case 'delete' OR 'undelete':
		if ( strtolower($action) == 'delete' ) {
			$delete = TRUE;
		} else {
			$delete = FALSE;
		}

		$dlf = TTnew( 'DepartmentListFactory' );

		foreach ($ids as $id) {
			$dlf->GetByIdAndCompanyId($id, $current_company->getId() );
			foreach ($dlf as $department) {
				$department->setDeleted($delete);
				$department->Save();
			}
		}

		Redirect::Page( URLBuilder::getURL(NULL, 'DepartmentList.php') );

		break;

	default:
		$sort_array = NULL;
		if ( $sort_column != '' ) {
			$sort_array = array(Misc::trimSortPrefix($sort_column) => $sort_order);
		}

		$dlf = TTnew( 'DepartmentListFactory' );
		$dlf->GetByCompanyId($current_company->getId(), $current_user_prefs->getItemsPerPage(), $page, NULL, $sort_array );

		$pager = new Pager($dlf);

		$departments = array();
		if ( $dlf->getRecordCount() > 0 ) {
			foreach ($dlf as $department) {
				$departments[] = array(
									'id' => $department->GetId(),
									'status_id' => $department->getStatus(),
									'manual_id' => $department->getManualID(),
									'name' => $department->getName(),
									'deleted' => $department->getDeleted()
								);
			}
		}

		$smarty->assign_by_ref('departments', $departments);

		$smarty->assign_by_ref('sort_column', $sort_column );
		$smarty->assign_by_ref('sort_order', $sort_order );

		$smarty->assign_by_ref('paging_data', $pager->getPageVariables() );

		break;
}
$smarty->display('department/DepartmentList.tpl');
?>