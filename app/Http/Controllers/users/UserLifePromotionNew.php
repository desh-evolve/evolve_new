<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use App\Models\Core\Debug;
use App\Models\Core\Environment;
use App\Models\Hierarchy\HierarchyListFactory;
use App\Models\Users\UserLifePromotionListFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class UserLifePromotionNew extends Controller
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


    public function index(Request $request, $user_id = null)
    {
        // if ( !$permission->Check('user','enabled')
        //     OR !( $permission->Check('accrual','view') OR $permission->Check('accrual','view_own') OR $permission->Check('accrual','view_child') ) ) {
        //     $permission->Redirect( FALSE ); //Redirect
        // }

        $current_company = $this->currentCompany;
        $current_user = $this->currentUser;
        $current_user_prefs = $this->userPrefs;
        $permission = $this->permission;

        $viewData['title'] = 'Employee Promotions';

        $ulplf = new UserLifePromotionListFactory();
		$ulf = new UserListFactory();

		// if ( $permission->Check('user','view') OR $permission->Check('user','view_child') ) {
		// 	if ( isset($filter_user_id) ) {
		// 		$user_id = $filter_user_id;
		// 	} else {
		// 		$user_id = $current_user->getId();
		// 		$filter_user_id = $current_user->getId();
		// 	}
		// } else {
		// 	$filter_user_id = $user_id = $current_user->getId();
		// }

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

			$ulplf->getByUserIdAndCompanyId( $user_id, $current_company->getId(), $current_user_prefs->getItemsPerPage() );
            $lifepromotions = [];

			foreach ($ulplf->rs as $ulpf_obj) {
                $ulplf->data = (array)$ulpf_obj;
                $ulpf_obj = $ulplf;

				$lifepromotions[] = array(
									'id' => $ulpf_obj->getId(),
									'user_id' => $ulpf_obj->getUser(),
                                    'current_designation' => $ulpf_obj->getCurrentDesignation(),
                                    'new_designation' => $ulpf_obj->getNewDesignation(),
                                    'current_salary' => $ulpf_obj->getCurrentSalary(),
                                    'new_salary' => $ulpf_obj->getNewSalary(),
                                    'effective_date' => $ulpf_obj->getEffectiveDate(),
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

            $user_options = []; // Initialize the variable to prevent errors

			$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
			$user_options = $ulf->getArrayByListFactory( $ulf, FALSE, TRUE );

            $is_owner = $permission->isOwner($user_obj->getCreatedBy(), $user_obj->getId());
            $is_child = $permission->isChild($user_obj->getId(), $permission_children_ids);

            $viewData['is_owner'] = $is_owner;
            $viewData['is_child'] = $is_child;
            $viewData['lifepromotions'] = $lifepromotions;
            $viewData['user_options'] = $user_options;
            $viewData['filter_user_id'] = $filter_user_id;
            // dd($viewData);

            return view('users.UserLifePromotion', $viewData);

		}
    }


    public function add()
    {
        return redirect()->route('user.promotions.edit'); // You can define this route
        // Redirect::Page( URLBuilder::getURL( NULL, 'EditUserLifePromotion.php') );
    }

}
