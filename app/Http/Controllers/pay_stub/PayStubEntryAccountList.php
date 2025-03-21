<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: PayStubEntryAccountList.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('pay_stub_account','enabled')
		OR !( $permission->Check('pay_stub_account','view') OR $permission->Check('pay_stub_account','view_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect

}

$smarty->assign('title', __($title = 'Pay Stub Account List')); // See index.php

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
	case 'add_presets':
		//Debug::setVerbosity(11);
		PayStubEntryAccountFactory::addPresets( $current_company->getId() );

		Redirect::Page( URLBuilder::getURL( NULL, 'PayStubEntryAccountList.php') );
	case 'add':

		Redirect::Page( URLBuilder::getURL( NULL, 'EditPayStubEntryAccount.php', FALSE) );

		break;
	case 'delete':
	case 'undelete':
		if ( strtolower($action) == 'delete' ) {
			$delete = TRUE;
		} else {
			$delete = FALSE;
		}

		$psealf = new PayStubEntryAccountListFactory();

		foreach ($ids as $id) {
			$psealf->getByIdAndCompanyId($id, $current_company->getId() );
			foreach ($psealf as $psea_obj) {
				$psea_obj->setDeleted($delete);
				if ( $psea_obj->isValid() ) {
					$psea_obj->Save();
				}
			}
		}

		Redirect::Page( URLBuilder::getURL( NULL, 'PayStubEntryAccountList.php') );

		break;
	default:
		BreadCrumb::setCrumb($title);

		$psealf = new PayStubEntryAccountListFactory();
		$psealf->getByCompanyId( $current_company->getId() );

		$pager = new Pager($psealf);

		$status_options = $psealf->getOptions('status');
		$type_options = $psealf->getOptions('type');

		foreach ($psealf as $psea_obj) {

			$rows[] = array(
								'id' => $psea_obj->getId(),
								'status_id' => $psea_obj->getStatus(),
								'status' => $status_options[$psea_obj->getStatus()],
								'type_id' => $psea_obj->getType(),
								'type' => $type_options[$psea_obj->getType()],
								'name' => $psea_obj->getName(),
								'ps_order' => $psea_obj->getOrder(),
								'debit_account' => $psea_obj->getDebitAccount(),
								'credit_account' => $psea_obj->getCreditAccount(),
								'deleted' => $psea_obj->getDeleted()
							);

		}
		$smarty->assign_by_ref('rows', $rows);

		$smarty->assign_by_ref('sort_column', $sort_column );
		$smarty->assign_by_ref('sort_order', $sort_order );

		$smarty->assign_by_ref('paging_data', $pager->getPageVariables() );

		break;
}
$smarty->display('pay_stub/PayStubEntryAccountList.tpl');
?>