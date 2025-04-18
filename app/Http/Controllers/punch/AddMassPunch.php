<?php

namespace App\Http\Controllers\punch;

use App\Http\Controllers\Controller;
use App\Models\Company\BranchListFactory;
use Illuminate\Http\Request;

use App\Models\Core\Debug;
use App\Models\Core\Environment;
use App\Models\Core\Factory;
use App\Models\Core\FormVariables;
use App\Models\Core\Misc;
use App\Models\Core\Option;
use App\Models\Core\OtherFieldListFactory;
use App\Models\Core\Redirect;
use App\Models\Core\StationListFactory;
use App\Models\Core\TTDate;
use App\Models\Core\URLBuilder;
use App\Models\Department\DepartmentListFactory;
use App\Models\Hierarchy\HierarchyListFactory;
use App\Models\Punch\PunchControlFactory;
use App\Models\Punch\PunchControlListFactory;
use App\Models\Punch\PunchFactory;
use App\Models\Punch\PunchListFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;


class AddMassPunch extends Controller
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
		$current_company = $this->currentCompany;
		$current_user = $this->currentUser;
		$current_user_prefs = $this->userPrefs;

		if ( !$permission->Check('punch','enabled')
				OR !( $permission->Check('punch','edit')
						OR $permission->Check('punch','edit_own')
						OR $permission->Check('punch','edit_child')
						) ) {
			$permission->Redirect( FALSE ); //Redirect
		}

		$viewData['title'] = 'Mass Punch';

		extract	(FormVariables::GetVariables(
			array (
				'action',
				'id',
				'pc_data',
				'filter_user_id'												
			) 
		));

		$punch_full_time_stamp = NULL;
		if ( isset($pc_data) ) {
			if ( $pc_data['start_date_stamp'] != ''
					AND !is_numeric($pc_data['start_date_stamp'])
					AND $pc_data['end_date_stamp'] != ''
					AND !is_numeric($pc_data['end_date_stamp'])
					AND $pc_data['time_stamp'] != ''
					AND !is_numeric($pc_data['time_stamp'])
					) {
				$pc_data['start_punch_full_time_stamp'] = TTDate::parseDateTime($pc_data['start_date_stamp'].' '.$pc_data['time_stamp']);
				$pc_data['end_punch_full_time_stamp'] = TTDate::parseDateTime($pc_data['end_date_stamp'].' '.$pc_data['time_stamp']);
				$pc_data['time_stamp'] = TTDate::parseDateTime($pc_data['start_date_stamp'].' '.$pc_data['time_stamp']);
			} else {
				$pc_data['start_punch_full_time_stamp'] = NULL;
				$pc_data['end_punch_full_time_stamp'] = NULL;
			}

			if ( $pc_data['start_date_stamp'] != '') {
				$pc_data['start_date_stamp'] = TTDate::parseDateTime($pc_data['start_date_stamp']);
			}
			if ( $pc_data['end_date_stamp'] != '') {
				$pc_data['end_date_stamp'] = TTDate::parseDateTime($pc_data['end_date_stamp']);
			}
			
		}

		//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
		$hlf = new HierarchyListFactory();
		$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );
		$filter_data = array();
		//Debug::Arr($permission_children_ids,'Permission Children Ids:', __FILE__, __LINE__, __METHOD__,10);
		if ( $permission->Check('punch','edit') == FALSE ) {
			if ( $permission->Check('punch','edit_child') ) {
				$filter_data['permission_children_ids'] = $permission_children_ids;
			}
			if ( $permission->Check('punch','edit_own') ) {
				$filter_data['permission_children_ids'][] = $current_user->getId();
			}
		}

		$pcf = new PunchControlFactory();
		$pf = new PunchFactory();
		$ulf = new UserListFactory();


		if ( !is_array($pc_data) ) {
			Debug::Text(' ID was NOT passed: '. $id, __FILE__, __LINE__, __METHOD__,10);
			$time_stamp = $date_stamp = TTDate::getBeginDayEpoch( TTDate::getTime() ) + (3600*12); //Noon
			
			$pc_data = array(
				//'user_id' => $user_obj->getId(),
				//'user_full_name' => $user_obj->getFullName(),
				'start_date_stamp' => date('Y-m-d', $date_stamp),
				'end_date_stamp' => date('Y-m-d', $date_stamp),
				'time_stamp' => date('H:i', $time_stamp),
				'status_id' => 10,
				//'branch_id' => $user_obj->getDefaultBranch(),
				//'department_id' => $user_obj->getDefaultDepartment(),
				'quantity' => 0,
				'bad_quantity' => 0,
				'dow' => array(1 => TRUE, 2 => TRUE, 3 => TRUE, 4 => TRUE, 5 => TRUE)
			);


			unset($time_stamp, $date_stamp);
		}
		//var_dump($pc_data);

		$ulf = new UserListFactory();
		$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
		$src_user_options = UserListFactory::getArrayByListFactory( $ulf, FALSE, FALSE );

		$user_options = Misc::arrayDiffByKey( (array)$filter_user_id, $src_user_options );
		$filter_user_options = Misc::arrayIntersectByKey( (array)$filter_user_id, $src_user_options );
		
		$prepend_array_option = array( 0 => '--', -1 => _('-- Default --') );

		$blf = new BranchListFactory();
		$blf->getByCompanyId( $current_company->getId() );
		$branch_options = Misc::prependArray( $prepend_array_option,  $blf->getArrayByListFactory( $blf, FALSE, TRUE ) );

		$dlf = new DepartmentListFactory();
		$dlf->getByCompanyId( $current_company->getId() );
		$department_options = Misc::prependArray( $prepend_array_option,  $dlf->getArrayByListFactory( $dlf, FALSE, TRUE ) );

		//Select box options;
		$viewData['user_options'] = $user_options;
		$viewData['filter_user_options'] = $filter_user_options;
		
		$pc_data['status_options'] = $pf->getOptions('status');
		$pc_data['type_options'] = $pf->getOptions('type');
		$pc_data['branch_options'] = $branch_options;
		$pc_data['department_options'] = $department_options;

		//Get other field names
		$oflf = new OtherFieldListFactory();
		$pc_data['other_field_names'] = $oflf->getByCompanyIdAndTypeIdArray( $current_company->getId(), 15 );

		$viewData['current_user_prefs'] = $current_user_prefs;

		$viewData['pc_data'] = $pc_data;

		//dd($viewData);
		$viewData['pcf'] = $pcf;
		$viewData['pf'] = $pf;
		
		return view('punch/AddMassPunch', $viewData);
	}

	public function submit(Request $request){
		
		$pc_data = $request->pc_data;

		
		$pcf = new PunchControlFactory();

		$fail_transaction = FALSE;

		$pc_data['time_stamp'] = Factory::convertToSeconds($pc_data['time_stamp']);
		$pc_data['start_date_stamp'] = TTDate::parseDateTime($pc_data['start_date_stamp']);
		$pc_data['end_date_stamp'] = TTDate::parseDateTime($pc_data['end_date_stamp']);

		if ( TTDate::getDayDifference( $pc_data['start_date_stamp'], $pc_data['end_date_stamp'] > 31 )) {
			$pc_data['end_date_stamp'] = $pc_data['start_date_stamp'] + (86400*31);
		}
		//dd($pc_data);
		if ( isset($filter_user_id) AND is_array($filter_user_id) AND count($filter_user_id) > 0 ) {
			Redirect::Page( URLBuilder::getURL( array('action' => 'add_mass_punch', 'filter_user_id' => $filter_user_id, 'data' => $pc_data ), '../progress_bar/ProgressBarControl') );
		} else {
			$pcf->Validator->isTrue('user_id',FALSE, 'Please select at least one employee');
		}
	}

}


?>