<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: ExceptionPolicyControlList.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('exception_policy','enabled')
		OR !( $permission->Check('exception_policy','view') OR $permission->Check('exception_policy','view_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect
}

//Debug::setVerbosity(11);

$smarty->assign('title', __($title = 'Exception Policy List')); // See index.php
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

		Redirect::Page( URLBuilder::getURL( NULL, 'EditExceptionPolicyControl.php', FALSE) );

		break;
	case 'delete' OR 'undelete':
		if ( strtolower($action) == 'delete' ) {
			$delete = TRUE;
		} else {
			$delete = FALSE;
		}

		$epclf = new ExceptionPolicyControlListFactory();

		foreach ($ids as $id) {
			$epclf->getByIdAndCompanyId($id, $current_company->getId() );
			foreach ($epclf as $epc_obj) {
				$epc_obj->setDeleted($delete);
				if ( $epc_obj->isValid() ) {
					$epc_obj->Save();
				}
			}
		}

		Redirect::Page( URLBuilder::getURL( NULL, 'ExceptionPolicyControlList.php') );

		break;

	default:
		$epclf = new ExceptionPolicyControlListFactory();
		$epclf->getByCompanyId( $current_company->getId() );

		$pager = new Pager($epclf);

		$show_no_policy_group_notice = FALSE;
		foreach ($epclf as $epc_obj) {
			if ( (int)$epc_obj->getColumn('assigned_policy_groups') == 0 ) {
				$show_no_policy_group_notice = TRUE;
			}

			$policies[] = array(
								'id' => $epc_obj->getId(),
								'name' => $epc_obj->getName(),
								'assigned_policy_groups' => (int)$epc_obj->getColumn('assigned_policy_groups'),
								'deleted' => $epc_obj->getDeleted()
							);

		}
		$smarty->assign_by_ref('policies', $policies);

		$smarty->assign_by_ref('show_no_policy_group_notice', $show_no_policy_group_notice );

		$smarty->assign_by_ref('sort_column', $sort_column );
		$smarty->assign_by_ref('sort_order', $sort_order );

		$smarty->assign_by_ref('paging_data', $pager->getPageVariables() );

		break;
}
$smarty->display('policy/ExceptionPolicyControlList.tpl');
?>