<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use App\Models\Core\Debug;
use App\Models\Core\Environment;
use App\Models\Core\Redirect;
use App\Models\Core\URLBuilder;
use App\Models\Hierarchy\HierarchyListFactory;
use App\Models\Users\UserEducationListFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class UserEducation extends Controller
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

        // if ( !$permission->Check('accrual','enabled')
        //         OR !( $permission->Check('accrual','view') OR $permission->Check('accrual','view_own') OR $permission->Check('accrual','view_child') ) ) {
        //     $permission->Redirect( FALSE ); //Redirect
        // }

	}


    public function index(Request $request, $user_id = null)
    {
        $current_company = $this->currentCompany;
        $current_user = $this->currentUser;
        $current_user_prefs = $this->userPrefs;
        $permission = $this->permission;

        $viewData['title'] = 'Employee Qualification';

        $uelf = new UserEducationListFactory();
		$ulf = new UserListFactory();

		$filter_user_id = $request->input('filter_user_id', $user_id);

        if ( isset($filter_user_id) ) {
            $user_id = $filter_user_id;
        } else {
            $user_id = $current_user->getId();
            $filter_user_id = $current_user->getId();
        }

		$filter_data = NULL;

		//Get user object
		$ulf->getByIdAndCompanyID( $user_id, $current_company->getId() );

		if (  $ulf->getRecordCount() > 0 ) {
			$user_obj = $ulf->getCurrent();

			$uelf->getByUserIdAndCompanyId( $user_id, $current_company->getId(), $current_user_prefs->getItemsPerPage() );
            $qualifications = [];

			foreach ($uelf->rs as $uef_obj) {
                $uelf->data = (array)$uef_obj;
                $uef_obj = $uelf;

				$qualifications[] = array(
									'id' => $uef_obj->getId(),
									'user_id' => $uef_obj->getUser(),
                                    'qualification' => $uef_obj->getQualificationName(),
                                    'institute' => $uef_obj->getInstitute(),
                                    'year' => $uef_obj->getYear(),
                                    'remaks' => $uef_obj->getRemarks(),
								);
			}

			$hlf = new HierarchyListFactory();

			$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );
			Debug::Arr($permission_children_ids,'Permission Children Ids:', __FILE__, __LINE__, __METHOD__,10);
			if ( $permission->Check('accrual','view') == FALSE ) {
				if ( $permission->Check('user','view_child') ) {
					$filter_data['permission_children_ids'] = $permission_children_ids;
				}
				if ( $permission->Check('user','view_own') ) {
					$filter_data['permission_children_ids'][] = $current_user->getId();
				}
			}

            $user_options = [];

			$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
			$user_options = $ulf->getArrayByListFactory( $ulf, FALSE, TRUE );

            $is_owner = $permission->isOwner($user_obj->getCreatedBy(), $user_obj->getId());
            $is_child = $permission->isChild($user_obj->getId(), $permission_children_ids);

            $viewData['is_owner'] = $is_owner;
            $viewData['is_child'] = $is_child;
            $viewData['qualifications'] = $qualifications;
            $viewData['user_options'] = $user_options;
            $viewData['filter_user_id'] = $filter_user_id;
            // dd($viewData);

            return view('users.UserEducation', $viewData);

		}

    }

    public function add()
    {
        Redirect::Page( URLBuilder::getURL( NULL, 'EditUserLifePromotion.php') );
    }



}
