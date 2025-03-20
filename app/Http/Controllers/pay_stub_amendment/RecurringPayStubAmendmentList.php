<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: RecurringPayStubAmendmentList.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('pay_stub_amendment','enabled')
		OR !( $permission->Check('pay_stub_amendment','view') OR $permission->Check('pay_stub_amendment','view_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect

}

$smarty->assign('title', TTi18n::gettext($title = 'Recurring Pay Stub Amendment List')); // See index.php
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
												'user_id'
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

		Redirect::Page( URLBuilder::getURL( NULL, 'EditRecurringPayStubAmendment.php', FALSE) );

		break;
	case 'delete' OR 'undelete':
		if ( strtolower($action) == 'delete' ) {
			$delete = TRUE;
		} else {
			$delete = FALSE;
		}

		$rpsalf = new RecurringPayStubAmendmentListFactory();

		foreach ($ids as $id) {
			$rpsalf->getById( $id );
			foreach ($rpsalf as $recurring_pay_stub_amendment) {
				$recurring_pay_stub_amendment->setDeleted($delete);
				$recurring_pay_stub_amendment->Save();
			}
		}
		unset($recurring_pay_stub_amendment);

		Redirect::Page( URLBuilder::getURL( NULL, 'RecurringPayStubAmendmentList.php', FALSE) );

		break;
	default:
		URLBuilder::setURL(NULL, array('sort_column' => $sort_column, 'sort_order' => $sort_order) );

		$rpsalf = new RecurringPayStubAmendmentListFactory();

		$rpsalf->getByCompanyId($current_company->getId(), $current_user_prefs->getItemsPerPage(), $page, NULL, $sort_array );

		$pager = new Pager($rpsalf);

		$psealf = new PayStubEntryAccountListFactory();

		foreach ($rpsalf as $recurring_pay_stub_amendment) {

			$recurring_pay_stub_amendments[] = array(
								'id' => $recurring_pay_stub_amendment->GetId(),
								'name' => $recurring_pay_stub_amendment->getName(),
								'description' => $recurring_pay_stub_amendment->getDescription(),
								'status' => Option::getByKey($recurring_pay_stub_amendment->getStatus(), $recurring_pay_stub_amendment->getOptions('status') ),
								'frequency' => Option::getByKey($recurring_pay_stub_amendment->getFrequency(), $recurring_pay_stub_amendment->getOptions('frequency') ),
								'pay_stub_entry_name' => $psealf->getById( $recurring_pay_stub_amendment->getPayStubEntryNameId() )->getCurrent()->getName(),
								'deleted' => $recurring_pay_stub_amendment->getDeleted()
							);

		}

		$smarty->assign_by_ref('recurring_pay_stub_amendments', $recurring_pay_stub_amendments);

		$smarty->assign_by_ref('sort_column', $sort_column );
		$smarty->assign_by_ref('sort_order', $sort_order );

		$smarty->assign_by_ref('paging_data', $pager->getPageVariables() );

		$smarty->assign_by_ref('user_id', $user_id );
		break;
}
$smarty->display('pay_stub_amendment/RecurringPayStubAmendmentList.tpl');
?>