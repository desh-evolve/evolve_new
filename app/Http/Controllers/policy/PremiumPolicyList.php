<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 5519 $
 * $Id: PremiumPolicyList.php 5519 2011-11-15 19:28:49Z ipso $
 * $Date: 2011-11-15 11:28:49 -0800 (Tue, 15 Nov 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('premium_policy','enabled')
		OR !( $permission->Check('premium_policy','view') OR $permission->Check('premium_policy','view_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect

}

$smarty->assign('title', TTi18n::gettext($title = 'Premium Policy List')); // See index.php

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

		Redirect::Page( URLBuilder::getURL( NULL, 'EditPremiumPolicy.php', FALSE) );

		break;
	case 'delete':
	case 'undelete':
		if ( strtolower($action) == 'delete' ) {
			$delete = TRUE;
		} else {
			$delete = FALSE;
		}

		$pplf = TTnew( 'PremiumPolicyListFactory' );

		foreach ($ids as $id) {
			$pplf->getByIdAndCompanyId($id, $current_company->getId() );

			foreach ($pplf as $pp_obj) {
				$pp_obj->setDeleted($delete);
				if ( $pp_obj->isValid() ) {
					$pp_obj->Save();
				}
			}
		}

		Redirect::Page( URLBuilder::getURL( NULL, 'PremiumPolicyList.php') );

		break;

	default:
		BreadCrumb::setCrumb($title);
		$pplf = TTnew( 'PremiumPolicyListFactory' );
		$pplf->getByCompanyId( $current_company->getId() );

		$pager = new Pager($pplf);

		$type_options = $pplf->getOptions('type');

 		$show_no_policy_group_notice = FALSE;
		foreach ($pplf as $pp_obj) {
			if ( (int)$pp_obj->getColumn('assigned_policy_groups') == 0 ) {
				$show_no_policy_group_notice = TRUE;
			}

			$policies[] = array(
								'id' => $pp_obj->getId(),
								'name' => $pp_obj->getName(),
								'type_id' => $pp_obj->getType(),
								'type' => $type_options[$pp_obj->getType()],
								//'trigger_time' => $pp_obj->getTriggerTime(),
								'assigned_policy_groups' => (int)$pp_obj->getColumn('assigned_policy_groups'),
								'deleted' => $pp_obj->getDeleted()
							);

		}
		$smarty->assign_by_ref('policies', $policies);

		$smarty->assign_by_ref('show_no_policy_group_notice', $show_no_policy_group_notice );

		$smarty->assign_by_ref('sort_column', $sort_column );
		$smarty->assign_by_ref('sort_order', $sort_order );

		$smarty->assign_by_ref('paging_data', $pager->getPageVariables() );

		break;
}
$smarty->display('policy/PremiumPolicyList.tpl');
?>