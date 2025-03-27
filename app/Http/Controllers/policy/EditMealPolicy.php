<?php

namespace App\Http\Controllers\policy;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Core\Environment;
use App\Models\Core\Debug;
use App\Models\Core\Factory;
use App\Models\Core\TTDate;
use App\Models\Core\URLBuilder;
use App\Models\Policy\MealPolicyFactory;
use App\Models\Policy\MealPolicyListFactory;
use Illuminate\Support\Facades\View;

class EditMealPolicy extends Controller
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
        if ( !$permission->Check('meal_policy','enabled')
				OR !( $permission->Check('meal_policy','edit') OR $permission->Check('meal_policy','edit_own') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
        */

		$viewData['title'] = isset($id) ? 'Edit Meal Policy' : 'Add Meal Policy';

		$current_company = $this->currentCompany;

		if ( isset($data['trigger_time'] ) ) {
			$data['trigger_time'] = TTDate::parseTimeUnit($data['trigger_time']);
			$data['amount'] = TTDate::parseTimeUnit($data['amount']);
			$data['start_window'] = TTDate::parseTimeUnit($data['start_window']);
			$data['window_length'] = TTDate::parseTimeUnit($data['window_length']);
			$data['minimum_punch_time'] = TTDate::parseTimeUnit($data['minimum_punch_time']);
			$data['maximum_punch_time'] = TTDate::parseTimeUnit($data['maximum_punch_time']);
		}
		
		$mpf = new MealPolicyFactory();

		if ( isset($id) ) {

			$mplf = new MealPolicyListFactory();
			$mplf->getByIdAndCompanyID( $id, $current_company->getID() );

			foreach ($mplf->rs as $mp_obj) {
				$mplf->data = (array)$mp_obj;
				$mp_obj = $mplf;
				//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);

				$data = array (
					'id' => $mp_obj->getId(),
					'name' => $mp_obj->getName(),
					'type_id' => $mp_obj->getType(),
					'trigger_time' => Factory::convertToHoursAndMinutes($mp_obj->getTriggerTime()),
					'amount' => Factory::convertToHoursAndMinutes($mp_obj->getAmount()),
					'auto_detect_type_id' => $mp_obj->getAutoDetectType(),
					'start_window' => Factory::convertToHoursAndMinutes($mp_obj->getStartWindow()),
					'window_length' => Factory::convertToHoursAndMinutes($mp_obj->getWindowLength()),
					'minimum_punch_time' => Factory::convertToHoursAndMinutes($mp_obj->getMinimumPunchTime()),
					'maximum_punch_time' => Factory::convertToHoursAndMinutes($mp_obj->getMaximumPunchTime()),
					'include_lunch_punch_time' => $mp_obj->getIncludeLunchPunchTime(),
					'created_date' => $mp_obj->getCreatedDate(),
					'created_by' => $mp_obj->getCreatedBy(),
					'updated_date' => $mp_obj->getUpdatedDate(),
					'updated_by' => $mp_obj->getUpdatedBy(),
					'deleted_date' => $mp_obj->getDeletedDate(),
					'deleted_by' => $mp_obj->getDeletedBy()
				);
			}
		} else {
			$data = array (
				'type_id' => 10,
				'auto_detect_type_id' => 10,
				'trigger_time' => Factory::convertToHoursAndMinutes(3600 * 5),
				'amount' => Factory::convertToHoursAndMinutes(3600),
				'start_window' => Factory::convertToHoursAndMinutes(3600*4),
				'window_length' => Factory::convertToHoursAndMinutes(3600*2),
				'minimum_punch_time' => Factory::convertToHoursAndMinutes(60*30),
				'maximum_punch_time' => Factory::convertToHoursAndMinutes(60*60),
			);
		}

		//Select box options;
		$data['type_options'] = $mpf->getOptions('type');
		$data['auto_detect_type_options'] = $mpf->getOptions('auto_detect_type');

		$viewData['data'] = $data;
		$viewData['mpf'] = $mpf;

        return view('policy/EditMealPolicy', $viewData);

    }

	public function submit(Request $request){
		
		$mpf = new MealPolicyFactory();
		$data = $request->data;
		$current_company = $this->currentCompany;

		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$mpf->setId( $data['id'] );
		$mpf->setCompany( $current_company->getId() );
		$mpf->setName( $data['name'] );
		$mpf->setType( $data['type_id'] );
		$mpf->setTriggerTime( Factory::convertToSeconds($data['trigger_time']) );
		$mpf->setAmount( Factory::convertToSeconds($data['amount']) );

		$mpf->setAutoDetectType( $data['auto_detect_type_id'] );
		$mpf->setStartWindow( Factory::convertToSeconds($data['start_window']) );
		$mpf->setWindowLength( Factory::convertToSeconds($data['window_length']) );
		$mpf->setMinimumPunchTime( Factory::convertToSeconds($data['minimum_punch_time']) );
		$mpf->setMaximumPunchTime( Factory::convertToSeconds($data['maximum_punch_time']) );

		if ( isset($data['include_lunch_punch_time']) ) {
			$mpf->setIncludeLunchPunchTime( TRUE );
		} else {
			$mpf->setIncludeLunchPunchTime( FALSE );
		}

		if ( $mpf->isValid() ) {
			$mpf->Save();
			return redirect( URLBuilder::getURL( NULL, '/policy/meal_policies'));
		}
	}
}

?>