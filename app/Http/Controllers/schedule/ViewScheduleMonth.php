<?php

namespace App\Http\Controllers\schedule;

use App\Http\Controllers\Controller;
use App\Models\Company\BranchListFactory;
use App\Models\Core\Debug;
use Illuminate\Http\Request;

use App\Models\Core\Environment;
use App\Models\Core\FastTree;
use App\Models\Core\FormVariables;
use App\Models\Core\Misc;
use App\Models\Core\Redirect;
use App\Models\Core\TTDate;
use App\Models\Core\URLBuilder;
use App\Models\Department\DepartmentListFactory;
use App\Models\Hierarchy\HierarchyListFactory;
use App\Models\Holiday\HolidayListFactory;
use App\Models\Schedule\RecurringScheduleControlListFactory;
use App\Models\Schedule\RecurringScheduleTemplateControlListFactory;
use App\Models\Schedule\ScheduleFactory;
use App\Models\Users\UserGenericDataFactory;
use App\Models\Users\UserGenericDataListFactory;
use App\Models\Users\UserGroupListFactory;
use App\Models\Users\UserListFactory;
use App\Models\Users\UserTitleListFactory;
use Illuminate\Support\Facades\View;

class ViewScheduleMonth extends Controller
{
    protected $permission;
    protected $currentUser;
    protected $currentCompany;
    protected $userPrefs;

    public function __construct()
    {
        $basePath = Environment::getBasePath();
        require_once($basePath . '/app/Helpers/global.inc.php');
        require_once($basePath . '/app/Helpers/Interface.inc.php');

        $this->permission = View::shared('permission');
        $this->currentUser = View::shared('current_user');
        $this->currentCompany = View::shared('current_company');
        $this->userPrefs = View::shared('current_user_prefs');
    }
	
    public function index() {
		$permission = $this->permission;
		$current_user = $this->currentUser;
		$current_company = $this->currentCompany;
		$current_user_prefs = $this->userPrefs;

		//Debug::setVerbosity(11);

		if ( !$permission->Check('schedule','enabled')
				OR !( $permission->Check('schedule','view') OR $permission->Check('schedule','view_own') OR $permission->Check('schedule','view_child')) ) {
			$permission->Redirect( FALSE ); //Redirect
		}

		$viewData['title'] = 'My Schedule';

		/*
		* Get FORM variables
		*/
		extract	(FormVariables::GetVariables(
												array	(
														'do',
														'page',
														'sort_column',
														'sort_order',
														'filter_data',
														) ) );

		URLBuilder::setURL($_SERVER['SCRIPT_NAME'],
													array(
															'sort_column' => $sort_column,
															'sort_order' => $sort_order,
															'page' => $page
														) );

		if ( isset( $filter_data['start_date'] ) AND $filter_data['start_date'] != '' ) {
			$filter_data['start_date'] = TTDate::parseDateTime($filter_data['start_date']);
		} else {
			$filter_data['start_date'] = time();
		}


		if ( !isset($filter_data['show_days']) OR ( isset($filter_data['show_days']) AND $filter_data['show_days'] == '' )  ) {
			$filter_data['show_days'] = 1;
		}
		$filter_data['show_days'] = $filter_data['show_days'] * 7;

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

		//$do = Misc::findSubmitButton('do');

		//===================================================================================
		$action = '';
		if (isset($_POST['action'])) {
			$action = trim($_POST['action']);
		} elseif (isset($_GET['action'])) {
			$action = trim($_GET['action']);
		}
		$action = !empty($action) ? strtolower(str_replace(' ', '_', $action)) : '';
		//===================================================================================

		switch (strtolower($do)) {
			case 'view_schedule':
			default:
				$user_ids = array();

				if ( $filter_data['start_date'] != '' AND $filter_data['show_days'] != '' ) {
					$start_date = $filter_data['start_date'] = TTDate::getBeginWeekEpoch( $filter_data['start_date'], $current_user_prefs->getStartWeekDay() );
					$end_date = $filter_data['end_date'] = $start_date + ($filter_data['show_days']*86400-3601);
				} else {
					$start_date = $filter_data['start_date'] = TTDate::getBeginWeekEpoch( TTDate::getTime(), $current_user_prefs->getStartWeekDay() );
					$end_date = $filter_data['end_date'] = $start_date + (7*(86400-3600));
				}

				Debug::text(' Start Date: '. TTDate::getDate('DATE+TIME', $start_date) .' End Date: '. TTDate::getDate('DATE+TIME', $end_date) , __FILE__, __LINE__, __METHOD__,10);

				//var_dump( $filter_data);
				$sf = new ScheduleFactory(); 
				$raw_schedule_shifts = $sf->getScheduleArray( $filter_data );
				//Debug::Arr($raw_schedule_shifts, 'Raw Schedule Shifts1: ', __FILE__, __LINE__, __METHOD__, 10);
				if ( is_array($raw_schedule_shifts) ) {
					foreach( $raw_schedule_shifts as $day_epoch => $day_schedule_shifts ) {
						foreach ( $day_schedule_shifts as $day_schedule_shift ) {
							$user_ids[] = $day_schedule_shift['user_id'];

							//$schedule_shifts[$day_epoch][$day_schedule_shift['branch']][$day_schedule_shift['department']][] = $day_schedule_shift;
							$schedule_shifts[$day_epoch][] = $day_schedule_shift;
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
				$total_users = count($user_ids);

				//Debug::Arr($schedule_shift_totals, 'Totals: ', __FILE__, __LINE__, __METHOD__, 10);
				
				//Total up employees/time per day.
				if ( isset($schedule_shift_totals) ) {
					foreach( $schedule_shift_totals as $day_epoch => $total_arr) {
						if ( isset($total_arr['users']) ) {
							$schedule_shift_totals[$day_epoch]['total_users'] = count(array_unique($total_arr['users']));
						}
						if ( isset($total_arr['absent_users']) ) {
							$schedule_shift_totals[$day_epoch]['total_absent_users'] = count(array_unique($total_arr['absent_users']));
						}
					}
				}
				//var_dump($tmp_schedule_shifts);

				$calendar_array = TTDate::getCalendarArray($start_date, $end_date, $current_user_prefs->getStartWeekDay(), FALSE);
				//var_dump($calendar_array);
				$smarty->assign_by_ref('calendar_array', $calendar_array);

				//Get column headers, taking in to account start_day_of_week.
				$x=0;
				foreach( $calendar_array as $tmp_calendar_day ) {
					$calendar_column_headers[] = __(date('l', $tmp_calendar_day['epoch']));

					if ( $x == 6 ) {
						break;
					}
					$x++;
				}
				$viewData['calendar_column_headers'] = $calendar_column_headers;

				$hlf = new HolidayListFactory(); 
				$holiday_array = $hlf->getArrayByPolicyGroupUserId( $user_ids, $start_date, $end_date );
				//var_dump($holiday_array);
				$viewData['holidays'] = $holiday_array;

				$viewData['filter_data'] = $filter_data;
				$viewData['serialize_filter_data'] = urlencode( base64_encode( serialize($filter_data) ) ) ;
				$viewData['total_users'] = $total_users;

				$viewData['schedule_shifts'] = $schedule_shifts;
				$viewData['schedule_shift_totals'] = $schedule_shift_totals;

				$viewData['do'] = $do ;

				break;
		}

		return view('schedule/ViewScheduleMonth', $viewData);
	}
}
?>