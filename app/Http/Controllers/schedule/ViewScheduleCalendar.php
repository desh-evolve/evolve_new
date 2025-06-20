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

class ViewScheduleCalendar extends Controller
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
				
		if ( !$permission->Check('schedule','enabled')
				OR !( $permission->Check('schedule','view') OR $permission->Check('schedule','view_own') ) ) {

			$permission->Redirect( FALSE ); //Redirect

		}

		$viewData['title'] = 'My Schedule';

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

		//===================================================================================
		$action = '';
		if (isset($_POST['action'])) {
			$action = trim($_POST['action']);
		} elseif (isset($_GET['action'])) {
			$action = trim($_GET['action']);
		}
		$action = !empty($action) ? strtolower(str_replace(' ', '_', $action)) : '';
		//===================================================================================

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

				$viewData['calendar_array'] = $calendar_array;

				$ulf = new UserListFactory();
				$user_obj = $ulf->getById( $user_id )->getCurrent();

				/*
				$holiday = new Holiday();
				$holiday->GetByCountryAndProvince($user_obj->getCountry(), $user_obj->getProvince() );
				*/
				$hlf = new HolidayListFactory(); 
				$holiday_array = $hlf->getArrayByPolicyGroupUserId( $user_id, $start_date, $end_date );
				//var_dump($holiday_array);

				$viewData['holidays'] = $holiday_array;

				$viewData['filter_start_date'] = $filter_start_date;
				$viewData['filter_end_date'] = $filter_end_date;

				$user_options = UserListFactory::getByCompanyIdArray( $current_company->getId(), FALSE );
				$viewData['user_options'] = $user_options;
				$viewData['filter_user_id'] = $user_id;

				$viewData['schedule_shifts'] = $default_schedule_shifts;

				$viewData['current_epoch'] = TTDate::getBeginDayEpoch() ;

				break;
		}

		return view('schedule/ViewScheduleCalendar', $viewData);
	}
}
?>