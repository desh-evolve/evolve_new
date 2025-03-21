<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 2035 $
 * $Id: BreakPolicyList.php 2035 2008-08-08 20:36:51Z ipso $
 * $Date: 2008-08-08 13:36:51 -0700 (Fri, 08 Aug 2008) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('break_policy','enabled')
		OR !( $permission->Check('break_policy','view') OR $permission->Check('break_policy','view_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect

}

$smarty->assign('title', __($title = 'Break Policy List')); // See index.php
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

		Redirect::Page( URLBuilder::getURL( NULL, 'EditBreakPolicy.php', FALSE) );

		break;
	case 'delete' OR 'undelete':
		if ( strtolower($action) == 'delete' ) {
			$delete = TRUE;
		} else {
			$delete = FALSE;
		}

		$bplf = new BreakPolicyListFactory();

		foreach ($ids as $id) {
			$bplf->getByIdAndCompanyId($id, $current_company->getId() );
			foreach ($bplf as $bp_obj) {
				$bp_obj->setDeleted($delete);
				if ( $bp_obj->isValid() ) {
					$bp_obj->Save();
				}
			}
		}

		Redirect::Page( URLBuilder::getURL( NULL, 'BreakPolicyList.php') );

		break;

	default:
		$bplf = new BreakPolicyListFactory();
		$bplf->getByCompanyId( $current_company->getId() );

		$pager = new Pager($bplf);

		$type_options = $bplf->getOptions('type');

		$show_no_policy_group_notice = FALSE;
		foreach ($bplf as $bp_obj) {
			if ( (int)$bp_obj->getColumn('assigned_policy_groups') == 0 ) {
				$show_no_policy_group_notice = TRUE;
			}

			$policies[] = array(
								'id' => $bp_obj->getId(),
								'name' => $bp_obj->getName(),
								'type_id' => $bp_obj->getType(),
								'type' => $type_options[$bp_obj->getType()],
								'amount' => $bp_obj->getAmount(),
								'trigger_time' => $bp_obj->getTriggerTime(),
								'assigned_policy_groups' => (int)$bp_obj->getColumn('assigned_policy_groups'),
								'deleted' => $bp_obj->getDeleted()
							);

		}
		$smarty->assign_by_ref('policies', $policies);

		$smarty->assign_by_ref('show_no_policy_group_notice', $show_no_policy_group_notice );

		$smarty->assign_by_ref('sort_column', $sort_column );
		$smarty->assign_by_ref('sort_order', $sort_order );

		$smarty->assign_by_ref('paging_data', $pager->getPageVariables() );

		break;
}
$smarty->display('policy/BreakPolicyList.tpl');
?>