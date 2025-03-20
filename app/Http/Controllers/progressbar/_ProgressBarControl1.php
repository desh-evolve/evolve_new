<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 2413 $
 * $Id: ProgressBarControl.php 2413 2009-02-06 21:59:57Z ipso $
 * $Date: 2009-02-06 13:59:57 -0800 (Fri, 06 Feb 2009) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');


//Debug::setVerbosity(11);

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'next_page',
												'pay_period_ids',
												'filter_user_id',
												'pay_stub_ids',
												'data',
												) ) );

$action = strtolower($action);
switch ($action) {
	case 'recalculate_company':
		Debug::Text('ProgressBarControl: Recalculating Company TimeSheet!', __FILE__, __LINE__, __METHOD__,10);

		if ( !$permission->Check('punch','enabled')
				OR !( $permission->Check('punch','edit') OR $permission->Check('punch','edit_own') ) ) {

			$permission->Redirect( FALSE ); //Redirect
		}

		$comment = TTi18n::gettext('Recalculating Company TimeSheet...');

		break;
	case 'recalculate_employee':
		Debug::Text('ProgressBarControl: Recalculating Employee / Company TimeSheet!', __FILE__, __LINE__, __METHOD__,10);

		if ( !$permission->Check('punch','enabled')
				OR !( $permission->Check('punch','edit') OR $permission->Check('punch','edit_own') ) ) {

			$permission->Redirect( FALSE ); //Redirect
		}

		$comment = TTi18n::gettext('Recalculating Employee TimeSheet...');

		break;
	case 'generate_paystubs':
		Debug::Text('Generate PayStubs!', __FILE__, __LINE__, __METHOD__,10);

		if ( !$permission->Check('pay_period_schedule','enabled')
				OR !( $permission->Check('pay_period_schedule','edit') OR $permission->Check('pay_period_schedule','edit_own') ) ) {

			$permission->Redirect( FALSE ); //Redirect
		}

		$comment = TTi18n::gettext('Generating Pay Stubs...');

		//$smarty->assign_by_ref('action', $action);
		//$smarty->assign_by_ref('pay_period_ids', $pay_period_ids);

		break;
	case 'recalculate_paystub_ytd':
		Debug::Text('Re-Calculating PayStub YTD values!', __FILE__, __LINE__, __METHOD__,10);

		if ( !$permission->Check('pay_period_schedule','enabled')
				OR !( $permission->Check('pay_period_schedule','edit') OR $permission->Check('pay_period_schedule','edit_own') ) ) {

			$permission->Redirect( FALSE ); //Redirect
		}

		$comment = TTi18n::gettext('Recalculating Pay Stub Year To Date (YTD) amounts...');

		break;
	case 'recalculate_accrual_policy':
		Debug::Text('Recalculate Accrual Policy!', __FILE__, __LINE__, __METHOD__,10);

		if ( !$permission->Check('accrual_policy','enabled')
				OR !( $permission->Check('accrual_policy','edit')
						OR $permission->Check('accrual_policy','edit_own')
						OR $permission->Check('accrual_policy','edit_child')
						 ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}

		$comment = TTi18n::gettext('Recalculating Accrual Policy...');

		break;
	case 'add_mass_punch':
		Debug::Text('Add Mass Punch!', __FILE__, __LINE__, __METHOD__,10);

		if ( !$permission->Check('punch','enabled')
				OR !( $permission->Check('punch','edit')
						OR $permission->Check('punch','edit_own')
						OR $permission->Check('punch','edit_child')
						 ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}

		$comment = TTi18n::gettext('Adding Punches...');

		break;
	case 'add_mass_schedule':
		Debug::Text('Add Mass Schedule!', __FILE__, __LINE__, __METHOD__,10);

		if ( !$permission->Check('schedule','enabled')
				OR !( $permission->Check('schedule','edit')
						OR $permission->Check('schedule','edit_own')
						OR $permission->Check('schedule','edit_child')
						 ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}

		$comment = TTi18n::gettext('Adding Schedule Shifts...');

		break;
	case 'add_mass_schedule_npvc':
            echo '<pre>'; print_r($data); die;
		Debug::Text('Add Mass Schedule!', __FILE__, __LINE__, __METHOD__,10);

		if ( !$permission->Check('schedule','enabled')
				OR !( $permission->Check('schedule','edit')
						OR $permission->Check('schedule','edit_own')
						OR $permission->Check('schedule','edit_child')
						 ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}

		$comment = TTi18n::gettext('Adding Schedule Shifts...');

		break;
	default:
		$comment = TTi18n::gettext('Test Progress Bar...');
		//$smarty->assign_by_ref('user_data', $user_data);

		break;


}

/*
	This suffers from URLs that are too long, especially when coming from Mass Punch/Schedule.
	Offer a method to store the data in the user_generic_data table, and retreive it on the ProgressBar.php page, bypassing the URL completely.
*/
$url = URLBuilder::getURL( array('action' => $action, 'pay_period_ids' => $pay_period_ids, 'filter_user_id' => $filter_user_id, 'pay_stub_ids' => $pay_stub_ids, 'data' => $data, 'next_page' => urlencode($next_page) ), Environment::getBaseURL().'/progress_bar/ProgressBar.php');

$smarty->assign_by_ref('comment', $comment);
//$smarty->assign_by_ref('next_page', $next_page);
$smarty->assign_by_ref('url', $url);

$smarty->display('progress_bar/ProgressBarControl.tpl');
?>