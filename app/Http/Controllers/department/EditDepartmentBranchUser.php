<?php

namespace App\Http\Controllers\department;

use App\Http\Controllers\Controller;
use App\Models\Company\BranchListFactory;
use Illuminate\Http\Request;

use App\Models\Core\Environment;
use App\Models\Core\Debug;
use App\Models\Core\FormVariables;
use App\Models\Core\Redirect;
use App\Models\Core\URLBuilder;
use App\Models\Department\DepartmentBranchListFactory;
use App\Models\Department\DepartmentBranchUserFactory;
use App\Models\Department\DepartmentBranchUserListFactory;
use App\Models\Department\DepartmentListFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;

class EditDepartmentBranchUser extends Controller
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
        if ( !$permission->Check('department','enabled')
				OR !( $permission->Check('department','assign') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
        */
		
        $viewData['title'] = 'Department Employees';

		$dlf = new DepartmentListFactory();

		$dlf->GetByIdAndCompanyId($id, $current_company->getId() );

		foreach ($dlf->rs as $department) {
			$dlf->data = (array)$department;
			$department = $dlf;
			//Debug::Arr($department,'Department', __FILE__, __LINE__, __METHOD__,10);

			$branch_data = array();

			$dblf = new DepartmentBranchListFactory(); 
			$dblf->getByDepartmentId( $department->getId() );
			foreach($dblf->rs as $department_branch) {
				$dblf->data = (array)$department_branch;
				$department_branch = $dblf;

				$branch_id = $department_branch->getBranch();
				Debug::Text('DepartmentBranchId: '. $branch_id , __FILE__, __LINE__, __METHOD__,10);

				if ( isset($id) ) {
					//Get User ID's from database.
					$dbulf = new DepartmentBranchUserListFactory();
					$dbulf->getByDepartmentBranchId( $department_branch->getId() );

					$department_branch_user_ids = array();
					foreach($dbulf->rs as $department_branch_user) {
						$dbulf->data = (array)$department_branch_user;
						$department_branch_user = $dbulf;

						$department_branch_user_ids[] = $department_branch_user->getUser();
						Debug::Text('DepartmentBranchUser: '. $department_branch_user->getUser(), __FILE__, __LINE__, __METHOD__,10);
					}
				} else {
					//Use selected User Id's.
					$department_branch_user_ids = $department_data['branch_data'][$branch_id];
				}

				$blf = new BranchListFactory();
				$blf->getById( $branch_id );
				$branch = $blf->getCurrent();
				$branch_data[$branch_id] = array (
					'id' => $branch->getId(),
					'name' => $branch->getName(),
					'user_ids' => $department_branch_user_ids
				);
			}

			$department_data = array(
								'id' => $department->getId(),
								'company_name' => $current_company->getName(),
								'status' => $department->getStatus(),
								'name' => $department->getName(),
								'branch_list' => $department->getBranch(),
								'branch_data' => $branch_data,
								'created_date' => $department->getCreatedDate(),
								'created_by' => $department->getCreatedBy(),
								'updated_date' => $department->getUpdatedDate(),
								'updated_by' => $department->getUpdatedBy(),
								'deleted_date' => $department->getDeletedDate(),
								'deleted_by' => $department->getDeletedBy()
							);
		}


		//Select box options;
		$department_data['branch_list_options'] = BranchListFactory::getByCompanyIdArray($current_company->getId());

		//$ulf = new UserListFactory;
		$department_data['user_options'] = UserListFactory::getByCompanyIdArray( $current_company->getId(), FALSE );
		//var_dump($te);

		$viewData['department_data'] = $department_data;
		$viewData['dbuf'] = $dbuf;

        return view('department/EditDepartmentBranchUser', $viewData);

    }

	public function submit(Request $request){
		
		$department_data = $request->data;

		$dbuf = new DepartmentBranchUserFactory();
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		Debug::Text('Department ID: '. $department_data['id'] , __FILE__, __LINE__, __METHOD__,10);


		$dbulf = new DepartmentBranchUserListFactory();

		//Delete all mappings first?
		$dblf = new DepartmentBranchListFactory();
		$dblf->getByDepartmentId( $department_data['id'] );

		foreach ($dblf->rs as $department_branch) {
			$dblf->data = (array)$department_branch;
			$department_branch = $dblf;

			$dbulf->getByDepartmentBranchId( $department_branch->getId() );

			foreach($dbulf->rs as $department_branch_user) {
				$dbulf->data = (array)$department_branch_user;
				$department_branch_user = $dbulf;

				Debug::Text('Deleting Department Branch Mapping: '. $department_branch_user->getId() , __FILE__, __LINE__, __METHOD__,10);
				$department_branch_user->Delete();
			}
		}

		$dbulf = new DepartmentBranchUserListFactory();

		if ( isset($department_data['branch_data']) AND is_array($department_data['branch_data']) ) {
			foreach($department_data['branch_data'] as $branch_id => $user_ids) {
				Debug::Text('BranchID: '. $branch_id , __FILE__, __LINE__, __METHOD__,10);
				Debug::Arr($user_ids, 'Branch User IDs: ', __FILE__, __LINE__, __METHOD__,10);

				//Get DepartmentBranchId
				$dblf->getByDepartmentIdAndBranchId($department_data['id'],$branch_id);
				$department_branch_id = $dblf->getCurrent()->getId();

				Debug::Text('DepartmentBranchID: '. $department_branch_id, __FILE__, __LINE__, __METHOD__,10);

				foreach ($user_ids as $user_id) {
					Debug::Text('Mapping User: '. $user_id .' To DepartmentBranchID: '. $department_branch_id, __FILE__, __LINE__, __METHOD__,10);
					$dbuf->setDepartmentBranch($department_branch_id);
					$dbuf->setUser($user_id);
					if ( $dbuf->isValid() ) {
						$dbuf->Save();
					}

				}
			}
		}

		if ( $dbuf->isValid() ) {

			Redirect::Page( URLBuilder::getURL(NULL, 'DepartmentList.php') );

		}
	}
}


?>