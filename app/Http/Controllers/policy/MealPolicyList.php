<?php

namespace App\Http\Controllers\policy;

use App\Http\Controllers\Controller;

use App\Models\Core\Environment;
use App\Models\Core\Factory;
use App\Models\Core\Redirect;
use App\Models\Core\URLBuilder;
use App\Models\Policy\MealPolicyListFactory;
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

		$mplf = new MealPolicyListFactory();
		$mplf->getByCompanyId( $current_company->getId() );

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
				'amount' => Factory::convertToHoursAndMinutes($mp_obj->getAmount()),
				'trigger_time' => Factory::convertToHoursAndMinutes($mp_obj->getTriggerTime()),
				'assigned_policy_groups' => (int)$mp_obj->getColumn('assigned_policy_groups'),
				'deleted' => $mp_obj->getDeleted()
			);

		}

		$viewData['policies'] = $policies;
		$viewData['show_no_policy_group_notice'] = $show_no_policy_group_notice;
		
        return view('policy/MealPolicyList', $viewData);

    }

	public function delete($id){
		if (empty($id)) {
            return response()->json(['error' => 'No Meal Policy Selected.'], 400);
        }

		$current_company = $this->currentCompany;
		$delete = TRUE;

		$mplf = new MealPolicyListFactory();
		$mplf->getByIdAndCompanyId($id, $current_company->getId() );

		foreach ($mplf->rs as $mp_obj) {
			$mplf->data = (array)$mp_obj;
			$mp_obj = $mplf;

			$mp_obj->setDeleted($delete);
			if ( $mp_obj->isValid() ) {
				$res = $mp_obj->Save();

				if($res){
					return response()->json(['success' => 'Meal Policy Deleted Successfully.']);
				}else{
					return response()->json(['error' => 'Meal Policy Deleted Failed.']);
				}
			}
		}

		Redirect::Page( URLBuilder::getURL( NULL, 'MealPolicyList') );

	}
}


?>