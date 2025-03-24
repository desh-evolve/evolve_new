<?php

namespace App\Http\Controllers\policy;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Core\Environment;
use App\Models\Core\Debug;
use App\Models\Core\FormVariables;
use App\Models\Core\Option;
use App\Models\Core\Misc;
use App\Models\Core\Pager;
use App\Models\Core\Redirect;
use App\Models\Core\TTDate;
use App\Models\Core\URLBuilder;
use App\Models\Policy\MealPolicyFactory;
use App\Models\Policy\MealPolicyListFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;

class MealPolicyList extends Controller
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
        if ( !$permission->Check('meal_policy','enabled')
				OR !( $permission->Check('meal_policy','view') OR $permission->Check('meal_policy','view_own') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
        */

        $viewData['title'] = 'Meal Policy List';
		$current_company = $this->currentCompany;

		extract	(FormVariables::GetVariables(
			array (
				'action',
				'page',
				'sort_column',
				'sort_order',
				'ids',
			) 
		) );
		
		URLBuilder::setURL($_SERVER['SCRIPT_NAME'],
			array (
				'sort_column' => $sort_column,
				'sort_order' => $sort_order,
				'page' => $page
			) 
		);
		
		$sort_array = NULL;
		if ( $sort_column != '' ) {
			$sort_array = array($sort_column => $sort_order);
		}		

		$mplf = new MealPolicyListFactory();
		$mplf->getByCompanyId( $current_company->getId() );

		$pager = new Pager($mplf);

		$type_options = $mplf->getOptions('type');

		$show_no_policy_group_notice = FALSE;
		foreach ($mplf->rs as $mp_obj) {
			$mplf->data = (array)$mp_obj;
			$mp_obj = $mplf;

			if ( (int)$mp_obj->getColumn('assigned_policy_groups') == 0 ) {
				$show_no_policy_group_notice = TRUE;
			}

			$policies[] = array(
								'id' => $mp_obj->getId(),
								'name' => $mp_obj->getName(),
								'type_id' => $mp_obj->getType(),
								'type' => $type_options[$mp_obj->getType()],
								'amount' => $mp_obj->getAmount(),
								'trigger_time' => $mp_obj->getTriggerTime(),
								'assigned_policy_groups' => (int)$mp_obj->getColumn('assigned_policy_groups'),
								'deleted' => $mp_obj->getDeleted()
							);

		}

		$viewData['policies'] = $policies;
		$viewData['show_no_policy_group_notice'] = $show_no_policy_group_notice;
		$viewData['sort_column'] = $sort_column;
		$viewData['sort_order'] = $sort_order;
		$viewData['paging_data'] = $pager->getPageVariables();
		
        return view('policy/MealPolicyList', $viewData);

    }

	public function add(){
		Redirect::Page( URLBuilder::getURL( NULL, 'EditMealPolicy', FALSE) );
	}

	public function delete(){
		$current_company = $this->currentCompany;

		$delete = TRUE;

		$mplf = new MealPolicyListFactory();

		foreach ($ids as $id) {
			$mplf->getByIdAndCompanyId($id, $current_company->getId() );
			foreach ($mplf->rs as $mp_obj) {
				$mplf->data = (array)$mp_obj;
				$mp_obj = $mplf;

				$mp_obj->setDeleted($delete);
				if ( $mp_obj->isValid() ) {
					$mp_obj->Save();
				}
			}
		}

		Redirect::Page( URLBuilder::getURL( NULL, 'MealPolicyList') );

	}
}


?>