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

class ViewScheduleLinear extends Controller
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
					$end_date = $filter_data['end_date'] = TTDate::getEndDayEpoch($start_date + ($filter_data['show_days']*(86400-3601)));
				} else {
					$start_date = $filter_data['start_date'] = TTDate::getBeginWeekEpoch( TTDate::getTime(), $current_user_prefs->getStartWeekDay() );
					$end_date = $filter_data['end_date'] = TTDate::getEndDayEpoch($start_date + ($filter_data['show_days']*(86400-3601)));
				}

				Debug::text(' Start Date: '. TTDate::getDate('DATE+TIME', $start_date) .' End Date: '. TTDate::getDate('DATE+TIME', $end_date) , __FILE__, __LINE__, __METHOD__,10);

				$i=0;
				$min_hour = 0;
				$max_hour = 0;

				$sf = new ScheduleFactory(); 
				$raw_schedule_shifts = $sf->getScheduleArray(  $filter_data );
				if ( is_array($raw_schedule_shifts) ) {
					foreach( $raw_schedule_shifts as $day_epoch => $day_schedule_shifts ) {
						foreach ( $day_schedule_shifts as $day_schedule_shift ) {
							//$day_schedule_shift['is_owner'] = $permission->isOwner( $u_obj->getCreatedBy(), $u_obj->getId() );
							//$day_schedule_shift['is_child'] = $permission->isChild( $u_obj->getId(), $permission_children_ids );
							$day_schedule_shift['is_owner'] = $permission->isOwner( $day_schedule_shift['user_created_by'], $day_schedule_shift['user_id'] );
							$day_schedule_shift['is_child'] = $permission->isChild( $day_schedule_shift['user_id'], $permission_children_ids );


							$day_schedule_shift['span_day'] = FALSE;
							$day_schedule_shift['span_day_split'] = TRUE;

							//var_dump($day_schedule_shift);
							$tmp_start_hour = TTDate::getHour( $day_schedule_shift['start_time'] );
							$tmp_end_hour = TTDate::getHour( $day_schedule_shift['end_time'] );
							if ( $tmp_end_hour < $tmp_start_hour ) {
								$tmp_end_hour = 24;
							}
							Debug::text(' Schedule: Start Date: '. TTDate::getDate('DATE+TIME', $day_schedule_shift['start_time']) .' End Date: '. TTDate::getDate('DATE+TIME',  $day_schedule_shift['end_time']) , __FILE__, __LINE__, __METHOD__,10);

							if ( $i == 0 OR $tmp_start_hour < $min_hour ) {
								$min_hour = $tmp_start_hour;
								//Always try to keep one hour before the actual min time,
								//otherwise the schedule looks cluttered.
								if ( $min_hour > 0 ) {
									$min_hour--;
								}
								//Debug::text(' aSetting Min Hour: '. $min_hour, __FILE__, __LINE__, __METHOD__,10);
							}

							if ( $i == 0 OR $tmp_end_hour > $max_hour ) {
								$max_hour = $tmp_end_hour;
								Debug::text(' aSetting Max Hour: '. $max_hour, __FILE__, __LINE__, __METHOD__,10);
								if ( $max_hour < 22 ) {
									$max_hour = $max_hour + 2;
								}
								Debug::text(' bSetting Max Hour: '. $max_hour, __FILE__, __LINE__, __METHOD__,10);
							}

							if ( TTDate::getDayOfMonth( $day_schedule_shift['start_time'] ) != TTDate::getDayOfMonth( ($day_schedule_shift['end_time']-1) ) ) { //-1 from end time to handle a 12:00AM end time without going to next day.
								Debug::text(' aSchedule Spans the Day boundary!', __FILE__, __LINE__, __METHOD__,10);
								$day_schedule_shift['span_day'] = TRUE;
								$min_hour = 0;
								$max_hour = 23;
							}

							if ( $day_schedule_shift['span_day'] == TRUE ) {

								//Cut shift into two days.
								$tmp_schedule_shift_day1 = $tmp_schedule_shift_day2 = $day_schedule_shift;
								$tmp_schedule_shift_day1['span_day_split'] = TRUE;
								$tmp_schedule_shift_day1['end_time'] = TTDate::getEndDayEpoch( $day_schedule_shift['start_time'] )+1;
								$tmp_schedule_shift_day2['start_time'] = TTDate::getBeginDayEpoch( $day_schedule_shift['end_time'] );
								$tmp_schedule_shift_day2['span_day_split'] = FALSE;

								$tmp_schedule_shifts[$day_epoch][$day_schedule_shift['branch']][$day_schedule_shift['department']][$day_schedule_shift['user_id']][] = $tmp_schedule_shift_day1;
								$tmp_schedule_shifts[TTDate::getISODateStamp($tmp_schedule_shift_day2['start_time'])][$day_schedule_shift['branch']][$day_schedule_shift['department']][$day_schedule_shift['user_id']][] = $tmp_schedule_shift_day2;

								Debug::text(' Shift SPans the Day Boundary: First End Date: '. TTDate::getDate('DATE+TIME', $tmp_schedule_shift_day1['end_time'] ) .' Second Start Date: '. TTDate::getDate('DATE+TIME', $tmp_schedule_shift_day2['start_time'] ) , __FILE__, __LINE__, __METHOD__,10);
							} else {
								$tmp_schedule_shifts[$day_epoch][$day_schedule_shift['branch']][$day_schedule_shift['department']][$day_schedule_shift['user_id']][] = $day_schedule_shift;
							}

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

							$i++;
						}
					}
				}

				$total_span_hours = 1;
				if ( isset($schedule_shift_totals) ) {
					//Find out how many hours to span
					$total_span_hours = abs($max_hour - $min_hour)+1;
					Debug::text(' Total Hours Span: '. $total_span_hours, __FILE__, __LINE__, __METHOD__,10);

					if ( $min_hour > $max_hour) {
						$tmp_max_hour = $max_hour+24;
						$tmp_min_hour = $min_hour;
					} else {
						$tmp_max_hour = $max_hour;
						$tmp_min_hour = $min_hour;
					}


					//Generate smarty array for table header
					for($i=$tmp_min_hour; $i <= $tmp_max_hour; $i++) {
						$header_hours[] = array('hour' => TTDate::getTimeStamp( "","","", $i ) );
					}
					unset($tmp_min_hour, $tmp_max_hour);
					//var_dump($header_hours);

					//Total up employees/time per day.
					if ( isset($schedule_shift_totals) ) {
						foreach( $schedule_shift_totals as $day_epoch => $total_arr) {
							if ( !isset($total_arr['users']) ) {
								$total_arr['users'] = array();
							}
							$schedule_shift_totals[$day_epoch]['total_users'] = count(array_unique($total_arr['users']));
						}
					}
					//var_dump($tmp_schedule_shifts);
				}

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
					//Remember that we have to handle split shifts here, so its more difficult to sort by last name.
					foreach ( $tmp_schedule_shifts as $day_epoch => $day_tmp_schedule_shift ) {
						foreach ( $day_tmp_schedule_shift as $branch => $department_schedule_shifts ) {
							foreach ( $department_schedule_shifts as $department => $department_schedule_shift ) {
								$tmp2_schedule_shifts[$day_epoch][$branch][$department] = Sort::multiSort( $department_schedule_shift, 'start_time' );
							}
						}
					}

					//Sort each department by start time.
					foreach ( $tmp2_schedule_shifts as $day_epoch => $day_tmp_schedule_shift ) {
						foreach ( $day_tmp_schedule_shift as $branch => $department_schedule_shifts ) {
							foreach ( $department_schedule_shifts as $department => $user_schedule_shifts ) {
								foreach ( $user_schedule_shifts as $user => $user_schedule_shift ) {
									$schedule_shifts[$day_epoch][$branch][$department][$user] = Sort::multiSort( $user_schedule_shift, 'start_time');
								}
							}
						}
					}
					unset($tmp_schedule_shifts, $tmp2_schedule_shifts);
					$tmp_schedule_shifts = $schedule_shifts;
				}
				//print_r($schedule_shifts);

				if ( isset($tmp_schedule_shifts) ) {
					//Format array so Smarty has an easier time.
					foreach ( $tmp_schedule_shifts as $day_epoch => $day_tmp_schedule_shift ) {
						foreach ( $day_tmp_schedule_shift as $branch => $department_schedule_shifts ) {
							foreach ( $department_schedule_shifts as $department => $user_schedule_shifts ) {
								foreach ( $user_schedule_shifts as $user_id => $user_schedule_shifts ) {
									$x=0;
									foreach( $user_schedule_shifts as $user_schedule_shift ) {

										if ( $x == 0 ) {
											$tmp_min_start_date = TTDate::getTimeStamp( date('Y', $user_schedule_shift['start_time']),date('m', $user_schedule_shift['start_time']),date('d', $user_schedule_shift['start_time']), $min_hour );
										} else {
											$tmp_min_start_date = $prev_user_schedule_shift['end_time'];
										}

										$off_duty = ($user_schedule_shift['start_time'] - $tmp_min_start_date) / 900; //15 Min increments
										$on_duty = ($user_schedule_shift['end_time'] - $user_schedule_shift['start_time']) / 900;
										$user_schedule_shift['off_duty'] = $off_duty;
										$user_schedule_shift['on_duty'] = $on_duty;

										$schedule_shifts[$day_epoch][$branch][$department][$user_id][] = $user_schedule_shift;

										$prev_user_schedule_shift = $user_schedule_shift;
										$x++;
									}
								}
							}
						}
					}
				}

				$viewData['header_hours'] = $header_hours;
				$viewData['total_span_hours'] = $total_span_hours;
				$viewData['total_span_columns'] = ($total_span_hours*4)+1;
				$viewData['column_widths'] = round( floor(99 / $total_span_hours) / 4 ) ;

				$calendar_array = TTDate::getCalendarArray($start_date, $end_date, $current_user_prefs->getStartWeekDay(), FALSE);
				//var_dump($calendar_array);
				$viewData['calendar_array'] = $calendar_array;

				$hlf = new HolidayListFactory(); 
				$holiday_array = $hlf->getArrayByPolicyGroupUserId( $user_ids, $start_date, $end_date );
				//var_dump($holiday_array);

				$viewData['holidays'] = $holiday_array;
				$viewData['schedule_shifts'] = $schedule_shifts;
				$viewData['schedule_shift_totals'] = $schedule_shift_totals;

				$viewData['do'] = $do ;

				break;
		}

		return view('schedule/ViewScheduleLinear', $viewData);
	}
}
?>