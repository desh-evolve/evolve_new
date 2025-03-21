<?php

namespace App\Http\Controllers\accrual;

use App\Http\Controllers\Controller;
use App\Models\Accrual\AccrualBalanceFactory;
use App\Models\Accrual\AccrualBalanceListFactory;
use App\Models\Accrual\AccrualFactory;
use App\Models\Accrual\AccrualListFactory;
use Illuminate\Http\Request;

use App\Models\Core\Environment;
use App\Models\Core\Debug;
use App\Models\Core\FormVariables;
use App\Models\Core\Option;
use App\Models\Core\Pager;
use App\Models\Core\Redirect;
use App\Models\Core\TTDate;
use App\Models\Core\URLBuilder;
use App\Models\Hierarchy\HierarchyListFactory;
use App\Models\Policy\AccrualPolicyListFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;

class ViewUserAccrualList extends Controller
{
    protected $permission;
    protected $company;
    protected $userPrefs;

    public function __construct()
    {
        $basePath = Environment::getBasePath();
        require_once($basePath . '/app/Helpers/global.inc.php');
        require_once($basePath . '/app/Helpers/Interface.inc.php');

        $this->userPrefs = View::shared('current_user_prefs');
        $this->company = View::shared('current_company');
        $this->permission = View::shared('permission');

        /*
        if ( $permission->Check('accrual','view') OR $permission->Check('accrual','view_child')) {
			$user_id = $user_id;
		} else {
			$user_id = $current_user->getId();
		}
        */
    }

	public function index() {

        $viewData['title'] = 'Accrual List';

		extract	(FormVariables::GetVariables(
			array	(
				'action',
				'page',
				'sort_column',
				'sort_order',
				'user_id',
				'accrual_policy_id',
				'ids',
			) 
		) );

		URLBuilder::setURL($_SERVER['SCRIPT_NAME'],
			array(
				'user_id' => $user_id,
				'accrual_policy_id' => $accrual_policy_id,
				'sort_column' => $sort_column,
				'sort_order' => $sort_order,
				'page' => $page
			) 
		);

		$sort_array = NULL;
		if ( $sort_column != '' ) {
			$sort_array = array($sort_column => $sort_order);
		}

		$alf = new AccrualListFactory();
		$alf->getByCompanyIdAndUserIdAndAccrualPolicyID( $current_company->getId(), $user_id, $accrual_policy_id, $current_user_prefs->getItemsPerPage(), $page, NULL, $sort_array);

		$pager = new Pager($alf);

		foreach ($alf->rs as $a_obj) {
			$alf->data = (array)$a_obj;
			$a_obj = $alf;

			$date_stamp = $a_obj->getColumn('date_stamp');
			if ( $date_stamp != '' ) {
				$date_stamp = TTDate::strtotime($date_stamp);
			}
			$accruals[] = array(
								'id' => $a_obj->getId(),
								'user_id' => $a_obj->getUser(),
								'accrual_policy_id' => $a_obj->getAccrualPolicyId(),
								'type_id' => $a_obj->getType(),
								'type' => Option::getByKey( $a_obj->getType(), $a_obj->getOptions('type') ),
								'user_date_total_id' => $a_obj->getUserDateTotalId(),
								'user_date_total_date_stamp' => $date_stamp,
								'time_stamp' => $a_obj->getTimeStamp(),
								'amount' => $a_obj->getAmount()/(8 * 3600),
								'system_type' => $a_obj->isSystemType(),
								'deleted' => $a_obj->getDeleted()
							);

			

		}
		$viewData['accruals'] = $accruals;

		$ulf = new UserListFactory();
		$user_obj = $ulf->getById( $user_id )->getCurrent();

		$aplf = new AccrualPolicyListFactory();
		$accrual_policy_obj = $aplf->getById( $accrual_policy_id )->getCurrent();

		$viewData['user_id'] = $user_id;
		$viewData['user_full_name'] = $user_obj->getFullName();
		$viewData['accrual_policy_id'] = $accrual_policy_id;
		$viewData['accrual_policy'] = $accrual_policy_obj->getName();
		$viewData['sort_column'] = $sort_column;
		$viewData['sort_order'] = $sort_order;
		$viewData['paging_data'] = $pager->getPageVariables();

		return view('accrual/ViewUserAccrualList', $viewData);

	}

	public function add(){
		Redirect::Page( URLBuilder::getURL( NULL, 'EditUserAccrual') );
	}

	public function delete(){


		extract	(FormVariables::GetVariables(
			array	(
				'action',
				'page',
				'sort_column',
				'sort_order',
				'user_id',
				'accrual_policy_id',
				'ids',
			) 
		) );

		$alf = new AccrualListFactory();

		$alf->StartTransaction();
		foreach ($ids as $id) {

			$alf->getById( $id );
			foreach ($alf->rs as $a_obj) {
				$alf->data = (array)$a_obj;
				$a_obj = $alf;
				//Allow user to delete AccrualPolicy entries, but not Banked/Used entries.
				if ( $a_obj->getUserDateTotalID() == FALSE ) {
					$a_obj->setEnableCalcBalance(FALSE);
					$a_obj->setDeleted(true);
					if ( $a_obj->isValid() ) {
						$a_obj->Save();
					}
				}
			}
		}

		AccrualBalanceFactory::calcBalance( $user_id, $accrual_policy_id );

		$alf->CommitTransaction();

		Redirect::Page( URLBuilder::getURL( NULL, 'ViewUserAccrualList') );
	}

}

?>