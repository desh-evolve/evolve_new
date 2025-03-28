<?php

namespace App\Http\Controllers\policy;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Core\Environment;
use App\Models\Core\Debug;
use App\Models\Core\Factory;
use App\Models\Core\FormVariables;
use App\Models\Core\Option;
use App\Models\Core\Misc;
use App\Models\Core\Pager;
use App\Models\Core\Redirect;
use App\Models\Core\TTDate;
use App\Models\Core\URLBuilder;
use App\Models\Policy\RoundIntervalPolicyFactory;
use App\Models\Policy\RoundIntervalPolicyListFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;

class EditRoundIntervalPolicy extends Controller
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
        if ( !$permission->Check('round_policy','enabled')
				OR !( $permission->Check('round_policy','edit') OR $permission->Check('round_policy','edit_own') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
        */

		$viewData['title'] = isset($id) ? 'Edit Rounding Policy' : 'Add Rounding Policy';
		$current_company = $this->currentCompany;

		if ( isset($data['interval'] ) ) {
			$data['interval'] = TTDate::parseTimeUnit($data['interval']);
			$data['grace'] = TTDate::parseTimeUnit($data['grace']);
		}
		
		
		$ripf = new RoundIntervalPolicyFactory();

		if ( isset($id) ) {

			$riplf = new RoundIntervalPolicyListFactory();
			$riplf->getByIdAndCompanyID( $id, $current_company->getID() );

			foreach ($riplf->rs as $rip_obj) {
				$riplf->data = (array)$rip_obj;
				$rip_obj = $riplf;
				//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);

				$data = array(
					'id' => $rip_obj->getId(),
					'name' => $rip_obj->getName(),
					'punch_type_id' => $rip_obj->getPunchType(),
					'round_type_id' => $rip_obj->getRoundType(),
					'interval' => $rip_obj->getInterval(),
					'grace' => $rip_obj->getGrace(),
					'strict' => $rip_obj->getStrict(),
					'created_date' => $rip_obj->getCreatedDate(),
					'created_by' => $rip_obj->getCreatedBy(),
					'updated_date' => $rip_obj->getUpdatedDate(),
					'updated_by' => $rip_obj->getUpdatedBy(),
					'deleted_date' => $rip_obj->getDeletedDate(),
					'deleted_by' => $rip_obj->getDeletedBy()
				);
			}
		} else {
			$data = array(
							'interval' => 900,
							'grace' => 0
							);
		}

		//Select box options;
		$data['punch_type_options'] = $ripf->getOptions('punch_type');
		$data['round_type_options'] = $ripf->getOptions('round_type');

		$viewData['data'] = $data;
		$viewData['ripf'] = $ripf;

        return view('policy/EditRoundIntervalPolicy', $viewData);

    }

	public function submit(Request $request){
		$ripf = new RoundIntervalPolicyFactory();
		$data = $request->data;
		$current_company = $this->currentCompany;
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$ripf->setId( $data['id'] );
		$ripf->setCompany( $current_company->getId() );
		$ripf->setName( $data['name'] );
		$ripf->setPunchType( $data['punch_type_id'] );
		$ripf->setRoundType( $data['round_type_id'] );
		$interval = Factory::convertToSeconds($data['interval']);
		$grace = Factory::convertToSeconds($data['grace']);

		$ripf->setInterval( $interval );
		$ripf->setGrace( $grace );
		
		if ( isset($data['strict'] ) ) {
			$ripf->setStrict( TRUE );
		} else {
			$ripf->setStrict( FALSE );
		}

		if ( $ripf->isValid() ) {
			$ripf->Save();

			return redirect(URLBuilder::getURL( NULL, '/policy/rounding_policies'));

		}

	}
}

?>