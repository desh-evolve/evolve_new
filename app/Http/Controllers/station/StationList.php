<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: StationList.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('station','enabled')
		OR !( $permission->Check('station','view') OR $permission->Check('station','view_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect

}

$smarty->assign('title', __($title = 'Station List')); // See index.php
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

$action = Misc::findSubmitButton();
switch ($action) {
	case 'add':

		Redirect::Page( URLBuilder::getURL(NULL, 'EditStation.php') );

		break;
	case 'delete' OR 'undelete':
		if ( strtolower($action) == 'delete' ) {
			$delete = TRUE;
		} else {
			$delete = FALSE;
		}

		$slf = new StationListFactory();

		foreach ($ids as $id) {
			$slf->GetByIdAndCompanyId($id, $current_company->getId() );
			foreach ($slf as $station) {
				$station->setDeleted($delete);
				$station->Save();
			}
		}

		Redirect::Page( URLBuilder::getURL(NULL, 'StationList.php') );

		break;

	default:
		$slf = new StationListFactory();

		$slf->getByCompanyId($current_company->getId(), $current_user_prefs->getItemsPerPage(), $page, NULL, $sort_array );

		$pager = new Pager($slf);

		foreach ($slf as $station) {
			$stations[] = array(
								'id' => $station->GetId(),
								'type' => Option::getByKey($station->getType(), $station->getOptions('type') ),
								'status' => Option::getByKey($station->getStatus(), $station->getOptions('status') ),
								'source' => $station->getSource(),
								'station' => $station->getStation(),
								'short_station' => Misc::TruncateString( $station->getStation(), 15 ),
								'description' => Misc::TruncateString( $station->getDescription(), 30 ) ,
								'deleted' => $station->getDeleted()
							);

			unset($description);
		}
		$smarty->assign_by_ref('stations', $stations);

		$smarty->assign_by_ref('sort_column', $sort_column );
		$smarty->assign_by_ref('sort_order', $sort_order );

		$smarty->assign_by_ref('paging_data', $pager->getPageVariables() );

		break;
}
$smarty->display('station/StationList.tpl');
?>