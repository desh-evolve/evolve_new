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
use App\Models\Policy\BreakPolicyFactory;
use App\Models\Policy\BreakPolicyListFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;

class EditBreakPolicy extends Controller
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
        if ( !$permission->Check('break_policy','enabled')
				OR !( $permission->Check('break_policy','edit') OR $permission->Check('break_policy','edit_own') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
        */

		$viewData['title'] = isset($id) ? 'Edit Break Policy' : 'Add Break Policy';
		$current_company = $this->currentCompany;

		if ( isset($data['trigger_time'] ) ) {
			$data['trigger_time'] = TTDate::parseTimeUnit($data['trigger_time']);
			$data['amount'] = TTDate::parseTimeUnit($data['amount']);
			$data['start_window'] = TTDate::parseTimeUnit($data['start_window']);
			$data['window_length'] = TTDate::parseTimeUnit($data['window_length']);
			$data['minimum_punch_time'] = TTDate::parseTimeUnit($data['minimum_punch_time']);
			$data['maximum_punch_time'] = TTDate::parseTimeUnit($data['maximum_punch_time']);
		}
		
		$bpf = new BreakPolicyFactory();

		if ( isset($id) ) {

			$mplf = new BreakPolicyListFactory();
			$mplf->getByIdAndCompanyID( $id, $current_company->getId() );

			foreach ($mplf->rs as $mp_obj) {
				$mplf->data = (array)$mp_obj;
				$mp_obj = $mplf;
				//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);

				$data = array (
					'id' => $mp_obj->getId(),
					'name' => $mp_obj->getName(),
					'type_id' => $mp_obj->getType(),
					'trigger_time' => $mp_obj->getTriggerTime(),
					'amount' => $mp_obj->getAmount(),
					'auto_detect_type_id' => $mp_obj->getAutoDetectType(),
					'start_window' => $mp_obj->getStartWindow(),
					'window_length' => $mp_obj->getWindowLength(),
					'minimum_punch_time' => $mp_obj->getMinimumPunchTime(),
					'maximum_punch_time' => $mp_obj->getMaximumPunchTime(),
					'include_break_punch_time' => $mp_obj->getIncludeBreakPunchTime(),
					'include_multiple_breaks' => $mp_obj->getIncludeMultipleBreaks(),
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
				'trigger_time' => 3600 * 1,
				'amount' => 60 * 15,
				'auto_detect_type_id' => 10,
				'start_window' => 3600*1,
				'window_length' => 3600*1,
				'minimum_punch_time' => 60*5,
				'maximum_punch_time' => 60*20,
			);
		}

		//Select box options;
		$data['type_options'] = $bpf->getOptions('type');
		$data['auto_detect_type_options'] = $bpf->getOptions('auto_detect_type');

		$viewData['data'] = $data;
		$viewData['bpf'] = $bpf;

        return view('policy/EditBreakPolicy', $viewData);

    }

	public function submit(Request $request){

		$current_company = $this->currentCompany;
		$data = $request->data;
		$bpf = new BreakPolicyFactory();

		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$bpf->setId( $data['id'] );
		$bpf->setCompany( $current_company->getId() );
		$bpf->setName( $data['name'] );
		$bpf->setType( $data['type_id'] );
		$bpf->setTriggerTime( $data['trigger_time'] );
		$bpf->setAmount( $data['amount'] );

		$bpf->setAutoDetectType( $data['auto_detect_type_id'] );
		$bpf->setStartWindow( $data['start_window'] );
		$bpf->setWindowLength( $data['window_length'] );
		$bpf->setMinimumPunchTime( $data['minimum_punch_time'] );
		$bpf->setMaximumPunchTime( $data['maximum_punch_time'] );

		if ( isset($data['include_break_punch_time']) ) {
			$bpf->setIncludeBreakPunchTime( TRUE );
		} else {
			$bpf->setIncludeBreakPunchTime( FALSE );
		}

		if ( isset($data['include_multiple_breaks']) ) {
			$bpf->setIncludeMultipleBreaks( TRUE );
		} else {
			$bpf->setIncludeMultipleBreaks( FALSE );
		}

		if ( $bpf->isValid() ) {
			$bpf->Save();

			Redirect::Page( URLBuilder::getURL( NULL, 'BreakPolicyList') );
		}
	}
}


?>