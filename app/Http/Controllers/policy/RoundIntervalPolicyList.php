<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: RoundIntervalPolicyList.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('round_policy','enabled')
		OR !( $permission->Check('round_policy','view') OR $permission->Check('round_policy','view_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect

}

$smarty->assign('title', __($title = 'Rounding Policy List')); // See index.php
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

		Redirect::Page( URLBuilder::getURL( NULL, 'EditRoundIntervalPolicy.php', FALSE) );

		break;
	case 'delete' OR 'undelete':
		if ( strtolower($action) == 'delete' ) {
			$delete = TRUE;
		} else {
			$delete = FALSE;
		}

		$riplf = new RoundIntervalPolicyListFactory();

		foreach ($ids as $id) {
			$riplf->getByIdAndCompanyId($id, $current_company->getId() );
			foreach ($riplf as $rip_obj) {
				$rip_obj->setDeleted($delete);
				$rip_obj->Save();
			}
		}

		Redirect::Page( URLBuilder::getURL( NULL, 'RoundIntervalPolicyList.php') );

		break;

	default:
		$riplf = new RoundIntervalPolicyListFactory();
		$riplf->getByCompanyId( $current_company->getId() );

		$pager = new Pager($riplf);

		$punch_type_options = $riplf->getOptions('punch_type');

 		$show_no_policy_group_notice = FALSE;
		foreach ($riplf as $rip_obj) {
			if ( (int)$rip_obj->getColumn('assigned_policy_groups') == 0 ) {
				$show_no_policy_group_notice = TRUE;
			}

			$policies[] = array(
								'id' => $rip_obj->getId(),
								'name' => $rip_obj->getName(),
								'punch_type_id' => $rip_obj->getPunchType(),
								'punch_type' => $punch_type_options[$rip_obj->getPunchType()],
								'interval' => $rip_obj->getInterval(),
								'assigned_policy_groups' => (int)$rip_obj->getColumn('assigned_policy_groups'),
								'deleted' => $rip_obj->getDeleted()
							);

		}
		$smarty->assign_by_ref('policies', $policies);

		$smarty->assign_by_ref('show_no_policy_group_notice', $show_no_policy_group_notice );

		$smarty->assign_by_ref('sort_column', $sort_column );
		$smarty->assign_by_ref('sort_order', $sort_order );

		$smarty->assign_by_ref('paging_data', $pager->getPageVariables() );

		break;
}
$smarty->display('policy/RoundIntervalPolicyList.tpl');
?>