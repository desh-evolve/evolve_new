<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: RoundPolicyList.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('round_policy','enabled')
		OR !( $permission->Check('round_policy','view') OR $permission->Check('round_policy','view_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect

}

$smarty->assign('title', TTi18n::gettext($title = 'Rounding Policy List')); // See index.php
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

switch ($action) {
	case 'add':

		Redirect::Page( URLBuilder::getURL(NULL, 'EditRoundPolicy.php') );

		break;
	case 'delete' OR 'undelete':
		if ( strtolower($action) == 'delete' ) {
			$delete = TRUE;
		} else {
			$delete = FALSE;
		}

		$rplf = new RoundPolicyListFactory();

		foreach ($ids as $id) {
			$rplf->getByIdAndCompanyId($id, $current_company->getId() );
			foreach ($rplf as $round_policy_obj) {
				$round_policy_obj->setDeleted($delete);
				$round_policy_obj->Save();
			}
		}

		Redirect::Page( URLBuilder::getURL(NULL, 'RoundPolicyList.php') );

		break;

	default:
		$rplf = new RoundPolicyListFactory();

		$rplf->getByCompanyId($current_company->getId(), $current_user_prefs->getItemsPerPage(),$page, NULL, $sort_array );

		$pager = new Pager($rplf);

		foreach ($rplf as $round_policy_obj) {

			$round_policies[] = array(
								'id' => $round_policy_obj->getId(),
								'name' => $round_policy_obj->getName(),
								'description' => $round_policy_obj->getDescription(),
								'default' => $round_policy_obj->getDefault()
							);

		}
		$smarty->assign_by_ref('round_policies', $round_policies);

		$smarty->assign_by_ref('sort_column', $sort_column );
		$smarty->assign_by_ref('sort_order', $sort_order );

		$smarty->assign_by_ref('paging_data', $pager->getPageVariables() );

		break;
}
$smarty->display('round_policy/RoundPolicyList.tpl');
?>