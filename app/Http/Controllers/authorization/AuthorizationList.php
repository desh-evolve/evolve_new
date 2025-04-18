<?php

namespace App\Http\Controllers\authorization;

use App\Http\Controllers\Controller;
use App\Models\Accrual\AccrualBalanceFactory;
use App\Models\Accrual\AccrualBalanceListFactory;
use App\Models\Accrual\AccrualFactory;
use App\Models\Accrual\AccrualListFactory;
use Illuminate\Http\Request;

use App\Models\Core\Environment;
use App\Models\Core\Debug;
use App\Models\Core\FormVariables;
use App\Models\Core\Misc;
use App\Models\Core\Option;
use App\Models\Core\Pager;
use App\Models\Core\Redirect;
use App\Models\Core\TTDate;
use App\Models\Core\URLBuilder;
use App\Models\Hierarchy\HierarchyLevelListFactory;
use App\Models\Hierarchy\HierarchyListFactory;
use App\Models\Hierarchy\HierarchyObjectTypeListFactory;
use App\Models\PayPeriod\PayPeriodTimeSheetVerifyListFactory;
use App\Models\Policy\AccrualPolicyListFactory;
use App\Models\Request\RequestListFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;

class AuthorizationList extends Controller
{
    protected $permission;
    protected $company;
    protected $userPrefs;

    public function __construct()
    {
        $basePath = Environment::getBasePath();
        require_once($basePath . '/app/Helpers/global.inc.php');
        require_once($basePath . '/app/Helpers/Interface.inc.php');

        $this->userPrefs = View::shared('current_user_prefs');
        $this->company = View::shared('current_company');
        $this->permission = View::shared('permission');

    }

	public function index() {
		/*
        if ( !$permission->Check('authorization','enabled')
				OR !( $permission->Check('authorization','view') ) ) {

			$permission->Redirect( FALSE ); //Redirect
		}
        */

        $viewData['title'] = 'Authorization List';

		URLBuilder::setURL($_SERVER['SCRIPT_NAME'],
			array(
				'sort_column' => $sort_column,
				'sort_order' => $sort_order,
				'page' => $page
			) 
		);

		$sort_array = NULL;
		if ( $sort_column != '' ) {
			$sort_array = array(Misc::trimSortPrefix($sort_column) => $sort_order);
		}

		$ulf = new UserListFactory();
		$hlf = new HierarchyListFactory();
		$hllf = new HierarchyLevelListFactory(); 
		$hotlf = new HierarchyObjectTypeListFactory();

		if ( $permission->Check('request','authorize') ) {

			//
			//Missed Punch: request_punch
			//
			$hierarchy_levels['request_punch'] = $hllf->getLevelsAndHierarchyControlIDsByUserIdAndObjectTypeID( $current_user->getId(), 1010 ); //Missed Punch
			//Debug::Arr( $hierarchy_levels['request_punch'], 'Request Punch Levels', __FILE__, __LINE__, __METHOD__,10);

			$selected_level_arr['request_punch'] = 0;
			if ( isset($selected_levels['request_punch']) AND isset($hierarchy_levels['request_punch'][$selected_levels['request_punch']]) ) {
				$selected_level_arr['request_punch'] = $hierarchy_levels['request_punch'][$selected_levels['request_punch']];
			} elseif ( isset($hierarchy_levels['request_punch'][1]) ) {
				$selected_level_arr['request_punch'] = $hierarchy_levels['request_punch'][1];
			}
			//Debug::Arr( $selected_level_arr['request_punch'], 'Request Punch Selected Level Arr: ', __FILE__, __LINE__, __METHOD__,10);

			if ( is_array($selected_level_arr['request_punch']) ) {
				$rlf = new RequestListFactory(); 
				$rlf->getByHierarchyLevelMapAndTypeAndStatusAndNotAuthorized($selected_level_arr['request_punch'], 10, 30, NULL, NULL, NULL, $sort_array ); //Missed Punch
				foreach( $rlf->rs as $r_obj) {
					$rlf->data = (array)$r_obj;
					$r_obj = $rlf;
					//Grab authorizations for this object.
					$requests['request_punch'][] = array(
														'id' => $r_obj->getId(),
														'user_date_id' => $r_obj->getId(),
														'user_id' => $r_obj->getUserDateObject()->getUser(),
														'user_full_name' => $r_obj->getUserDateObject()->getUserObject()->getFullName(),
														'date_stamp' => $r_obj->getUserDateObject()->getDateStamp(),
														'type_id' => $r_obj->getType(),
														'type' => Option::getByKey($r_obj->getType(), $rlf->getOptions('type') ),
														'status_id' => $r_obj->getStatus(),
														'status' => Option::getByKey($r_obj->getStatus(), $rlf->getOptions('status') ),
														'created_date' => $r_obj->getCreatedDate(),
													);
				}
			} else {
				Debug::Text( 'No request_punch hierarchy information found...', __FILE__, __LINE__, __METHOD__,10);
			}


			//
			//Missed Punch: request_punch_adjust
			//
			$hierarchy_levels['request_punch_adjust'] = $hllf->getLevelsAndHierarchyControlIDsByUserIdAndObjectTypeID( $current_user->getId(), 1020 ); //Punch Adjust
			//Debug::Arr( $hierarchy_levels['request_punch_adjust'], 'Request Punch Adjust Levels', __FILE__, __LINE__, __METHOD__,10);

			$selected_level_arr['request_punch_adjust'] = 0;
			if ( isset($selected_levels['request_punch_adjust']) AND isset($hierarchy_levels['request_punch_adjust'][$selected_levels['request_punch_adjust']]) ) {
				$selected_level_arr['request_punch_adjust'] = $hierarchy_levels['request_punch_adjust'][$selected_levels['request_punch_adjust']];
			} elseif ( isset($hierarchy_levels['request_punch_adjust'][1]) ) {
				$selected_level_arr['request_punch_adjust'] = $hierarchy_levels['request_punch_adjust'][1];
			}
			//Debug::Arr( $selected_level_arr['request_punch_adjust'], 'Request Punch Selected Level Arr: ', __FILE__, __LINE__, __METHOD__,10);

			if ( is_array($selected_level_arr['request_punch_adjust']) ) {
				$rlf = new RequestListFactory();
				$rlf->getByHierarchyLevelMapAndTypeAndStatusAndNotAuthorized($selected_level_arr['request_punch_adjust'], 20, 30, NULL, NULL, NULL, $sort_array ); //Punch Adjust
				foreach( $rlf->rs as $r_obj) {
					$rlf->data = (array)$r_obj;
					$r_obj = $rlf;
					//Grab authorizations for this object.
					$requests['request_punch_adjust'][] = array(
														'id' => $r_obj->getId(),
														'user_date_id' => $r_obj->getId(),
														'user_id' => $r_obj->getUserDateObject()->getUser(),
														'user_full_name' => $r_obj->getUserDateObject()->getUserObject()->getFullName(),
														'date_stamp' => $r_obj->getUserDateObject()->getDateStamp(),
														'type_id' => $r_obj->getType(),
														'type' => Option::getByKey($r_obj->getType(), $rlf->getOptions('type') ),
														'status_id' => $r_obj->getStatus(),
														'status' => Option::getByKey($r_obj->getStatus(), $rlf->getOptions('status') ),
														'created_date' => $r_obj->getCreatedDate(),
													);
				}
			} else {
				Debug::Text( 'No request_punch hierarchy information found...', __FILE__, __LINE__, __METHOD__,10);
			}


			//
			//Missed Punch: request_absence
			//
			$hierarchy_levels['request_absence'] = $hllf->getLevelsAndHierarchyControlIDsByUserIdAndObjectTypeID( $current_user->getId(), 1030 ); //Absence
			//Debug::Arr( $hierarchy_levels['request_absence'], 'Request Punch Adjust Levels', __FILE__, __LINE__, __METHOD__,10);

			$selected_level_arr['request_absence'] = 0;
			if ( isset($selected_levels['request_absence']) AND isset($hierarchy_levels['request_absence'][$selected_levels['request_absence']]) ) {
				$selected_level_arr['request_absence'] = $hierarchy_levels['request_absence'][$selected_levels['request_absence']];
			} elseif ( isset($hierarchy_levels['request_absence'][1]) ) {
				$selected_level_arr['request_absence'] = $hierarchy_levels['request_absence'][1];
			}
			//Debug::Arr( $selected_level_arr['request_absence'], 'Request Punch Selected Level Arr: ', __FILE__, __LINE__, __METHOD__,10);

			if ( is_array($selected_level_arr['request_absence']) ) {
				$rlf = new RequestListFactory();
				$rlf->getByHierarchyLevelMapAndTypeAndStatusAndNotAuthorized($selected_level_arr['request_absence'], 30, 30, NULL, NULL, NULL, $sort_array ); //Absence
				foreach( $rlf->rs as $r_obj) {
					$rlf->data = (array)$r_obj;
					$r_obj = $rlf;
					//Grab authorizations for this object.
					$requests['request_absence'][] = array(
														'id' => $r_obj->getId(),
														'user_date_id' => $r_obj->getId(),
														'user_id' => $r_obj->getUserDateObject()->getUser(),
														'user_full_name' => $r_obj->getUserDateObject()->getUserObject()->getFullName(),
														'date_stamp' => $r_obj->getUserDateObject()->getDateStamp(),
														'type_id' => $r_obj->getType(),
														'type' => Option::getByKey($r_obj->getType(), $rlf->getOptions('type') ),
														'status_id' => $r_obj->getStatus(),
														'status' => Option::getByKey($r_obj->getStatus(), $rlf->getOptions('status') ),
														'created_date' => $r_obj->getCreatedDate(),
													);
				}
			} else {
				Debug::Text( 'No request_punch hierarchy information found...', __FILE__, __LINE__, __METHOD__,10);
			}


			//
			//Missed Punch: request_schedule
			//
			$hierarchy_levels['request_schedule'] = $hllf->getLevelsAndHierarchyControlIDsByUserIdAndObjectTypeID( $current_user->getId(), 1040 ); //Schedule
			//Debug::Arr( $hierarchy_levels['request_schedule'], 'Request Punch Adjust Levels', __FILE__, __LINE__, __METHOD__,10);

			$selected_level_arr['request_schedule'] = 0;
			if ( isset($selected_levels['request_schedule']) AND isset($hierarchy_levels['request_schedule'][$selected_levels['request_schedule']]) ) {
				$selected_level_arr['request_schedule'] = $hierarchy_levels['request_schedule'][$selected_levels['request_schedule']];
			} elseif ( isset($hierarchy_levels['request_schedule'][1]) ) {
				$selected_level_arr['request_schedule'] = $hierarchy_levels['request_schedule'][1];
			}
			//Debug::Arr( $selected_level_arr['request_schedule'], 'Request Punch Selected Level Arr: ', __FILE__, __LINE__, __METHOD__,10);

			if ( is_array($selected_level_arr['request_schedule']) ) {
				$rlf = new RequestListFactory();
				$rlf->getByHierarchyLevelMapAndTypeAndStatusAndNotAuthorized($selected_level_arr['request_schedule'], 40, 30, NULL, NULL, NULL, $sort_array ); //Schedule
				foreach( $rlf->rs as $r_obj) {
					$rlf->data = (array)$r_obj;
					$r_obj = $rlf;
					//Grab authorizations for this object.
					$requests['request_schedule'][] = array(
														'id' => $r_obj->getId(),
														'user_date_id' => $r_obj->getId(),
														'user_id' => $r_obj->getUserDateObject()->getUser(),
														'user_full_name' => $r_obj->getUserDateObject()->getUserObject()->getFullName(),
														'date_stamp' => $r_obj->getUserDateObject()->getDateStamp(),
														'type_id' => $r_obj->getType(),
														'type' => Option::getByKey($r_obj->getType(), $rlf->getOptions('type') ),
														'status_id' => $r_obj->getStatus(),
														'status' => Option::getByKey($r_obj->getStatus(), $rlf->getOptions('status') ),
														'created_date' => $r_obj->getCreatedDate(),
													);
				}
			} else {
				Debug::Text( 'No request_punch hierarchy information found...', __FILE__, __LINE__, __METHOD__,10);
			}

			//
			//Missed Punch: request_other
			//
			$hierarchy_levels['request_other'] = $hllf->getLevelsAndHierarchyControlIDsByUserIdAndObjectTypeID( $current_user->getId(), 1100 ); //Other
			//Debug::Arr( $hierarchy_levels['request_other'], 'Request Punch Adjust Levels', __FILE__, __LINE__, __METHOD__,10);

			$selected_level_arr['request_other'] = 0;
			if ( isset($selected_levels['request_other']) AND isset($hierarchy_levels['request_other'][$selected_levels['request_other']]) ) {
				$selected_level_arr['request_other'] = $hierarchy_levels['request_other'][$selected_levels['request_other']];
			} elseif ( isset($hierarchy_levels['request_other'][1]) ) {
				$selected_level_arr['request_other'] = $hierarchy_levels['request_other'][1];
			}
			//Debug::Arr( $selected_level_arr['request_other'], 'Request Punch Selected Level Arr: ', __FILE__, __LINE__, __METHOD__,10);

			if ( is_array($selected_level_arr['request_other']) ) {
				$rlf = new RequestListFactory();
				$rlf->getByHierarchyLevelMapAndTypeAndStatusAndNotAuthorized($selected_level_arr['request_other'], 100, 30, NULL, NULL, NULL, $sort_array ); //Other
				foreach( $rlf->rs as $r_obj) {
					$rlf->data = (array)$r_obj;
					$r_obj = $rlf;
					//Grab authorizations for this object.
					$requests['request_other'][] = 	array(
														'id' => $r_obj->getId(),
														'user_date_id' => $r_obj->getId(),
														'user_id' => $r_obj->getUserDateObject()->getUser(),
														'user_full_name' => $r_obj->getUserDateObject()->getUserObject()->getFullName(),
														'date_stamp' => $r_obj->getUserDateObject()->getDateStamp(),
														'type_id' => $r_obj->getType(),
														'type' => Option::getByKey($r_obj->getType(), $rlf->getOptions('type') ),
														'status_id' => $r_obj->getStatus(),
														'status' => Option::getByKey($r_obj->getStatus(), $rlf->getOptions('status') ),
														'created_date' => $r_obj->getCreatedDate(),
													);
				}
			} else {
				Debug::Text( 'No request_punch hierarchy information found...', __FILE__, __LINE__, __METHOD__,10);
			}

			if ( isset($requests) ) {
				$viewData['requests'] = $requests;
			}
		}

		if ( $permission->Check('punch','authorize') ) {
			//Debug::Text('TimeSheet: Selected Level: '. $selected_levels['timesheet'], __FILE__, __LINE__, __METHOD__,10);

			//$timesheet_levels = $hllf->getLevelsAndHierarchyControlIDsByUserIdAndObjectTypeID( $current_user->getId(), 90 );
			$hierarchy_levels['timesheet'] = $hllf->getLevelsAndHierarchyControlIDsByUserIdAndObjectTypeID( $current_user->getId(), 90 );
			//Debug::Arr( $timesheet_levels , 'TimeSheet Levels', __FILE__, __LINE__, __METHOD__,10);

			if ( isset($selected_levels['timesheet']) AND isset($hierarchy_levels['timesheet'][$selected_levels['timesheet']]) ) {
				$selected_level_arr['timesheet'] = $hierarchy_levels['timesheet'][$selected_levels['timesheet']];
				Debug::Text(' Switching Levels to Level: '. key($selected_level_arr['timesheet']), __FILE__, __LINE__, __METHOD__,10);
			} elseif ( isset($hierarchy_levels['timesheet'][1]) ) {
				$selected_level_arr['timesheet'] = $hierarchy_levels['timesheet'][1];
			} else {
				Debug::Text( 'No TimeSheet Levels... Not in hierarchy?', __FILE__, __LINE__, __METHOD__,10);
				$selected_level_arr['timesheet'] = 0;
			}
			//Debug::Arr( $timesheet_selected_level, 'TimeSheet Selected Level Arr: ', __FILE__, __LINE__, __METHOD__,10);

			if ( is_array($selected_level_arr['timesheet']) ) {
				$pptsvlf = new PayPeriodTimeSheetVerifyListFactory();
				$pptsvlf->getByHierarchyLevelMapAndStatusAndNotAuthorized($selected_level_arr['timesheet'], 30, NULL, NULL, NULL, $sort_array );
				foreach( $pptsvlf->rs as $pptsv_obj) {
					$pptsvlf->data = (array)$pptsv_obj;
					$pptsv_obj = $pptsvlf;
					//Grab authorizations for this object.
					$timesheets[] = array(
										'id' => $pptsv_obj->getId(),
										'pay_period_id' => $pptsv_obj->getPayPeriod(),
										'user_id' => $pptsv_obj->getUser(),
										'user_full_name' => $pptsv_obj->getUserObject()->getFullName(),
										'pay_period_start_date' => $pptsv_obj->getPayPeriodObject()->getStartDate(),
										'pay_period_end_date' => $pptsv_obj->getPayPeriodObject()->getEndDate(),
										'status_id' => $pptsv_obj->getStatus(),
										'status' => Option::getByKey($pptsv_obj->getStatus(), $pptsvlf->getOptions('status') ),
									);
				}
				
				$viewData['timesheets'] = $timesheets;
			} else {
				Debug::Text( 'No hierarchy information found...', __FILE__, __LINE__, __METHOD__,10);
			}
		}

		$viewData['selected_levels'] = $selected_levels ;
		$viewData['selected_level_arr'] = $selected_level_arr;
		$viewData['hierarchy_levels'] = $hierarchy_levels;
		$viewData['sort_column'] = $sort_column ;
		$viewData['sort_order'] = $sort_order ;

		return view('authorization/AuthorizationList', $viewData);

	}

	public function submit(){
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);
	}
}

?>