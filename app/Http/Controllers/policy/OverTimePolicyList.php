<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: OverTimePolicyList.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('over_time_policy','enabled')
		OR !( $permission->Check('over_time_policy','view') OR $permission->Check('over_time_policy','view_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect

}

$smarty->assign('title', __($title = 'Overtime Policy List')); // See index.php
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

		Redirect::Page( URLBuilder::getURL( NULL, 'EditOverTimePolicy.php', FALSE) );

		break;
	case 'delete' OR 'undelete':
		if ( strtolower($action) == 'delete' ) {
			$delete = TRUE;
		} else {
			$delete = FALSE;
		}

		$otplf = new OverTimePolicyListFactory();

		foreach ($ids as $id) {
			$otplf->getByIdAndCompanyId($id, $current_company->getId() );

			foreach ($otplf as $otp_obj) {
				$otp_obj->setDeleted($delete);
				if ( $otp_obj->isValid() ) {
					$otp_obj->Save();
				}
			}
		}

		Redirect::Page( URLBuilder::getURL( NULL, 'OverTimePolicyList.php') );

		break;

	default:
		$otplf = new OverTimePolicyListFactory();
		$otplf->getByCompanyId( $current_company->getId() );

		$pager = new Pager($otplf);

		$type_options = $otplf->getOptions('type');

 		$show_no_policy_group_notice = FALSE;
		foreach ($otplf as $otp_obj) {
			if ( (int)$otp_obj->getColumn('assigned_policy_groups') == 0 ) {
				$show_no_policy_group_notice = TRUE;
			}

			$policies[] = array(
								'id' => $otp_obj->getId(),
								'name' => $otp_obj->getName(),
								'type_id' => $otp_obj->getType(),
								'type' => $type_options[$otp_obj->getType()],
								'trigger_time' => $otp_obj->getTriggerTime(),
								'assigned_policy_groups' => (int)$otp_obj->getColumn('assigned_policy_groups'),
								'deleted' => $otp_obj->getDeleted()
							);

		}
		$smarty->assign_by_ref('policies', $policies);

		$smarty->assign_by_ref('show_no_policy_group_notice', $show_no_policy_group_notice );

		$smarty->assign_by_ref('sort_column', $sort_column );
		$smarty->assign_by_ref('sort_order', $sort_order );

		$smarty->assign_by_ref('paging_data', $pager->getPageVariables() );

		break;
}
$smarty->display('policy/OverTimePolicyList.tpl');
?>