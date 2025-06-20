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
use App\Models\Schedule\ScheduleFactory;
use App\Models\Users\UserGenericDataFactory;
use App\Models\Users\UserGenericDataListFactory;
use App\Models\Users\UserGroupListFactory;
use App\Models\Users\UserListFactory;
use App\Models\Users\UserTitleListFactory;
use Illuminate\Support\Facades\View;

class ViewSchedule extends Controller
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
														'generic_data',
														'filter_data',
														'page',
														'sort_column',
														'sort_order',
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
			$filter_data['start_date'] = TTDate::getBeginWeekEpoch( time() );
		}

		//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
		$permission_children_ids = array();
		if ( $permission->Check('schedule','view') == FALSE ) {
			$hlf = new HierarchyListFactory();
			$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );

			if ( $permission->Check('schedule','view_child') == FALSE ) {
				$permission_children_ids = array();
			}
			if ( $permission->Check('schedule','view_own') ) {
				$permission_children_ids[] = $current_user->getId();
			}

			$filter_data['permission_children_ids'] = $permission_children_ids;
		}

		$ugdlf = new UserGenericDataListFactory();
		$ugdf = new UserGenericDataFactory();

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
			case 'print_schedule':
				//Debug::setVerbosity(11);
				Debug::Text('Print Schedule:', __FILE__, __LINE__, __METHOD__,10);
				if ( !isset($filter_data['show_days']) OR ( isset($filter_data['show_days']) AND $filter_data['show_days'] == '' ) ) {
					$filter_data['show_days'] = 4;
				}
				if ( !isset($filter_data['group_schedule']) ) {
					$filter_data['group_schedule'] = FALSE;
				}

				$filter_data['start_date'] = TTDate::getBeginWeekEpoch( TTDate::getBeginDayEpoch( $filter_data['start_date'] ), $current_user_prefs->getStartWeekDay() );
				Debug::Text('Start Date: '. TTDate::getDate('DATE+TIME', $filter_data['start_date']), __FILE__, __LINE__, __METHOD__,10);
				$filter_data['end_date'] = $filter_data['start_date'] + (($filter_data['show_days']*7)*86400-3601);

				$sf = new ScheduleFactory(); 
				$output = $sf->getSchedule( $filter_data, $current_user_prefs->getStartWeekDay(), $filter_data['group_schedule'] );

				//print_r($output);
				if ( $output == FALSE ) {
					echo _('No Schedule to print!')."<br>\n";
				} else {
					if ( Debug::getVerbosity() < 11 ) {
						Misc::FileDownloadHeader('schedule.pdf', 'application/pdf', strlen($output));
						echo $output;
					} else {
						Debug::Display();
					}
				}
				exit;
				break;
			case 'filter':
				if ( $filter_start_date != '' AND $filter_show_days != '' ) {
					$start_date = $filter_start_date = TTDate::getBeginDayEpoch( $filter_start_date );
					$end_date = $start_date + ($filter_show_days*86400-3600);
				}
			case 'delete':
			case 'save':
				Debug::Text('Action: '. $action, __FILE__, __LINE__, __METHOD__,10);

				//$generic_data['id'] = UserGenericDataFactory::reportFormDataHandler( $action, $filter_data, $generic_data, URLBuilder::getURL(NULL, $_SERVER['SCRIPT_NAME']) );
				unset($generic_data['name']);
			default:

				if ( $action == 'load' ) {
					Debug::Text('Loading Report!', __FILE__, __LINE__, __METHOD__,10);

					//extract( UserGenericDataFactory::getReportFormData( $generic_data['id'] ) );
				} elseif ( $action == '' ) {
					//Check for default saved report first.
					$ugdlf->getByUserIdAndScriptAndDefault( $current_user->getId(), $_SERVER['SCRIPT_NAME'] );
					if ( $ugdlf->getRecordCount() > 0 ) {
						Debug::Text('Found Default Report!', __FILE__, __LINE__, __METHOD__,10);

						$ugd_obj = $ugdlf->getCurrent();
						$filter_data = $ugd_obj->getData();
						$generic_data['id'] = $ugd_obj->getId();
					} else {
						//Default selections
						$filter_data['user_status_ids'] = array( -1 );
						$filter_data['default_branch_ids'] = array( -1 );
						$filter_data['default_department_ids'] = array( -1 );
						$filter_data['schedule_branch_ids'] = array( -1 );
						$filter_data['schedule_department_ids'] = array( -1 );
						$filter_data['user_title_ids'] = array( -1 );
						$filter_data['group_ids'] = array( -1 );
					}
				}

				$ulf = new UserListFactory();
				$all_array_option = array('-1' => _('-- All --'));

				if ( !isset($filter_data['show_days']) OR ( isset($filter_data['show_days']) AND $filter_data['show_days'] == '' ) ) {
					$filter_data['show_days'] = 4;
				}

				if ( !isset( $filter_data['start_date']) OR $filter_data['start_date'] == '' OR $filter_data['show_days'] == '' ) {
					$start_date = $filter_data['start_date'] = TTDate::getBeginWeekEpoch( TTDate::getTime(), $current_user_prefs->getStartWeekDay() );
					$end_date = $start_date + (7*(86400-3600));
				}

				if ( !isset($filter_data['include_user_ids']) ) {
					$filter_data['include_user_ids'] = NULL;
				}
				$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), array('permission_children_ids' => $permission_children_ids ) );
				$user_options = $ulf->getArrayByListFactory( $ulf, FALSE, TRUE );

				$filter_data['src_include_user_options'] = Misc::arrayDiffByKey( (array)$filter_data['include_user_ids'], $user_options );
				$filter_data['selected_include_user_options'] = Misc::arrayIntersectByKey( (array)$filter_data['include_user_ids'], $user_options );

				//Get exclude employee list
				if ( !isset($filter_data['exclude_user_ids']) ) {
					$filter_data['exclude_user_ids'] = NULL;
				}
				$exclude_user_options = Misc::prependArray( $all_array_option, $ulf->getArrayByListFactory( $ulf, FALSE, TRUE ) );
				$filter_data['src_exclude_user_options'] = Misc::arrayDiffByKey( (array)$filter_data['exclude_user_ids'], $user_options );
				$filter_data['selected_exclude_user_options'] = Misc::arrayIntersectByKey( (array)$filter_data['exclude_user_ids'], $user_options );

				//Get Employee Groups
				if ( !isset($filter_data['group_ids']) ) {
					$filter_data['group_ids'] = NULL;
				}
				$uglf = new UserGroupListFactory();
				$group_options = Misc::prependArray( $all_array_option, $uglf->getArrayByNodes( FastTree::FormatArray( $uglf->getByCompanyIdArray( $current_company->getId() ), 'TEXT', TRUE) ) );
				$filter_data['src_group_options'] = Misc::arrayDiffByKey( (array)$filter_data['group_ids'], $group_options );
				$filter_data['selected_group_options'] = Misc::arrayIntersectByKey( (array)$filter_data['group_ids'], $group_options );

				//Get branches
				if ( !isset($filter_data['schedule_branch_ids']) ) {
					$filter_data['schedule_branch_ids'] = NULL;
				}
				if ( !isset($filter_data['default_branch_ids']) ) {
					$filter_data['default_branch_ids'] = NULL;
				}
				$blf = new BranchListFactory();
				$blf->getByCompanyId( $current_company->getId() );
				$branch_options = Misc::prependArray( $all_array_option, $blf->getArrayByListFactory( $blf, FALSE, TRUE ) );
				$filter_data['src_schedule_branch_options'] = Misc::arrayDiffByKey( (array)$filter_data['schedule_branch_ids'], $branch_options );
				$filter_data['selected_schedule_branch_options'] = Misc::arrayIntersectByKey( (array)$filter_data['schedule_branch_ids'], $branch_options );
				$filter_data['src_default_branch_options'] = Misc::arrayDiffByKey( (array)$filter_data['default_branch_ids'], $branch_options );
				$filter_data['selected_default_branch_options'] = Misc::arrayIntersectByKey( (array)$filter_data['default_branch_ids'], $branch_options );

				//Get departments
				if ( !isset($filter_data['schedule_department_ids']) ) {
					$filter_data['schedule_department_ids'] = NULL;
				}
				if ( !isset($filter_data['default_department_ids']) ) {
					$filter_data['default_department_ids'] = NULL;
				}
				$dlf = new DepartmentListFactory();
				$dlf->getByCompanyId( $current_company->getId() );
				$department_options = Misc::prependArray( $all_array_option, $dlf->getArrayByListFactory( $dlf, FALSE, TRUE ) );
				$filter_data['src_schedule_department_options'] = Misc::arrayDiffByKey( (array)$filter_data['schedule_department_ids'], $department_options );
				$filter_data['selected_schedule_department_options'] = Misc::arrayIntersectByKey( (array)$filter_data['schedule_department_ids'], $department_options );
				$filter_data['src_default_department_options'] = Misc::arrayDiffByKey( (array)$filter_data['default_department_ids'], $department_options );
				$filter_data['selected_default_department_options'] = Misc::arrayIntersectByKey( (array)$filter_data['default_department_ids'], $department_options );

				//Get employee titles
				if ( !isset($filter_data['user_title_ids']) ) {
					$filter_data['user_title_ids'] = NULL;
				}
				$utlf = new UserTitleListFactory();
				$utlf->getByCompanyId( $current_company->getId() );
				$user_title_options = Misc::prependArray( $all_array_option, $utlf->getArrayByListFactory( $utlf, FALSE, TRUE ) );
				$filter_data['src_user_title_options'] = Misc::arrayDiffByKey( (array)$filter_data['user_title_ids'], $user_title_options );
				$filter_data['selected_user_title_options'] = Misc::arrayIntersectByKey( (array)$filter_data['user_title_ids'], $user_title_options );

				$filter_data['show_days_options'] = array( 1 => _('1 Week'), 2 => _('2 Weeks'), 3 => _('3 Weeks'), 4 => _('4 Weeks'), 5 => _('5 Weeks'), 6 => _('6 Weeks'), 7 => _('7 Weeks'), 8 => _('8 Weeks'), 9 => _('9 Weeks'), 10 => _('10 Weeks'), 11 => _('11 Weeks'), 12 => _('12 Weeks'));
				$filter_data['view_type_options'] = array( 10 => _('Month'), 20 => _('Week'), 30 => _('Day') );

				$saved_report_options = $ugdlf->getByUserIdAndScriptArray( $current_user->getId(), $_SERVER['SCRIPT_NAME']);
				$generic_data['saved_report_options'] = $saved_report_options;
				$viewData['generic_data'] = $generic_data;
				$viewData['filter_data'] = $filter_data;
				$viewData['ugdf'] = $ugdf;

				break;
		}

		return view('schedule/ViewSchedule', $viewData);
	}
}
?>