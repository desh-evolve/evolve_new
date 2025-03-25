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
use App\Models\Holiday\HolidayFactory;
use App\Models\Holiday\HolidayListFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;

class EditHoliday extends Controller
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
		if ( !$permission->Check('holiday_policy','enabled')
				OR !( $permission->Check('holiday_policy','edit') OR $permission->Check('holiday_policy','edit_own') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
        */

		$viewData['title'] = isset($id) ? 'Edit Holiday' : 'Add Holiday';

		if ( isset($data['date_stamp'] ) ) {
			$data['date_stamp'] = TTDate::parseDateTime($data['date_stamp']);
		}
		
		$hf = new HolidayFactory(); 

		if ( isset($id) AND $id != '' ) {

			$hlf = new HolidayListFactory();
			$hlf->getByIdAndHolidayPolicyID( $id, $holiday_policy_id );
			if ( $hlf->getRecordCount() > 0 ) {
				foreach ($hlf->rs as $h_obj) {
					$hlf->data = (array)$h_obj;
					$h_obj = $hlf;

					//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);

					$data = array(
										'id' => $h_obj->getId(),
										'holiday_policy_id' => $h_obj->getHolidayPolicyID(),
										'date_stamp' => $h_obj->getDateStamp(),
										'name' => $h_obj->getName(),
										'created_date' => $h_obj->getCreatedDate(),
										'created_by' => $h_obj->getCreatedBy(),
										'updated_date' => $h_obj->getUpdatedDate(),
										'updated_by' => $h_obj->getUpdatedBy(),
										'deleted_date' => $h_obj->getDeletedDate(),
										'deleted_by' => $h_obj->getDeletedBy()
									);
				}
				$holiday_policy_id = $h_obj->getHolidayPolicyID();
			}
		} else {
			$data = array(
						'date_stamp' => TTDate::getTime(),
						'holiday_policy_id' => $holiday_policy_id
						);
		}

		$viewData['holiday_policy_id'] = $holiday_policy_id;
		$viewData['data'] = $data;
		$viewData['hf'] = $hf;

        return view('policy/EditHoliday', $viewData);

    }

	public function submit(Request $request){
		$data = $request->data;
		$hf = new HolidayFactory();

		//Debug::setVerbosity(11);
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$hf->setId( $data['id'] );
		if ( isset($data['holiday_policy_id'] ) ) {
			$hf->setHolidayPolicyId( $data['holiday_policy_id'] );
		}
		//Set datestamp first.
		$hf->setDateStamp( $data['date_stamp'] );
		$hf->setName( $data['name'] );


		if ( $hf->isValid() ) {
			$hf->Save();

			Redirect::Page( URLBuilder::getURL( array('id' => $data['holiday_policy_id']), 'HolidayList') );
		}
	}
}


?>