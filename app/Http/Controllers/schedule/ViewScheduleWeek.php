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
use App\Models\Core\Sort;
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

class ViewScheduleWeek extends Controller
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
		//require_once(Environment::getBasePath() .'classes/misc/arr_multisort.class.php');

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
				OR !( $permission->Check('schedule','view') OR $permission->Check('schedule','view_own') OR $permission->Check('schedule','view_child') ) ) {
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

		switch ($do) {
			case 'view_schedule':
			default:
				$user_ids = array();

				if ( $filter_data['start_date'] != '' AND $filter_data['show_days'] != '' ) {
					$start_date = $filter_data['start_date'] = TTDate::getBeginDayEpoch( TTDate::parseDateTime($filter_data['start_date']) );
					$end_date = $filter_data['end_date'] = $start_date + ($filter_data['show_days']*86400-3601);
				} else {
					$start_date = $filter_data['start_date'] = TTDate::getBeginWeekEpoch( TTDate::getTime(), $current_user_prefs->getStartWeekDay() );
					$end_date = $filter_data['end_date'] = $start_date + ($filter_data['show_days']*(86400-3601));
				}

				Debug::text(' Start Date: '. TTDate::getDate('DATE+TIME', $start_date) .' End Date: '. TTDate::getDate('DATE+TIME', $end_date) , __FILE__, __LINE__, __METHOD__,10);

				$sf = new ScheduleFactory(); 
				$raw_schedule_shifts = $sf->getScheduleArray( $filter_data );
				if ( is_array($raw_schedule_shifts) ) {
					foreach( $raw_schedule_shifts as $day_epoch => $day_schedule_shifts ) {
						foreach ( $day_schedule_shifts as $day_schedule_shift ) {
							$user_ids[] = $day_schedule_shift['user_id'];

							//$day_schedule_shift['is_owner'] = $permission->isOwner( $u_obj->getCreatedBy(), $u_obj->getId() );
							//$day_schedule_shift['is_child'] = $permission->isChild( $u_obj->getId(), $permission_children_ids );
							$day_schedule_shift['is_owner'] = $permission->isOwner( $day_schedule_shift['user_created_by'], $day_schedule_shift['user_id'] );
							$day_schedule_shift['is_child'] = $permission->isChild( $day_schedule_shift['user_id'], $permission_children_ids );

							$tmp_schedule_shifts[$day_epoch][$day_schedule_shift['branch']][$day_schedule_shift['department']][] = $day_schedule_shift;
							//$schedule_shifts[$day_epoch][] = $day_schedule_shift;
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
								//var_dump($department_schedule_shift);
								$schedule_shifts[$day_epoch][$branch][$department] = Sort::multiSort( $department_schedule_shift, 'start_time', 'last_name' );
							}
						}
					}
				}
				//print_r($schedule_shift_totals);

				$calendar_array = TTDate::getCalendarArray($start_date, $end_date, $current_user_prefs->getStartWeekDay(), FALSE);
				$viewData['calendar_array'] = $calendar_array;

				$hlf = new HolidayListFactory();
				$holiday_array = $hlf->getArrayByPolicyGroupUserId( $user_ids, $start_date, $end_date );

				$viewData['holidays'] = $holiday_array;
				$viewData['schedule_shifts'] = $schedule_shifts;
				$viewData['schedule_shift_totals'] = $schedule_shift_totals;

				$viewData['do'] = $do ;

				break;
		}

		return view('schedule/ViewScheduleWeek', $viewData);
	}
}
?>