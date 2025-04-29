<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use App\Models\Core\Environment;
use App\Models\Core\Option;
use App\Models\Core\URLBuilder;
use App\Models\Hierarchy\HierarchyListFactory;
use App\Models\Users\UserDeductionListFactory;
use App\Models\Users\UserGenericDataListFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class UserDeductionListNew extends Controller
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

        // if ( !$permission->Check('user_tax_deduction','enabled')
        //         OR !( $permission->Check('user_tax_deduction','view') OR $permission->Check('user_tax_deduction','view_own') ) ) {

        //     $permission->Redirect( FALSE ); //Redirect

        // }

	}


    public function index(Request $request, $user_id = null)
    {
        $current_company = $this->currentCompany;
        $current_user = $this->currentUser;
        $permission = $this->permission;

        $ulf = new UserListFactory();
        $viewData['title'] = 'Employee Tax / Deduction List';

        $filter_user_id = $request->input('user_id', $user_id);

        if ( isset($filter_user_id) ) {
            $user_id = $filter_user_id;
        } else {
            $user_id = $current_user->getId();
            $filter_user_id = $current_user->getId();
        }

        //Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
		$hlf = new HierarchyListFactory();
		$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );

		$udlf = new UserDeductionListFactory();
		$udlf->getByCompanyIdAndUserId( $current_company->getId(), $user_id );

        $rows = [];

		$ulf->getByIdAndCompanyId( $user_id, $current_company->getId() );
		if ( $ulf->getRecordCount() > 0 ) {
			$user_obj = $ulf->getCurrent();

			if ( is_object($user_obj) ) {
				$is_owner = $permission->isOwner( $user_obj->getCreatedBy(), $user_obj->getID() );
				$is_child = $permission->isChild( $user_obj->getId(), $permission_children_ids );

				if ( $permission->Check('user_tax_deduction','view')
						OR ( $permission->Check('user_tax_deduction','view_own') AND $is_owner === TRUE )
						OR ( $permission->Check('user_tax_deduction','view_child') AND $is_child === TRUE ) ) {

					foreach ($udlf->rs as $ud_obj) {
                        $udlf->data = (array)$ud_obj;
                        $ud_obj = $udlf;

						$cd_obj = $ud_obj->getCompanyDeductionObject();

						$rows[] = array(
											'id' => $ud_obj->getId(),
											'status_id' => $cd_obj->getStatus(),
											'user_id' => $ud_obj->getUser(),
											'name' => $cd_obj->getName(),
											'type_id' => $cd_obj->getType(),
											'type' => Option::getByKey( $cd_obj->getType(), $cd_obj->getOptions('type') ),
											'calculation' => Option::getByKey( $cd_obj->getCalculation(), $cd_obj->getOptions('calculation') ),
											'is_owner' => $is_owner,
											'is_child' => $is_child,
											'deleted' => $ud_obj->getDeleted()
										);
					}
				}
			}
		}


		$filter_data = NULL;
		// extract( UserGenericDataListFactory::getSearchFormData( $saved_search_id, NULL ) );

		if ( $permission->Check('user_tax_deduction','view') == FALSE ) {
			if ( $permission->Check('user_tax_deduction','view_child') ) {
				$filter_data['permission_children_ids'] = $permission_children_ids;
			}
			if ( $permission->Check('user_tax_deduction','view_own') ) {
				$filter_data['permission_children_ids'][] = $current_user->getId();
			}
		}
		$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
		$user_options = UserListFactory::getArrayByListFactory( $ulf, FALSE, TRUE );


        $viewData['user_options'] = $user_options;
        $viewData['rows'] = $rows;
        $viewData['user_id'] = $user_id;
        // dd($viewData);

        return view('users.UserDeductionList', $viewData);

    }



    public function add($user_id = null)
    {
        if (!$user_id) {
            return redirect()->back()->with('error', 'User ID is required to add Tax / Deduction.');
        }

        // Optionally, validate user exists here before proceeding
        return redirect()->to(URLBuilder::getURL(array('user_id' => $user_id) , '/user/tax/edit', false));
    }



    public function delete($id)
	{
		$current_company = $this->currentCompany;

		if (empty($id)) {
			return response()->json(['error' => 'No Tax / Deduction List selected.'], 400);
		}

		$udlf = new UserDeductionListFactory();

			$taxDeduction_list = $udlf->getByCompanyIdAndId($current_company->getId(), $id, $current_company->getId() );

			foreach ($taxDeduction_list->rs as $ud_obj) {
				$taxDeduction_list->data = (array)$ud_obj; // added bcz currency data is null and it gives an error

				$taxDeduction_list->setDeleted(true); // Set deleted flag to true

				if ($taxDeduction_list->isValid()) {
					$res = $taxDeduction_list->Save();

					if($res){
						return response()->json(['success' => 'Tax / Deduction deleted successfully.']);
					}else{
						return response()->json(['error' => 'Tax / Deduction deleted failed.']);
					}
				}
			}

		return response()->json(['success' => 'Operation completed successfully.']);
	}


}
