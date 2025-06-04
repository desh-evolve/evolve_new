<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use App\Models\Company\BranchListFactory;
use App\Models\Company\CompanyListFactory;
use Illuminate\Http\Request;

use App\Models\Core\Environment;
use App\Models\Core\FastTree;
use App\Models\Core\Option;
use App\Models\Core\Redirect;
use App\Models\Core\TTDate;
use App\Models\Core\URLBuilder;
use App\Models\Department\DepartmentListFactory;
use App\Models\Hierarchy\HierarchyListFactory;
use App\Models\Users\UserGroupListFactory;
use App\Models\Users\UserListFactory;
use App\Models\Users\UserTitleListFactory;
use Illuminate\Support\Facades\View;

class UserList extends Controller
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

		if ( !$permission->Check('user','enabled')
				OR !( $permission->Check('user','view') OR $permission->Check('user','view_child')  ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}

		*/

		$current_company = $this->currentCompany;
		$permission = $this->permission;
		$current_user = $this->currentUser;

		//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
		$hlf = new HierarchyListFactory();
		$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );

		$viewData['title'] = 'Employee List';

		if ( isset($company_id) AND $company_id != '' ) {
			$filter_data['company_id'] = $company_id;
		}

		$ulf = new UserListFactory();
		$clf = new CompanyListFactory();

		if ( $permission->Check('company','view') ) {
			$clf = new CompanyListFactory();
			$clf->getAll();
		}

		//Get title list,
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


		$ulf->getAll();

		foreach ($ulf->rs as $u_obj) {
			$ulf->data = (array)$u_obj;
			$u_obj = $ulf;

			$company_name = $clf->getById( $u_obj->getCompany() )->getCurrent()->getName();

			$users[] = 	array (
							'id' => $u_obj->getId(),
							'company_id' => $u_obj->getCompany(),
							'employee_number' => $u_obj->getEmployeeNumber(),
							'status_id' => $u_obj->getStatus(),
							'status' => Option::getByKey( $u_obj->getStatus(), $u_obj->getOptions('status') ),
							'user_name' => $u_obj->getUserName(),
							'phone_id' => $u_obj->getPhoneID(),
							'ibutton_id' => $u_obj->getIButtonID(),

							'full_name' => $u_obj->getFullName(TRUE),
							'first_name' => $u_obj->getFirstName(),
							'middle_name' => $u_obj->getMiddleName(),
							'last_name' => $u_obj->getLastName(),

							'nic' => $u_obj->getNic(),

							'title' => Option::getByKey($u_obj->getTitle(), $title_options ),
							'user_group' => Option::getByKey($u_obj->getGroup(), $group_options ),

							'default_branch' => Option::getByKey($u_obj->getDefaultBranch(), $branch_options ),
							'default_department' => Option::getByKey($u_obj->getDefaultDepartment(), $department_options ),

							'sex_id' => $u_obj->getSex(),
							'sex' => Option::getByKey($u_obj->getSex(), $u_obj->getOptions('sex') ),

							'address1' => $u_obj->getAddress1(),
							'address2' => $u_obj->getAddress2(),
							'city' => $u_obj->getCity(),
							'province' => $u_obj->getProvince(),
							'country' => $u_obj->getCountry(),
							'postal_code' => $u_obj->getPostalCode(),
							'work_phone' => $u_obj->getWorkPhone(),
							'home_phone' => $u_obj->getHomePhone(),
							'mobile_phone' => $u_obj->getMobilePhone(),
							'fax_phone' => $u_obj->getFaxPhone(),
							'home_email' => $u_obj->getHomeEmail(),
							'work_email' => $u_obj->getWorkEmail(),
							'birth_date' => TTDate::getDate('DATE', $u_obj->getBirthDate() ),
							'sin' => $u_obj->getSecureSIN(),
							'hire_date' => TTDate::getDate('DATE', $u_obj->getHireDate() ),
							'termination_date' => TTDate::getDate('DATE', $u_obj->getTerminationDate() ),

							'map_url' => $u_obj->getMapURL(),

							'is_owner' => $permission->isOwner( $u_obj->getCreatedBy(), $u_obj->getId() ),
							'is_child' => $permission->isChild( $u_obj->getId(), $permission_children_ids ),
							'deleted' => $u_obj->getDeleted(),
						);
		}

		$viewData['users'] = $users;
        // dd($viewData);

		return view('users/UserList', $viewData);
	}

	public function delete(){
		//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
		$hlf = new HierarchyListFactory();
		$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );

		$delete = TRUE;

		if ( DEMO_MODE == FALSE
			AND ( $permission->Check('user','delete') OR $permission->Check('user','delete_own') OR $permission->Check('user','delete_child')  ) ) {

			if ( is_array($ids) ) {
				$ulf = new UserListFactory();
				$ulf->StartTransaction();

				foreach ($ids as $id) {
					if ( $id != $current_user->getId() ) {
						$ulf->getByIdAndCompanyId($id, $current_company->getID() );
						foreach ($ulf as $user) {
							$is_owner = $permission->isOwner( $user->getCreatedBy(), $user->getID() );
							$is_child = $permission->isChild( $user->getId(), $permission_children_ids );

							if ( $permission->Check('user','delete')
									OR ( $permission->Check('user','delete_child') AND $is_child === TRUE )
									OR ( $permission->Check('user','delete_own') AND $is_owner === TRUE ) ) {
								$user->setDeleted($delete);
								$user->Save();
							}
						}
					}
				}

				$ulf->CommitTransaction();
			}
		}

		Redirect::Page( URLBuilder::getURL( array('saved_search_id' => $saved_search_id ), 'UserList.php') );

	}

	public function search(){}
}

?>
