<?php

namespace App\Http\Controllers\schedule;

use App\Http\Controllers\Controller;
use App\Models\Company\BranchListFactory;
use App\Models\Core\Debug;
use Illuminate\Http\Request;

use App\Models\Core\Environment;
use App\Models\Core\FormVariables;
use App\Models\Core\Misc;
use App\Models\Core\Redirect;
use App\Models\Core\TTDate;
use App\Models\Core\URLBuilder;
use App\Models\Department\DepartmentListFactory;
use App\Models\Hierarchy\HierarchyListFactory;
use App\Models\Policy\AbsencePolicyListFactory;
use App\Models\Policy\SchedulePolicyListFactory;
use App\Models\Schedule\ScheduleFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;

class AddMassSchedule extends Controller
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
				OR !( $permission->Check('schedule','edit')
						OR $permission->Check('schedule','edit_own')
						OR $permission->Check('schedule','edit_child')
						) ) {
			$permission->Redirect( FALSE ); //Redirect
		}

		$viewData['title'] = 'Mass Schedule';

		/*
		* Get FORM variables
		*/
		extract	(FormVariables::GetVariables(
			array	(
					'action',
					'id',
					'data',
					'filter_user_id'
					) ) );

		$data = Misc::preSetArrayValues( $data, array( 'start_date_stamp', 'end_date_stamp', 'start_time', 'end_time' ), NULL );

		if ( isset($data) ) {
			if ( $data['start_date_stamp'] != ''
				AND !is_numeric($data['start_date_stamp'])
				AND $data['end_date_stamp'] != ''
				AND !is_numeric($data['end_date_stamp'])
				AND $data['start_time'] != ''
				AND !is_numeric($data['end_time'])
				AND $data['end_time'] != ''
				AND !is_numeric($data['end_time'])
			) {
				$data['start_full_time_stamp'] = TTDate::parseDateTime($data['start_date_stamp'].' '.$data['start_time']);
				$data['end_full_time_stamp'] = TTDate::parseDateTime($data['end_date_stamp'].' '.$data['end_time']);
			} else {
				$data['start_full_time_stamp'] = NULL;
				$data['end_full_time_stamp'] = NULL;
			}

			if ( $data['start_date_stamp'] != '') {
				$data['start_date_stamp'] = TTDate::parseDateTime($data['start_date_stamp']);
			}
			if ( $data['end_date_stamp'] != '') {
				$data['end_date_stamp'] = TTDate::parseDateTime($data['end_date_stamp']);
			}

			if ( $data['start_time'] != '') {
				$data['parsed_start_time'] = TTDate::strtotime( $data['start_time'], $data['start_date_stamp'] ) ;
			}
			if ( $data['end_time'] != '') {
				Debug::Text('End Time: '. $data['end_time'] .' Date Stamp: '. $data['start_date_stamp'] , __FILE__, __LINE__, __METHOD__,10);
				$data['parsed_end_time'] = strtotime( $data['end_time'], $data['start_date_stamp'] ) ;
				Debug::Text('bEnd Time: '. $data['end_time'] .' - '. TTDate::getDate('DATE+TIME',$data['end_time']) , __FILE__, __LINE__, __METHOD__,10);
			}
		}

		//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
		$hlf = new HierarchyListFactory(); 
		$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );
		//Debug::Arr($permission_children_ids,'Permission Children Ids:', __FILE__, __LINE__, __METHOD__,10);
		$filter_data = array();
		if ( $permission->Check('schedule','edit') == FALSE ) {
		if ( $permission->Check('schedule','edit_child') ) {
		$filter_data['permission_children_ids'] = $permission_children_ids;
		}
		if ( $permission->Check('schedule','edit_own') ) {
		$filter_data['permission_children_ids'][] = $current_user->getId();
		}
		}

		$sf = new ScheduleFactory(); 
		$ulf = new UserListFactory();

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
			case 'submit':
			//Debug::setVerbosity(11);
			Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

			$fail_transaction = FALSE;
			//echo '<pre>'; print_r($data);         die;

			if ( TTDate::getDayDifference( $data['start_date_stamp'], $data['end_date_stamp']) > 31 ) {
				Debug::Text('Date Range Exceeds 31 days, truncating', __FILE__, __LINE__, __METHOD__,10);
				$sf->Validator->isTrue('date_stamp', FALSE, _('Date range exceeds the maximum of 31 days') );
			}

			if ( !( isset($filter_user_id) AND is_array($filter_user_id) AND count($filter_user_id) > 0 )  ) {
				$sf->Validator->isTrue('user_id', FALSE, _('Please select at least one employee') );
			}

			if ( !( $data['start_full_time_stamp'] != '' AND $data['end_full_time_stamp'] != ''
			AND $data['start_full_time_stamp'] >= (time()-86400*365) AND $data['end_full_time_stamp'] <= (time()+86400*365) ) ) {
				$sf->Validator->isTrue('date_stamp', FALSE, _('Start or End dates are invalid') );
			}

			if ( $sf->Validator->isValid() ) {
				Redirect::Page( URLBuilder::getURL( array('action' => 'add_mass_schedule', 'filter_user_id' => $filter_user_id, 'data' => $data ), '/progress_bar_control') );
			}
			default:
			
			if ( $action != 'submit' AND !is_array($data) ) {
				Debug::Text(' ID was NOT passed: '. $id, __FILE__, __LINE__, __METHOD__,10);

				$user_id = NULL;
				$user_date_id = NULL;
				$user_full_name = NULL;
				$user_default_branch = NULL;
				$user_default_department = NULL;
				$pay_period_is_locked = FALSE;

				$time_stamp = $start_date_stamp = $end_date_stamp = TTDate::getBeginDayEpoch( TTDate::getTime() ) + (3600*12); //Noon

				$data = array(
					//'user_id' => $user_id,
					'start_date_stamp' => $start_date_stamp,
					'end_date_stamp' => $end_date_stamp,
					//'user_date_id' => $user_date_id,
					//'user_full_name' => $user_full_name,
					'start_time' => strtotime('08:00 AM'),
					'parsed_start_time' => strtotime('08:00 AM'),
					'end_time' => strtotime('05:00 PM'),
					'parsed_end_time' => strtotime('05:00 PM'),
					'total_time' => 3600*9,
					'branch_id' => $user_default_branch,
					'department_id' => $user_default_department,
					//'pay_period_is_locked' => $pay_period_is_locked
					'dow' => array(1 => TRUE, 2 => TRUE, 3 => TRUE, 4 => TRUE, 5 => TRUE)
				);
			}
			//var_dump($data);

			$ulf = new UserListFactory();
			$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
			$src_user_options = UserListFactory::getArrayByListFactory( $ulf, FALSE, FALSE );

			$user_options = Misc::arrayDiffByKey( (array)$filter_user_id, $src_user_options );
			$filter_user_options = Misc::arrayIntersectByKey( (array)$filter_user_id, $src_user_options );

			$prepend_array_option = array( 0 => '--', -1 => _('-- Default --') );

			$splf = new SchedulePolicyListFactory(); 
			$schedule_policy_options = $splf->getByCompanyIdArray( $current_company->getId() );

			$aplf = new AbsencePolicyListFactory();
			$absence_policy_options = $aplf->getByCompanyIdArray( $current_company->getId() );

			$blf = new BranchListFactory(); 
			$blf->getByCompanyId( $current_company->getId() );
			$branch_options = Misc::prependArray( $prepend_array_option,  $blf->getArrayByListFactory( $blf, FALSE, TRUE ) );

			$dlf = new DepartmentListFactory(); 
			$dlf->getByCompanyId( $current_company->getId() );
			$department_options = Misc::prependArray( $prepend_array_option,  $dlf->getArrayByListFactory( $dlf, FALSE, TRUE ) );

			/*
			if ( $current_company->getProductEdition() == 20 ) {
				$jlf = new JobListFactory();
				$jlf->getByStatusIdAndCompanyId( array(10,20,30,40), $current_company->getId() );
				//$jlf->getByCompanyIdAndUserIdAndStatus( $current_company->getId(),  $data['user_id'], array(10,20,30,40) );
				$data['job_options'] = $jlf->getArrayByListFactory( $jlf, TRUE, TRUE );
				$data['job_manual_id_options'] = $jlf->getManualIDArrayByListFactory($jlf, TRUE);

				$jilf = new JobItemListFactory();
				$jilf->getByCompanyId( $current_company->getId() );
				$data['job_item_options'] = $jilf->getArrayByListFactory( $jilf, TRUE );
				$data['job_item_manual_id_options'] = $jilf->getManualIdArrayByListFactory( $jilf, TRUE );
			}
			*/

			//Select box options;
			$viewData['user_options'] = $user_options;
			$viewData['filter_user_options'] = $filter_user_options;

			$data['status_options'] = $sf->getOptions('status');
			$data['schedule_policy_options'] = $schedule_policy_options;
			$data['absence_policy_options'] = $absence_policy_options;

			//$data['type_options'] = $pf->getOptions('type');
			$data['branch_options'] = $branch_options;
			$data['department_options'] = $department_options;

			$viewData['data'] = $data;

			break;
		}

		$viewData['sf'] = $sf;

		return view('schedule/AddMassSchedule', $viewData);

	}

}




?>