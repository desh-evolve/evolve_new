<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use App\Models\Company\WageGroupListFactory;
use App\Models\Core\Debug;
use App\Models\Core\Environment;
use App\Models\Core\Misc;
use App\Models\Core\Option;
use App\Models\Core\Redirect;
use App\Models\Core\TTDate;
use App\Models\Core\URLBuilder;
use App\Models\Hierarchy\HierarchyListFactory;
use App\Models\Users\UserGenericDataFactory;
use App\Models\Users\UserListFactory;
use App\Models\Users\UserWageListFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class UserWageList extends Controller
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

        $viewData['title'] = 'Employee Wage List';
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

		$uwlf = new UserWageListFactory();
		$uwlf->GetByUserIdAndCompanyId($user_id, $current_company->getId(), $current_user_prefs->getItemsPerPage() );

		$wglf = new WageGroupListFactory();
		$wage_group_options = $wglf->getArrayByListFactory( $wglf->getByCompanyId( $current_company->getId() ), TRUE );

		$user_obj = $ulf->getByIdAndCompanyId( $user_id, $current_company->getId() )->getCurrent();
        $user_factory = $ulf->getByIdAndCompanyId( $user_id, $current_company->getId() );
        $user_obj = is_object($user_factory) ? $user_factory->getCurrent() : null;

        $wages = [];

		if ( is_object($user_obj) ) {
			$is_owner = $permission->isOwner( $user_obj->getCreatedBy(), $user_obj->getID() );
			$is_child = $permission->isChild( $user_obj->getId(), $permission_children_ids );

			$currency_symbol = NULL;
			if ( is_object($user_obj->getCurrencyObject()) ) {
				$currency_symbol = $user_obj->getCurrencyObject()->getSymbol();
			}

			if ( $permission->Check('wage','view')
					OR ( $permission->Check('wage','view_own') AND $is_owner === TRUE )
					OR ( $permission->Check('wage','view_child') AND $is_child === TRUE ) ) {

				foreach ($uwlf->rs as $wage) {
                    $uwlf->data = (array)$wage;
                    $wage = $uwlf;

					$wages[] = array(
										'id' => $wage->getId(),
										'user_id' => $wage->getUser(),
										'wage_group_id' => $wage->getWageGroup(),
										'wage_group' => Option::getByKey($wage->getWageGroup(), $wage_group_options ),
										'type' => Option::getByKey($wage->getType(), $wage->getOptions('type') ),
										'wage' => Misc::MoneyFormat( Misc::removeTrailingZeros($wage->getWage()), TRUE ),
										'currency_symbol' => $currency_symbol,
										'effective_date' => TTDate::getDate( 'DATE', $wage->getEffectiveDate() ),
										'is_owner' => $is_owner,
										'is_child' => $is_child,
										'deleted' => $wage->getDeleted()
									);

					if ( $wage->getWageGroup() == 0 ) {
						$user_has_default_wage = TRUE;
					}
				}
			}
		}


		$filter_data = NULL;
		// extract( UserGenericDataFactory::getSearchFormData( $saved_search_id, null) );

		if ( $permission->Check('wage','view') == FALSE ) {
			if ( $permission->Check('wage','view_child') ) {
				$filter_data['permission_children_ids'] = $permission_children_ids;
			}
			if ( $permission->Check('wage','view_own') ) {
				$filter_data['permission_children_ids'][] = $current_user->getId();
			}
		}

		$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );

		$user_options = UserListFactory::getArrayByListFactory( $ulf, FALSE, TRUE );


        $viewData['user_id'] = $user_id;
        $viewData['user_has_default_wage'] = $user_has_default_wage;
        $viewData['wages'] = $wages;
        $viewData['user_options'] = $user_options;
        // dd($viewData);

        return view('users.UserWageList', $viewData);

    }


    public function add($user_id = null)
    {
        if (!$user_id) {
            return redirect()->back()->with('error', 'User ID is required to add wage.');
        }

        // Optionally, validate user exists here before proceeding
        return redirect()->to(URLBuilder::getURL(array('user_id' => $user_id) , '/user/wage/edit', false));
    }



    public function delete($id)
	{
		$current_company = $this->currentCompany;

		if (empty($id)) {
			return response()->json(['error' => 'No Wage List selected.'], 400);
		}

		$uwlf = new UserWageListFactory();

			$wage_list = $uwlf->getByIdAndCompanyId($id, $current_company->getId() );

			foreach ($wage_list->rs as $wage) {
				$wage_list->data = (array)$wage; // added bcz currency data is null and it gives an error

				$wage_list->setDeleted(true); // Set deleted flag to true

				if ($wage_list->isValid()) {
					$res = $wage_list->Save();

					if($res){
						return response()->json(['success' => 'Wage List deleted successfully.']);
					}else{
						return response()->json(['error' => 'Wage List deleted failed.']);
					}
				}
			}

		return response()->json(['success' => 'Operation completed successfully.']);
	}


}
