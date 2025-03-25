<?php

namespace App\Http\Controllers\policy;

use App\Http\Controllers\Controller;
use App\Models\Company\WageGroupListFactory;
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
use App\Models\PayStub\PayStubEntryAccountListFactory;
use App\Models\Policy\AccrualPolicyListFactory;
use App\Models\Policy\OverTimePolicyFactory;
use App\Models\Policy\OverTimePolicyListFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;

class EditOverTimePolicy extends Controller
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
        if ( !$permission->Check('over_time_policy','enabled')
				OR !( $permission->Check('over_time_policy','edit') OR $permission->Check('over_time_policy','edit_own') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
        */

		$viewData['title'] = isset($id) ? 'Edit Overtime Policy' : 'Add Overtime Policy';

		$current_company = $this->currentCompany;

		if ( isset($data['trigger_time'] ) ) {
			$data['trigger_time'] = TTDate::parseTimeUnit($data['trigger_time']);
		}
		
		if ( isset($data['max_time'] ) ) {
			$data['max_time'] = TTDate::parseTimeUnit($data['max_time']);
		}
		
		$otpf = new OverTimePolicyFactory();

		if ( isset($id) ) {

			$otplf = new OverTimePolicyListFactory();
			$otplf->getByIdAndCompanyID( $id, $current_company->getID() );

			foreach ($otplf->rs as $otp_obj) {
				$otplf->data = (array)$otp_obj;
				$otp_obj = $otplf;
				//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);

				$data = array(
					'id' => $otp_obj->getId(),
					'name' => $otp_obj->getName(),
					'type_id' => $otp_obj->getType(),
					//'level' => $otp_obj->getLevel(),
					'trigger_time' => $otp_obj->getTriggerTime(),
														'max_time' => $otp_obj->getMaxTime(),
					'rate' => Misc::removeTrailingZeros( $otp_obj->getRate() ),
					'wage_group_id' => $otp_obj->getWageGroup(),
					'accrual_rate' => Misc::removeTrailingZeros( $otp_obj->getAccrualRate() ),
					'accrual_policy_id' => $otp_obj->getAccrualPolicyID(),
					'pay_stub_entry_account_id' => $otp_obj->getPayStubEntryAccountId(),
					'created_date' => $otp_obj->getCreatedDate(),
					'created_by' => $otp_obj->getCreatedBy(),
					'updated_date' => $otp_obj->getUpdatedDate(),
					'updated_by' => $otp_obj->getUpdatedBy(),
					'deleted_date' => $otp_obj->getDeletedDate(),
					'deleted_by' => $otp_obj->getDeletedBy()
				);
			}
		} elseif ( $action != 'submit') {
			$data = array( 'trigger_time' => 0,'max_time' => 0, 'rate' => '1.00', 'accrual_rate' => '1.00' );
		}

		$aplf = new AccrualPolicyListFactory();
		$accrual_options = $aplf->getByCompanyIDArray( $current_company->getId(), TRUE );

		$psealf = new PayStubEntryAccountListFactory();
		$pay_stub_entry_options = $psealf->getByCompanyIdAndStatusIdAndTypeIdArray( $current_company->getId(), 10, array(10,20,30,50) );

		$wglf = new WageGroupListFactory();
		$data['wage_group_options'] = $wglf->getArrayByListFactory( $wglf->getByCompanyId( $current_company->getId() ), TRUE );

		//Select box options;
		$data['type_options'] = $otpf->getOptions('type');
		$data['accrual_options'] = $accrual_options;
		$data['pay_stub_entry_options'] = $pay_stub_entry_options;

		$viewData['data'] = $data;
		$viewData['otpf'] = $otpf;

        return view('policy/EditOverTimePolicy', $viewData);

    }

	public function submit(Request $request){
		$otpf = new OverTimePolicyFactory();
		$data = $request->data;
		$current_company = $this->currentCompany;

		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$otpf->setId( $data['id'] );
		$otpf->setCompany( $current_company->getId() );
		$otpf->setName( $data['name'] );
		$otpf->setType( $data['type_id'] );
		//$otpf->setLevel( $data['level'] );
		$otpf->setTriggerTime( $data['trigger_time'] );
                $otpf->setMaxTime( $data['max_time'] );
		$otpf->setRate( $data['rate'] );
		$otpf->setWageGroup( $data['wage_group_id'] );
		$otpf->setAccrualPolicyId( $data['accrual_policy_id'] );
		$otpf->setAccrualRate( $data['accrual_rate'] );
		$otpf->setPayStubEntryAccountId( $data['pay_stub_entry_account_id'] );

		if ( $otpf->isValid() ) {
			$otpf->Save();

			Redirect::Page( URLBuilder::getURL( NULL, 'OverTimePolicyList') );
		}
	}
}


?>