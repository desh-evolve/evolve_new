<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: AbsencePolicyList.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('absence_policy','enabled')
		OR !( $permission->Check('absence_policy','view') OR $permission->Check('absence_policy','view_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect

}

$smarty->assign('title', TTi18n::gettext($title = 'Absence Policy List')); // See index.php
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

		Redirect::Page( URLBuilder::getURL( NULL, 'EditAbsencePolicy.php', FALSE) );

		break;
	case 'delete' OR 'undelete':
		if ( strtolower($action) == 'delete' ) {
			$delete = TRUE;
		} else {
			$delete = FALSE;
		}

		$aplf = new AbsencePolicyListFactory();

		foreach ($ids as $id) {
			$aplf->getByIdAndCompanyId($id, $current_company->getId() );
			foreach ($aplf as $ap_obj) {
				$ap_obj->setDeleted($delete);
				if ( $ap_obj->isValid() ) {
					$ap_obj->Save();
				}
			}
		}

		Redirect::Page( URLBuilder::getURL( NULL, 'AbsencePolicyList.php') );

		break;

	default:
		$aplf = new AbsencePolicyListFactory();
		$aplf->getByCompanyId( $current_company->getId() );

		$pager = new Pager($aplf);

		$type_options = $aplf->getOptions('type');

		foreach ($aplf as $ap_obj) {

			$policies[] = array(
								'id' => $ap_obj->getId(),
								'name' => $ap_obj->getName(),
								'type_id' => $ap_obj->getType(),
								'type' => $type_options[$ap_obj->getType()],
								'deleted' => $ap_obj->getDeleted()
							);

		}
		$smarty->assign_by_ref('policies', $policies);

		$smarty->assign_by_ref('sort_column', $sort_column );
		$smarty->assign_by_ref('sort_order', $sort_order );

		$smarty->assign_by_ref('paging_data', $pager->getPageVariables() );

		break;
}
$smarty->display('policy/AbsencePolicyList.tpl');
?>