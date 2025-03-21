<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: RecurringHolidayList.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('holiday_policy','enabled')
		OR !( $permission->Check('holiday_policy','view') OR $permission->Check('holiday_policy','view_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect

}

$smarty->assign('title', __($title = 'Recurring Holiday List')); // See index.php
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
	case 'add_presets':
		//Debug::setVerbosity(11);
		RecurringHolidayFactory::addPresets( $current_company->getId(), $current_company->getCountry() );

		Redirect::Page( URLBuilder::getURL( NULL, 'RecurringHolidayList.php') );
	case 'add':

		Redirect::Page( URLBuilder::getURL( NULL, 'EditRecurringHoliday.php', FALSE) );

		break;
	case 'delete' OR 'undelete':
		if ( strtolower($action) == 'delete' ) {
			$delete = TRUE;
		} else {
			$delete = FALSE;
		}

		$rhlf = new RecurringHolidayListFactory();

		foreach ($ids as $id) {
			$rhlf->getByIdAndCompanyId($id, $current_company->getId() );
			foreach ($rhlf as $rh_obj) {
				$rh_obj->setDeleted($delete);
				if ( $rh_obj->isValid() ) {
					$rh_obj->Save();
				}
			}
		}

		Redirect::Page( URLBuilder::getURL( NULL, 'RecurringHolidayList.php') );

		break;

	default:
		$rhlf = new RecurringHolidayListFactory();
		$rhlf->getByCompanyId( $current_company->getId(), $current_user_prefs->getItemsPerPage(), $page, NULL, $sort_array );


		$pager = new Pager($rhlf);

		//$type_options = $aplf->getOptions('type');

		foreach ($rhlf as $rh_obj) {

			$rows[] = array(
								'id' => $rh_obj->getId(),
								'name' => $rh_obj->getName(),
								'next_date' => $rh_obj->getNextDate( time() ),
								'deleted' => $rh_obj->getDeleted()
							);
		}
		
		//Special sorting since next_date is calculated outside of the DB.
		if ( $sort_column == 'next_date' ) {
			Debug::Text('Sort By Date!', __FILE__, __LINE__, __METHOD__,10);
			$rows = Sort::Multisort($rows, $sort_column, NULL, $sort_order);
		}
		
		$smarty->assign_by_ref('rows', $rows);

		$smarty->assign_by_ref('sort_column', $sort_column );
		$smarty->assign_by_ref('sort_order', $sort_order );

		$smarty->assign_by_ref('paging_data', $pager->getPageVariables() );

		break;
}
$smarty->display('policy/RecurringHolidayList.tpl');
?>