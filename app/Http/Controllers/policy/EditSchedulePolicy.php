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
use App\Models\Policy\AbsencePolicyListFactory;
use App\Models\Policy\BreakPolicyListFactory;
use App\Models\Policy\MealPolicyListFactory;
use App\Models\Policy\OverTimePolicyListFactory;
use App\Models\Policy\SchedulePolicyFactory;
use App\Models\Policy\SchedulePolicyListFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;

class EditSchedulePolicy extends Controller
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

    public function index($id = null) {
        /*
        if ( !$permission->Check('schedule_policy','enabled')
				OR !( $permission->Check('schedule_policy','edit') OR $permission->Check('schedule_policy','edit_own') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
        */

		$viewData['title'] = isset($id) ? 'Edit Schedule Policy' : 'Add Schedule Policy';
		$current_company = $this->currentCompany;

		if ( isset($data['start_stop_window'] ) ) {
			$data['start_stop_window'] = TTDate::parseTimeUnit($data['start_stop_window']);
		}
		
		$spf = new SchedulePolicyFactory();

		if ( isset($id) ) {

			$splf = new SchedulePolicyListFactory();
			$splf->getByIdAndCompanyID( $id, $current_company->getID() );

			foreach ($splf->rs as $sp_obj) {
				$splf->data = (array)$sp_obj;
				$sp_obj = $splf;
				//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);

				$data = array(
					'id' => $sp_obj->getId(),
					'name' => $sp_obj->getName(),
					'over_time_policy_id' => $sp_obj->getOverTimePolicyID(),
					'absence_policy_id' => $sp_obj->getAbsencePolicyID(),
					'meal_policy_id' => $sp_obj->getMealPolicyID(),
					'break_policy_ids' => $sp_obj->getBreakPolicy(),
					'start_stop_window' => $sp_obj->getStartStopWindow(),
					'created_date' => $sp_obj->getCreatedDate(),
					'created_by' => $sp_obj->getCreatedBy(),
					'updated_date' => $sp_obj->getUpdatedDate(),
					'updated_by' => $sp_obj->getUpdatedBy(),
					'deleted_date' => $sp_obj->getDeletedDate(),
					'deleted_by' => $sp_obj->getDeletedBy()
				);
			}
		} else {
			$data = array(
				'start_stop_window' => 3600
			);
		}

		$aplf = new AbsencePolicyListFactory();
		$absence_options = $aplf->getByCompanyIDArray( $current_company->getId(), TRUE );

		$otplf = new OverTimePolicyListFactory();
		//$over_time_options = $otplf->getByCompanyIDArray( $current_company->getId(), TRUE );
		$over_time_options = $otplf->getByCompanyIDArray( $current_company->getId(), TRUE, array('type_id' => '= 200') );

		$mplf = new MealPolicyListFactory();
		$meal_options = $mplf->getByCompanyIDArray( $current_company->getId(), TRUE );

		$bplf = new BreakPolicyListFactory();
		$break_options = $bplf->getByCompanyIdArray( $current_company->getId(), TRUE );

		//Select box options;
		$data['over_time_options'] = $over_time_options;
		$data['absence_options'] = $absence_options;
		$data['meal_options'] = $meal_options;
		$data['break_options'] = $break_options;

		$viewData['data'] = $data;
		$viewData['spf'] = $spf;

        return view('policy/EditSchedulePolicy', $viewData);

    }

	public function submit(Request $request){
		$spf = new SchedulePolicyFactory();
		$data = $request->data;
		$current_company = $this->currentCompany;
		
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$spf->setId( $data['id'] );
		$spf->setCompany( $current_company->getId() );
		$spf->setName( $data['name'] );
		$spf->setMealPolicyID( $data['meal_policy_id'] );
		$spf->setOverTimePolicyID( $data['over_time_policy_id'] );
		$spf->setAbsencePolicyID( $data['absence_policy_id'] );
		$spf->setStartStopWindow( $data['start_stop_window'] );

		if ( $spf->isValid() ) {
			$spf->Save(FALSE);

			if ( isset($data['break_policy_ids']) ) {
				$spf->setBreakPolicy( $data['break_policy_ids'] );
			} else {
				$spf->setBreakPolicy( array() );
			}

			Redirect::Page( URLBuilder::getURL( NULL, 'SchedulePolicyList') );
		}
	}
}

?>