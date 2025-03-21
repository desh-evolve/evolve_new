<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: AccrualPolicyList.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('accrual_policy','enabled')
		OR !( $permission->Check('accrual_policy','view') OR $permission->Check('accrual_policy','view_own') ) ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', __($title = 'Accrual Policy List')); // See index.php
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

		Redirect::Page( URLBuilder::getURL( NULL, 'EditAccrualPolicy.php', FALSE) );

		break;
	case 'delete':
	case 'undelete':
		if ( strtolower($action) == 'delete' ) {
			$delete = TRUE;
		} else {
			$delete = FALSE;
		}

		$aplf = new AccrualPolicyListFactory();

		foreach ($ids as $id) {
			$aplf->getByIdAndCompanyId($id, $current_company->getId() );

			$aplf->StartTransaction();

			foreach ($aplf as $ap_obj) {
				$ap_obj->setDeleted($delete);
				if ( $ap_obj->isValid() ) {
					$ap_obj->Save();
				}
			}

			$aplf->CommitTransaction();
		}

		Redirect::Page( URLBuilder::getURL( NULL, 'AccrualPolicyList.php') );

		break;

	default:
		$aplf = new AccrualPolicyListFactory();
		$aplf->getByCompanyId( $current_company->getId() );

		$pager = new Pager($aplf);

		$type_options = $aplf->getOptions('type');

 		$show_no_policy_group_notice = FALSE;
		foreach ($aplf as $ap_obj) {
			if ( (int)$ap_obj->getColumn('assigned_policy_groups') == 0 ) {
				$show_no_policy_group_notice = TRUE;
			}

			$policies[] = array(
								'id' => $ap_obj->getId(),
								'name' => $ap_obj->getName(),
								'type_id' => $ap_obj->getType(),
								'type' => $type_options[$ap_obj->getType()],
								'assigned_policy_groups' => (int)$ap_obj->getColumn('assigned_policy_groups'),
								'deleted' => $ap_obj->getDeleted()
							);

		}
		$smarty->assign_by_ref('policies', $policies);

		$smarty->assign_by_ref('show_no_policy_group_notice', $show_no_policy_group_notice );

		$smarty->assign_by_ref('sort_column', $sort_column );
		$smarty->assign_by_ref('sort_order', $sort_order );

		$smarty->assign_by_ref('paging_data', $pager->getPageVariables() );

		break;
}
$smarty->display('policy/AccrualPolicyList.tpl');
?>