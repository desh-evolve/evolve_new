<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: ViewScheduleCalendar.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('schedule','enabled')
		OR !( $permission->Check('schedule','view') OR $permission->Check('schedule','view_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect

}

$smarty->assign('title', __($title = 'My Schedule')); // See index.php
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
												'filter_start_date',
												'filter_end_date',
												'filter_user_id'
												) ) );

URLBuilder::setURL($_SERVER['SCRIPT_NAME'],
											array(
													'sort_column' => $sort_column,
													'sort_order' => $sort_order,
													'page' => $page
												) );


switch ($action) {
	case 'filter':
		if ( $filter_start_date != '' AND $filter_end_date != '' ) {
			$start_date = $filter_start_date = TTDate::parseDateTime($filter_start_date);
			$end_date = $filter_end_date = TTDate::parseDateTime($filter_end_date);

			if ( $start_date >= $end_date ) {
				$filter_start_date = $filter_end_date = NULL;
			}

			//90 day limit.
			if ( $end_date - $start_date > (86400 * 90) ) {
				$end_date = $start_date + (86400 * 90);
			}
		}
	default:
		if ( $permission->Check('schedule','view') ) {
			Debug::text('Viewing all users schedule', __FILE__, __LINE__, __METHOD__,10);
			if ( $filter_user_id != '' ) {
				$user_id = $filter_user_id;
			} else {
				$user_id = $current_user->getId();
			}
		} else {
			$user_id = $current_user->getId();
		}

		if ( $filter_start_date == '' OR $filter_end_date == '' ) {
			$start_date = $filter_start_date = TTDate::getBeginWeekEpoch( TTDate::getTime() - 86400, $current_user_prefs->getStartWeekDay() );
			$end_date = $filter_end_date = TTDate::getEndWeekEpoch( TTDate::getTime() + ( 86400 * 28 ), $current_user_prefs->getStartWeekDay() );
		}

		//$start_date = $filter_start_date = TTDate::getBeginWeekEpoch( $start_date, 'mon');
		//$end_date = $filter_end_date = TTDate::getEndWeekEpoch( $end_date, 'mon' );

		Debug::text(' Start Date: '. TTDate::getDate('DATE+TIME', $start_date) .' End Date: '. TTDate::getDate('DATE+TIME', $end_date) , __FILE__, __LINE__, __METHOD__,10);

		$sf = new ScheduleFactory();
		$default_schedule_shifts = $sf->getScheduleArray( $user_id, $start_date, $end_date);
		//var_dump($default_schedule_shifts);

		$calendar_array = TTDate::getCalendarArray($start_date, $end_date, $current_user_prefs->getStartWeekDay() );

		$smarty->assign_by_ref('calendar_array', $calendar_array);
		//$smarty->assign_by_ref('pay_period_locked_rows', $pay_period_locked_rows);

		$ulf = new UserListFactory();
		$user_obj = $ulf->getById( $user_id )->getCurrent();

		/*
		$holiday = new Holiday();
		$holiday->GetByCountryAndProvince($user_obj->getCountry(), $user_obj->getProvince() );
		*/
		$hlf = new HolidayListFactory();
		$holiday_array = $hlf->getArrayByPolicyGroupUserId( $user_id, $start_date, $end_date );
		//var_dump($holiday_array);

		$smarty->assign_by_ref('holidays', $holiday_array);

		$smarty->assign_by_ref('filter_start_date', $filter_start_date);
		$smarty->assign_by_ref('filter_end_date', $filter_end_date);

		$user_options = UserListFactory::getByCompanyIdArray( $current_company->getId(), FALSE );
		$smarty->assign_by_ref('user_options', $user_options);
		$smarty->assign_by_ref('filter_user_id', $user_id);

		$smarty->assign_by_ref('schedule_shifts', $default_schedule_shifts);

		$smarty->assign_by_ref('current_epoch', TTDate::getBeginDayEpoch() );

		break;
}
$smarty->display('schedule/ViewScheduleCalendar.tpl');
?>