<?php

namespace App\Http\Controllers\punch;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Users\UserTitleList;
use App\Models\Company\BranchListFactory;
use App\Models\Core\Environment;
use App\Models\Core\FastTree;
use App\Models\Core\Option;
use App\Models\Core\Redirect;
use App\Models\Core\TTDate;
use App\Models\Core\URLBuilder;
use App\Models\Department\DepartmentListFactory;
use App\Models\Hierarchy\HierarchyListFactory;
use App\Models\PayPeriod\PayPeriodListFactory;
use App\Models\Punch\PunchListFactory;
use App\Models\Users\UserGenericDataFactory;
use App\Models\Users\UserGenericDataListFactory;
use App\Models\Users\UserGroupListFactory;
use App\Models\Users\UserListFactory;
use App\Models\Users\UserTitleListFactory;
use Illuminate\Support\Facades\View;

class PunchList extends Controller
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
		/*
		if ( !$permission->Check('punch','enabled')
				OR !( $permission->Check('punch','edit') OR $permission->Check('punch','edit_child')) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
		*/
		$viewData['title'] = 'Punch List';
		$current_company = $this->currentCompany;
		$current_user = $this->currentUser;
		$permission = $this->permission;

		$ugdlf = new UserGenericDataListFactory(); 
		$ugdf = new UserGenericDataFactory();

		$ulf = new UserListFactory();
		$plf = new PunchListFactory();

		$hlf = new HierarchyListFactory();
		$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );
		


		$pplf = new PayPeriodListFactory();
		$pplf->getByCompanyId( $current_company->getId() );
		$pay_period_options = $pplf->getArrayByListFactory( $pplf, FALSE, FALSE );
		$pay_period_ids = array_keys((array)$pay_period_options);

		$filter_data = [];
		$plf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );


		$punch_status_options = $plf->getOptions('status');
		$punch_type_options = $plf->getOptions('type');

		$utlf = new UserTitleListFactory(); 
		$utlf->getByCompanyId( $current_company->getId() );
		$title_options = $utlf->getArrayByListFactory( $utlf, FALSE, TRUE );

		$blf = new BranchListFactory();
		$blf->getByCompanyId( $current_company->getId() );
		$branch_options = $blf->getArrayByListFactory( $blf, FALSE, TRUE );

		$dlf = new DepartmentListFactory(); 
		$dlf->getByCompanyId( $current_company->getId() );
		$department_options = $dlf->getArrayByListFactory( $dlf, FALSE, TRUE );

		$uglf = new UserGroupListFactory(); 
		$group_options = $uglf->getArrayByNodes( FastTree::FormatArray( $uglf->getByCompanyIdArray( $current_company->getId() ), 'TEXT', TRUE) );

		$ulf = new UserListFactory();
		$user_options = $ulf->getByCompanyIdArray( $current_company->getID(), FALSE );

		foreach ($plf->rs as $p_obj) {
			$plf->data = (array)$p_obj;
			$p_obj = $plf;
			
			$user_obj = $ulf->getById( $p_obj->getColumn('user_id') )->getCurrent();

			$rows[] = array(
						'id' => $p_obj->getColumn('punch_id'),
						'punch_control_id' => $p_obj->getPunchControlId(),

						'user_id' => $p_obj->getColumn('user_id'),
						'first_name' => $user_obj->getFirstName(),
						'last_name' => $user_obj->getLastName(),
						'title' => Option::getByKey($user_obj->getTitle(), $title_options ),
						'group' => Option::getByKey($user_obj->getGroup(), $group_options ),
						'default_branch' => Option::getByKey($user_obj->getDefaultBranch(), $branch_options ),
						'default_department' => Option::getByKey($user_obj->getDefaultDepartment(), $department_options ),

						'branch_id' => $p_obj->getColumn('branch_id'),
						'branch' => Option::getByKey( $p_obj->getColumn('branch_id'), $branch_options ),
						'department_id' => $p_obj->getColumn('department_id'),
						'department' => Option::getByKey( $p_obj->getColumn('department_id'), $department_options ),
						//'status_id' => $p_obj->getStatus(),
						'status_id' => Option::getByKey($p_obj->getStatus(), $punch_status_options),
						//'type_id' => $p_obj->getType(),
						'type_id' => Option::getByKey($p_obj->getType(), $punch_type_options),
						'date_stamp' => TTDate::getDate('DATE', TTDate::strtotime($p_obj->getColumn('date_stamp')) ),

						'job_id' => $p_obj->getColumn('job_id'),
						'job_name' => $p_obj->getColumn('job_name'),
						'job_group_id' => $p_obj->getColumn('job_group_id'),
						'job_item_id' => $p_obj->getColumn('job_item_id'),

						'time_stamp' => TTDate::getDate('DATE+TIME', $p_obj->getTimeStamp() ),

						'is_owner' => $permission->isOwner( $p_obj->getCreatedBy(), $current_user->getId() ),
						'is_child' => $permission->isChild( $p_obj->getColumn('user_id'), $permission_children_ids ),
					);

		}

		$viewData['rows'] = $rows;

		return view('punch/PunchList', $viewData);
	}

	public function delete($punch_id){
		//Debug::setVerbosity(11);
		if (empty($punch_id)) {
            return response()->json(['error' => 'No Punch Selected.'], 400);
        }

		$plf = new PunchListFactory();
		$plf->getById( $punch_id );
		if ( $plf->getRecordCount() > 0 ) {
			foreach($plf->rs as $p_obj) {
				$plf->data = (array)$p_obj;
				$p_obj = $plf;

				$p_obj->setUser( $p_obj->getPunchControlObject()->getUserDateObject()->getUser() );
				$p_obj->setDeleted(TRUE);

				//These aren't doing anything because they aren't acting on the PunchControl object?
				$p_obj->setEnableCalcTotalTime( TRUE );
				$p_obj->setEnableCalcSystemTotalTime( TRUE );
				$p_obj->setEnableCalcWeeklySystemTotalTime( TRUE );
				$p_obj->setEnableCalcUserDateTotal( TRUE );
				$p_obj->setEnableCalcException( TRUE );
				$res = $p_obj->Save();

				if($res){
					return response()->json(['success' => 'Punch Deleted Successfully.']);
				}else{
					return response()->json(['error' => 'Punch Deleted Failed.']);
				}
			}
		}

		//return redirect(route('attendance.punchlist'));

	}
}


?>