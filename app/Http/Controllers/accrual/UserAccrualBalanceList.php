<?php

namespace App\Http\Controllers\accrual;

use App\Http\Controllers\Controller;
use App\Models\Accrual\AccrualBalanceListFactory;
use App\Models\Accrual\AccrualFactory;
use App\Models\Accrual\AccrualListFactory;
use Illuminate\Http\Request;

use App\Models\Core\Environment;
use App\Models\Core\Debug;
use App\Models\Core\Factory;
use App\Models\Core\FormVariables;
use App\Models\Core\Pager;
use App\Models\Core\Redirect;
use App\Models\Core\TTDate;
use App\Models\Core\URLBuilder;
use App\Models\Hierarchy\HierarchyListFactory;
use App\Models\Policy\AccrualPolicyListFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;

class UserAccrualBalanceList extends Controller
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

	public function index($filter_user_id = null)
    {
		/*
        if ( !$permission->Check('accrual','enabled')
				OR !( $permission->Check('accrual','view') OR $permission->Check('accrual','view_own') OR $permission->Check('accrual','view_child') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
        */
		
        $viewData['title'] = 'Accrual Balance List';

		$permission = $this->permission;
		$current_company = $this->currentCompany;
		$current_user = $this->currentUser;

		$ablf = new AccrualBalanceListFactory();
		$ulf = new UserListFactory();

		if ( $permission->Check('accrual','view') OR $permission->Check('accrual','view_child') ) {
			if ( isset($filter_user_id) ) {
				$user_id = $filter_user_id;
			} else {
				$user_id = $current_user->getId();
				$filter_user_id = $current_user->getId();
			}
		} else {
			$filter_user_id = $user_id = $current_user->getId();
		}

		$filter_data = NULL;

		//Get user object
		$ulf->getByIdAndCompanyID( $user_id, $current_company->getId() );
		
		if (  $ulf->getRecordCount() > 0 ) {
			$user_obj = $ulf->getCurrent();

			$ablf->getByUserIdAndCompanyId( $user_id, $current_company->getId() );

			$aplf = new AccrualPolicyListFactory();

			$accrual_policy_options = $aplf->getByCompanyIDArray( $current_company->getId() );
			$accruals = [];

			foreach ($ablf->rs as $ab_obj) {
				$ablf->data = (array)$ab_obj;
				$ab_obj = $ablf;

				$balance= $ab_obj->getBalance();
				$balance_temp = (float)$balance;
				$balance_temp1 = $balance_temp / 31500;
				
				$accruals[] = array(
					'id' => $ab_obj->getId(),
					'user_id' => $ab_obj->getUser(),
					'accrual_policy_id' => $ab_obj->getAccrualPolicyId(),
					'accrual_policy' => $accrual_policy_options[$ab_obj->getAccrualPolicyId()],
					'balance' => Factory::convertToHoursAndMinutes($ab_obj->getBalance()/8),
					'deleted' => $ab_obj->getDeleted()
				);
			}

			$viewData['accruals'] = $accruals;

			$hlf = new HierarchyListFactory();
			$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );
			
			if ( $permission->Check('accrual','view') == FALSE ) {
				if ( $permission->Check('accrual','view_child') ) {
					$filter_data['permission_children_ids'] = $permission_children_ids;
				}
				if ( $permission->Check('accrual','view_own') ) {
					$filter_data['permission_children_ids'][] = $current_user->getId();
				}
			}

			$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
			$user_options = $ulf->getArrayByListFactory( $ulf, FALSE, TRUE );
			
			$viewData['user_options'] = $user_options;
			$viewData['filter_user_id'] = $filter_user_id;
		}

		return view('accrual/UserAccrualBalanceList', $viewData);

	}

	public function add(){
		Redirect::Page( URLBuilder::getURL( NULL, 'EditUserAccrual') );
	}

}

?>