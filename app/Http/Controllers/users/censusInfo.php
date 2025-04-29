<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use App\Models\Core\Debug;
use App\Models\Core\Environment;
use App\Models\Core\Redirect;
use App\Models\Core\URLBuilder;
use App\Models\Hierarchy\HierarchyListFactory;
use App\Models\Users\UserCensusInformationFactory;
use App\Models\Users\UserCensusInformationListFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class CensusInfo extends Controller
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

        $viewData['title'] = 'Employee Census';

        $ucilf = new UserCensusInformationListFactory();
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

			$ucilf->getByUserIdAndCompanyId( $user_id, $current_company->getId(), $current_user_prefs->getItemsPerPage() );
            $censuses = [];

            $ucif = new UserCensusInformationFactory();
            $gender_options = $ucif->getOptions('gender');

			foreach ($ucilf->rs as $ucif_obj) {
                $ucilf->data = (array)$ucif_obj;
                $ucif_obj = $ucilf;

				$censuses[] = array(
									'id' => $ucif_obj->getId(),
									'user_id' => $ucif_obj->getUser(),
                                    'dependant' => $ucif_obj->getDependant(),
                                    'name' => $ucif_obj->getName(),
                                    'relationship' => $ucif_obj->getRelationship(),
                                    'dob' => $ucif_obj->getBirthDate(),
                                    'nic' => $ucif_obj->getNic(),
                                    // 'gender' => $ucif_obj->getGender(),
                                    'gender' => $gender_options[$ucif_obj->getGender()] ?? 'Unknown',

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
            $viewData['censuses'] = $censuses;
            $viewData['user_options'] = $user_options;
            $viewData['filter_user_id'] = $filter_user_id;
            // dd($viewData);

            return view('users.CensusInfo', $viewData);
		}

    }


    public function add()
    {
        Redirect::Page( URLBuilder::getURL( NULL, 'editCensus.php') );
    }


}
