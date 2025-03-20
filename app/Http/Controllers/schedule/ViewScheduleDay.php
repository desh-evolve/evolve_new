<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: ViewScheduleDay.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');
require_once(Environment::getBasePath() .'classes/misc/arr_multisort.class.php');

//Debug::setVerbosity(11);

if ( !$permission->Check('schedule','enabled')
		OR !( $permission->Check('schedule','view') OR $permission->Check('schedule','view_own') OR $permission->Check('schedule','view_child')) ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'My Schedule')); // See index.php
//BreadCrumb::setCrumb($title);

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'page',
												'sort_column',
												'sort_order',
												'filter_data',
												'serialize_filter_data',
												//'filter_start_date',
												//'filter_user_id'
												) ) );

URLBuilder::setURL($_SERVER['SCRIPT_NAME'],
											array(
													'sort_column' => $sort_column,
													'sort_order' => $sort_order,
													'page' => $page
												) );

//Data is coming in serialized from Schedule Month.
$serialize_filter_data = unserialize( base64_decode( urldecode( $serialize_filter_data ) ) );

if ( isset( $filter_data['start_date'] ) AND $filter_data['start_date'] != '' ) {
	$filter_data['start_date'] = TTDate::parseDateTime($filter_data['start_date']);
} else {
	$filter_data['start_date'] = TTDate::getBeginWeekEpoch( time() );
}
$filter_data = array_merge( $serialize_filter_data, $filter_data);

//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
$hlf = new HierarchyListFactory();
$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );
if ( $permission->Check('schedule','view') == FALSE ) {
	if ( $permission->Check('schedule','view_child') == FALSE ) {
		$permission_children_ids = array();
	}
	if ( $permission->Check('schedule','view_own') ) {
		$permission_children_ids[] = $current_user->getId();
	}

	$filter_data['permission_children_ids'] = $permission_children_ids;
}

$action = Misc::findSubmitButton();
switch ($action) {
	default:
		$user_ids = array();

		if ( $filter_data['start_date'] != '' ) {
			$start_date = $filter_data['start_date'] = TTDate::getBeginDayEpoch( $filter_data['start_date'] );
			$end_date = $filter_data['end_date'] = TTDate::getEndDayEpoch($start_date);
		}

		Debug::text(' Start Date: '. TTDate::getDate('DATE+TIME', $start_date) .' End Date: '. TTDate::getDate('DATE+TIME', $end_date) , __FILE__, __LINE__, __METHOD__,10);

		$sf = new ScheduleFactory();
		$raw_schedule_shifts = $sf->getScheduleArray(  $filter_data );
		if ( is_array($raw_schedule_shifts) ) {
			foreach( $raw_schedule_shifts as $day_epoch => $day_schedule_shifts ) {
				foreach ( $day_schedule_shifts as $day_schedule_shift ) {
					$user_ids[] = $day_schedule_shift['user_id'];

					//$day_schedule_shift['is_owner'] = $permission->isOwner( $u_obj->getCreatedBy(), $u_obj->getId() );
					//$day_schedule_shift['is_child'] = $permission->isChild( $u_obj->getId(), $permission_children_ids );
					$day_schedule_shift['is_owner'] = $permission->isOwner( $day_schedule_shift['user_created_by'], $day_schedule_shift['user_id'] );
					$day_schedule_shift['is_child'] = $permission->isChild( $day_schedule_shift['user_id'], $permission_children_ids );

					$tmp_schedule_shifts[$day_epoch][$day_schedule_shift['branch']][$day_schedule_shift['department']][] = $day_schedule_shift;
					//echo 'User ID: '. $day_schedule_shift['user_id'] .' Date: '. $day_epoch .' Total Time: '. ($day_schedule_shift['total_time']/3600) ."<br>\n";
					if ( $day_schedule_shift['status_id'] == 10 ) { //Working
						if ( isset($schedule_shift_totals[$day_epoch]['total_time']) ) {
							$schedule_shift_totals[$day_epoch]['total_time'] += $day_schedule_shift['total_time'];
						} else {
							$schedule_shift_totals[$day_epoch]['total_time'] = $day_schedule_shift['total_time'];
						}
						$schedule_shift_totals[$day_epoch]['users'][] = $day_schedule_shift['user_id'];

					} elseif ( $day_schedule_shift['status_id'] == 20 ) { //Absent
						if ( isset($schedule_shift_totals[$day_epoch]['absent_total_time']) ) {
							$schedule_shift_totals[$day_epoch]['absent_total_time'] += $day_schedule_shift['total_time'];
						} else {
							$schedule_shift_totals[$day_epoch]['absent_total_time'] = $day_schedule_shift['total_time'];
						}
						$schedule_shift_totals[$day_epoch]['absent_users'][] = $day_schedule_shift['user_id'];
					}
				}
			}
		}
		$user_ids = array_unique($user_ids);

		//Total up employees/time per day.
		if ( isset($schedule_shift_totals) ) {
			foreach( $schedule_shift_totals as $day_epoch => $total_arr) {
				if ( !isset($total_arr['users']) ) {
					$total_arr['users'] = array();
				}
				$schedule_shift_totals[$day_epoch]['total_users'] = count(array_unique($total_arr['users']));
			}
		}
		//print_r($schedule_shift_totals);
		//var_dump($tmp_schedule_shifts);

		if ( isset($tmp_schedule_shifts) ) {
			//Sort Branches/Departments first
			foreach ( $tmp_schedule_shifts as $day_epoch => $day_tmp_schedule_shift ) {
				ksort($day_tmp_schedule_shift);
				$tmp_schedule_shifts[$day_epoch] = $day_tmp_schedule_shift;

				foreach ( $day_tmp_schedule_shift as $branch => $department_schedule_shifts ) {
					ksort($tmp_schedule_shifts[$day_epoch][$branch]);
				}
			}

			//Sort each department by start time.
			foreach ( $tmp_schedule_shifts as $day_epoch => $day_tmp_schedule_shift ) {
				foreach ( $day_tmp_schedule_shift as $branch => $department_schedule_shifts ) {
					foreach ( $department_schedule_shifts as $department => $department_schedule_shift ) {
						$schedule_shifts[$day_epoch][$branch][$department] = Sort::multiSort( $department_schedule_shift, 'start_time', 'last_name' );
					}
				}
			}
		}
		//print_r($schedule_shifts);

		if ( isset($start_date) AND isset($end_date) ) {
			$calendar_array = TTDate::getCalendarArray($start_date, $end_date, $current_user_prefs->getStartWeekDay(), FALSE);
			//var_dump($calendar_array);
		}
		$smarty->assign_by_ref('calendar_array', $calendar_array);

		$hlf = new HolidayListFactory();
		$holiday_array = $hlf->getArrayByPolicyGroupUserId( $user_ids, $start_date, $end_date );
		//var_dump($holiday_array);

		$smarty->assign_by_ref('holidays', $holiday_array);

		$smarty->assign_by_ref('schedule_shifts', $schedule_shifts);
		$smarty->assign_by_ref('schedule_shift_totals', $schedule_shift_totals);

		$smarty->assign_by_ref('action', $action );

		break;
}
$smarty->display('schedule/ViewScheduleDay.tpl');
?>