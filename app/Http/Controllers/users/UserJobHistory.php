<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use App\Models\Company\BranchListFactory;
use App\Models\Core\Debug;
use App\Models\Core\Environment;
use App\Models\Core\Option;
use App\Models\Core\TTDate;
use App\Models\Core\URLBuilder;
use App\Models\Department\DepartmentListFactory;
use App\Models\Hierarchy\HierarchyListFactory;
use App\Models\Users\UserGenericDataFactory;
use App\Models\Users\UserJobListFactory;
use App\Models\Users\UserListFactory;
use App\Models\Users\UserTitleListFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class UserJobHistory extends Controller
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

        // if ( !$permission->Check('wage','enabled')
        //         OR !( $permission->Check('wage','view') OR $permission->Check('wage','view_child') OR $permission->Check('wage','view_own') ) ) {
        //     $permission->Redirect( FALSE ); //Redirect
        // }

	}


    public function index(Request $request, $user_id = null)
    {
        $current_company = $this->currentCompany;
        $current_user = $this->currentUser;
        $current_user_prefs = $this->userPrefs;
        $permission = $this->permission;

        $viewData['title'] = 'Employee Job History';
        $ulf = new UserListFactory();

        $filter_user_id = $request->input('user_id', $user_id);

        if ( isset($filter_user_id) ) {
            $user_id = $filter_user_id;
        } else {
            $user_id = $current_user->getId();
            $filter_user_id = $current_user->getId();
        }

        //Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
		$user_has_default_wage = FALSE;

		$hlf = new HierarchyListFactory();
		$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );
		Debug::Arr($permission_children_ids,'Permission Children Ids:', __FILE__, __LINE__, __METHOD__,10);

        $ujlf = new UserJobListFactory();
		$ujlf->GetByUserIdAndCompanyId($user_id, $current_company->getId(), $current_user_prefs->getItemsPerPage() );

        //ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
		//Select box options;
		$blf = new BranchListFactory();
		$branch_options = $blf->getByCompanyIdArray( $current_company->getId() );

        //ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
        //Select box options;
		$dlf = new DepartmentListFactory();
		$department_options = $dlf->getByCompanyIdArray( $current_company->getId() );

        //ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
        //Select box options;
		$utlf = new UserTitleListFactory();
		$title_options = $utlf->getByCompanyIdArray( $current_company->getId() );

        $job_history = [];

		$user_obj = $ulf->getByIdAndCompanyId( $user_id, $current_company->getId() )->getCurrent();
		if ( is_object($user_obj) ) {
			$is_owner = $permission->isOwner( $user_obj->getCreatedBy(), $user_obj->getID() );
			$is_child = $permission->isChild( $user_obj->getId(), $permission_children_ids );


			if ( $permission->Check('wage','view')
					OR ( $permission->Check('wage','view_own') AND $is_owner === TRUE )
					OR ( $permission->Check('wage','view_child') AND $is_child === TRUE ) ) {

				foreach ($ujlf->rs as $job) {
                    $ujlf->data = (array)$job;
                    $job = $ujlf;

					$job_history[] = array(
										'id' => $job->getId(),
										'user_id' => $job->getUser(),
                       /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'default_branch_id' => Option::getByKey($job->getDefaultBranch(), $branch_options ), $job->getDefaultBranch(),
                       /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'default_department_id' => Option::getByKey($job->getDefaultDepartment(), $department_options ),$job->getDefaultDepartment(),
                       /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'title_id' => Option::getByKey($job->getTitle(), $title_options ), $job->getTitle(),
                       /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'first_worked_date' => TTDate::getDate( 'DATE', $job->getFirstWorkedDate() ),
                       /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'last_worked_date' => TTDate::getDate( 'DATE', $job->getLastWorkedDate() ),
                       ///* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'note' => $job->getNote(),
										'is_owner' => $is_owner,
										'is_child' => $is_child,
										'deleted' => $job->getDeleted()
									);

				}
                // print_r($wages);

			}
		}


		$filter_data = NULL;
		// extract( UserGenericDataFactory::getSearchFormData( $saved_search_id, NULL ) );

		if ( $permission->Check('wage','view') == FALSE ) {
			if ( $permission->Check('wage','view_child') ) {
				$filter_data['permission_children_ids'] = $permission_children_ids;
			}
			if ( $permission->Check('wage','view_own') ) {
				$filter_data['permission_children_ids'][] = $current_user->getId();
			}
		}

        $user_options = [];

		$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );

		$user_options = UserListFactory::getArrayByListFactory( $ulf, FALSE, TRUE );

        $viewData['job_history'] = $job_history;
        $viewData['user_options'] = $user_options;
        $viewData['user_has_default_wage'] = $user_has_default_wage;
        $viewData['user_id'] = $user_id;
        // dd($viewData);

        return view('users.UserJobHistoryList', $viewData);

    }



    public function add($user_id = null)
    {
        if (!$user_id) {
            return redirect()->back()->with('error', 'User ID is required to add jobhistory.');
        }

        // Optionally, validate user exists here before proceeding
        return redirect()->to(URLBuilder::getURL(array('user_id' => $user_id) , '/user/jobhistory/edit', false));
    }



    public function delete($id)
	{
		$current_company = $this->currentCompany;

		if (empty($id)) {
			return response()->json(['error' => 'No jobhistory List selected.'], 400);
		}

		$ujlf = new UserJobListFactory();

			$job_list = $ujlf->getByIdAndCompanyId($id, $current_company->getId() );

			foreach ($job_list->rs as $job) {
				$job_list->data = (array)$job; // added bcz currency data is null and it gives an error

				$job_list->setDeleted(true); // Set deleted flag to true

				if ($job_list->isValid()) {
					$res = $job_list->Save();

					if($res){
						return response()->json(['success' => 'Job List deleted successfully.']);
					}else{
						return response()->json(['error' => 'Job List deleted failed.']);
					}
				}
			}

		return response()->json(['success' => 'Operation completed successfully.']);
	}


}
