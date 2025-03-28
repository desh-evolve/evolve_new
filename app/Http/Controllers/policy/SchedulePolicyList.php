<?php

namespace App\Http\Controllers\policy;

use App\Http\Controllers\Controller;

use App\Models\Core\Environment;
use App\Models\Core\Factory;
use App\Models\Core\Option;
use App\Models\Core\Redirect;
use App\Models\Core\URLBuilder;
use App\Models\Policy\AbsencePolicyListFactory;
use App\Models\Policy\MealPolicyListFactory;
use App\Models\Policy\SchedulePolicyListFactory;
use Illuminate\Support\Facades\View;

class SchedulePolicyList extends Controller
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
		if ( !$permission->Check('schedule_policy','enabled')
				OR !( $permission->Check('schedule_policy','view') OR $permission->Check('schedule_policy','view_own') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
        */

        $viewData['title'] = 'Schedule Policy List';
		$current_company = $this->currentCompany;

		$splf = new SchedulePolicyListFactory();
		$splf->getByCompanyId( $current_company->getId() );

		$aplf = new AbsencePolicyListFactory();
		$absence_options = $aplf->getByCompanyIDArray( $current_company->getId(), TRUE );

		$mplf = new MealPolicyListFactory();
		$meal_options = $mplf->getByCompanyIDArray( $current_company->getId(), TRUE );

		foreach ($splf->rs as $sp_obj) {
			$splf->data = (array)$sp_obj;
			$sp_obj = $splf;

			$policies[] = array(
				'id' => $sp_obj->getId(),
				'name' => $sp_obj->getName(),
				'meal_policy_id' => $sp_obj->getMealPolicyID(),
				'meal_policy' => Option::getByKey($sp_obj->getMealPolicyID(), $meal_options ),
				'absence_policy_id' => $sp_obj->getAbsencePolicyID(),
				'absence_policy' => Option::getByKey($sp_obj->getAbsencePolicyID(), $absence_options ),
				'start_stop_window' => Factory::convertToHoursAndMinutes($sp_obj->getStartStopWindow()),
				'deleted' => $sp_obj->getDeleted()
			);

		}
		
		$viewData['policies'] = $policies;
		
        return view('policy/SchedulePolicyList', $viewData);

    }

	public function delete( $id ){
		if (empty($id)) {
            return response()->json(['error' => 'No Schedule Policy Selected.'], 400);
        }

		$current_company = $this->currentCompany;
		$delete = TRUE;

		$splf = new SchedulePolicyListFactory();
		$splf->getByIdAndCompanyId($id, $current_company->getId() );

		foreach ($splf->rs as $sp_obj) {
			$splf->data = (array)$sp_obj;
			$sp_obj = $splf;

			$sp_obj->setDeleted($delete);
			if ( $sp_obj->isValid() ) {
				$res = $sp_obj->Save();

				if($res){
					return response()->json(['success' => 'Schedule Policy Deleted Successfully.']);
				}else{
					return response()->json(['error' => 'Schedule Policy Deleted Failed.']);
				}
			}
		}

		Redirect::Page( URLBuilder::getURL( NULL, 'SchedulePolicyList') );
	}

}


?>