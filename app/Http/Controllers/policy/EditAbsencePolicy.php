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
use App\Models\Policy\AbsencePolicyFactory;
use App\Models\Policy\AbsencePolicyListFactory;
use App\Models\Policy\AccrualPolicyListFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;

class EditAbsencePolicy extends Controller
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
        if ( !$permission->Check('absence_policy','enabled')
				OR !( $permission->Check('absence_policy','edit') OR $permission->Check('absence_policy','edit_own') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
        */

		$viewData['title'] = isset($id) ? 'Edit Absence Policy' : 'Add Absence Policy';
		$current_company = $this->currentCompany;

		$apf = new AbsencePolicyFactory();

		if ( isset($id) ) {
			$aplf = new AbsencePolicyListFactory();
			$aplf->getByIdAndCompanyID( $id, $current_company->getId() );

			foreach ($aplf->rs as $ap_obj) {
				$aplf->data = (array)$ap_obj;
				$ap_obj = $aplf;
				//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);

				$data = array (
					'id' => $ap_obj->getId(),
					'name' => $ap_obj->getName(),
					'type_id' => $ap_obj->getType(),
					'rate' => Misc::removeTrailingZeros( $ap_obj->getRate() ),
					'wage_group_id' => $ap_obj->getWageGroup(),
					'accrual_rate' => Misc::removeTrailingZeros( $ap_obj->getAccrualRate() ),
					'pay_stub_entry_account_id' => $ap_obj->getPayStubEntryAccountID(),
					'accrual_policy_id' => $ap_obj->getAccrualPolicyID(),
					'created_date' => $ap_obj->getCreatedDate(),
					'created_by' => $ap_obj->getCreatedBy(),
					'updated_date' => $ap_obj->getUpdatedDate(),
					'updated_by' => $ap_obj->getUpdatedBy(),
					'deleted_date' => $ap_obj->getDeletedDate(),
					'deleted_by' => $ap_obj->getDeletedBy()
				);
			}
		} else {
			$data = array(
				'rate' => '1.00',
				'accrual_rate' => '1.00',
			);

		}

		$aplf = new AccrualPolicyListFactory();
		$accrual_options = $aplf->getByCompanyIDArray( $current_company->getId(), TRUE );

		$psealf = new PayStubEntryAccountListFactory();
		$pay_stub_entry_options = $psealf->getByCompanyIdAndStatusIdAndTypeIdArray( $current_company->getId(), 10, array(10,20,30,50) );

		$wglf = new WageGroupListFactory();
		$data['wage_group_options'] = $wglf->getArrayByListFactory( $wglf->getByCompanyId( $current_company->getId() ), TRUE );

		//Select box options;
		$data['type_options'] = $apf->getOptions('type');
		$data['accrual_options'] = $accrual_options;
		$data['pay_stub_entry_options'] = $pay_stub_entry_options;

		$viewData['data'] = $data;
		$viewData['apf'] = $apf;
		
        return view('policy/EditAbsencePolicy', $viewData);

    }

	public function submit(Request $request){
		$current_company = $this->currentCompany;
		$data = $request->data;
		$apf = new AbsencePolicyFactory();

		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$apf->setId( $data['id'] );
		$apf->setCompany( $current_company->getId() );
		$apf->setName( $data['name'] );
		$apf->setType( $data['type_id'] );
		$apf->setRate( $data['rate'] );
		$apf->setWageGroup( $data['wage_group_id'] );
		$apf->setAccrualRate( $data['accrual_rate'] );
		$apf->setAccrualPolicyID( $data['accrual_policy_id'] );
		$apf->setPayStubEntryAccountID( $data['pay_stub_entry_account_id'] );

		if ( $apf->isValid() ) {
			$apf->Save();

			Redirect::Page( URLBuilder::getURL( NULL, 'AbsencePolicyList') );
		}

	}
}

?>