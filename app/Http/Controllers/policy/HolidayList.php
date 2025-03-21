<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: HolidayList.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('holiday_policy','enabled')
		OR !( $permission->Check('holiday_policy','view') OR $permission->Check('holiday_policy','view_own') ) ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', __($title = 'Holiday List')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'page',
												'sort_column',
												'sort_order',
												'holiday_policy_id',
												'id',
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

		Redirect::Page( URLBuilder::getURL( array('holiday_policy_id' => $holiday_policy_id ), 'EditHoliday.php', FALSE) );

		break;
	case 'delete' OR 'undelete':
		if ( strtolower($action) == 'delete' ) {
			$delete = TRUE;
		} else {
			$delete = FALSE;
		}

		$hlf = new HolidayListFactory();

		foreach ($ids as $id) {
			$hlf->getById($id );
			foreach ($hlf as $h_obj) {
				$h_obj->setDeleted($delete);
				if ( $h_obj->isValid() ) {
					$h_obj->Save();
				}
			}
		}

		Redirect::Page( URLBuilder::getURL( array('id' => $holiday_policy_id ), 'HolidayList.php') );

		break;

	default:
		BreadCrumb::setCrumb($title);

		$hlf = new HolidayListFactory();
		$hlf->getByCompanyIdAndHolidayPolicyId( $current_company->getId(), $id );

		$pager = new Pager($hlf);
		
		if ( $hlf->getRecordCount() > 0 ) {
			foreach ($hlf as $h_obj) {

				$rows[] = array(
									'id' => $h_obj->getId(),
									'date_stamp' => $h_obj->getDateStamp(),
									'name' => $h_obj->getName(),
									'deleted' => $h_obj->getDeleted()
								);

			}
		}

		$smarty->assign_by_ref('holiday_policy_id', $id);
		$smarty->assign_by_ref('rows', $rows);

		$smarty->assign_by_ref('sort_column', $sort_column );
		$smarty->assign_by_ref('sort_order', $sort_order );

		$smarty->assign_by_ref('paging_data', $pager->getPageVariables() );

		break;
}
$smarty->display('policy/HolidayList.tpl');
?>