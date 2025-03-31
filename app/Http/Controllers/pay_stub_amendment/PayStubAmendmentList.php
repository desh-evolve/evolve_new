<?php

namespace App\Http\Controllers\pay_stub_amendment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Core\Environment;
use App\Models\Core\Debug;
use App\Models\Core\Option;
use App\Models\Core\Misc;
use App\Models\Core\TTDate;
use App\Models\Core\URLBuilder;
use App\Models\PayStub\PayStubEntryAccountListFactory;
use App\Models\PayStubAmendment\PayStubAmendmentListFactory;
use App\Models\Users\UserGenericDataFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;

class PayStubAmendmentList extends Controller
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
        if ( !$permission->Check('pay_stub_amendment','enabled')
				OR !( $permission->Check('pay_stub_amendment','view') OR $permission->Check('pay_stub_amendment','view_child') OR $permission->Check('pay_stub_amendment','view_own') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
        */

		$current_company = $this->currentCompany;
		$current_user = $this->currentUser;

		$ulf = new UserListFactory();
		$psalf = new PayStubAmendmentListFactory(); 
		$filter_data = null;
		$psalf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );

		$psealf = new PayStubEntryAccountListFactory(); 
		$pay_stub_entry_name_options = $psealf->getByCompanyIdAndStatusIdAndTypeIdArray( $current_company->getId(), 10, array(10,20,30,50,60,65) );

		foreach ($psalf->rs as $psa_obj) {
			$psalf->data = (array)$psa_obj;
			$psa_obj = $psalf;
			$user_obj = $ulf->getById( $psa_obj->getUser() )->getCurrent();

			if ( $psa_obj->getType() == 10 ) {
				$amount = $psa_obj->getAmount();
			} else {
				$amount = $psa_obj->getPercentAmount().'%';
			}
			$pay_stub_amendments[] = array(
				'id' => $psa_obj->getId(),
				'user_id' => $psa_obj->getUser(),
				'first_name' => $user_obj->getFirstName(),
				'middle_name' => $user_obj->getMiddleName(),
				'last_name' => $user_obj->getLastName(),
				'status_id' =>$psa_obj->getStatus(),
				'status' => Option::getByKey($psa_obj->getStatus(), $psa_obj->getOptions('status') ),
				'type_id' => $psa_obj->getType(),
				'type' => Option::getByKey($psa_obj->getType(), $psa_obj->getOptions('type') ),
				'effective_date' => TTDate::getDate('DATE', $psa_obj->getEffectiveDate() ),
				'pay_stub_account_name' => Option::getByKey( $psa_obj->getPayStubEntryNameId(), $pay_stub_entry_name_options ),
				'amount' => $amount,
				//'amount' => $psa_obj->getAmount(),
				//'percent_amount' => $psa_obj->getPercentAmount(),
				'rate' => $psa_obj->getRate(),
				'units' => $psa_obj->getUnits(),
				'description' => $psa_obj->getDescription(),
				'authorized' => $psa_obj->getAuthorized(),
				'ytd_adjustment' => Misc::HumanBoolean($psa_obj->getYTDAdjustment()),
				'deleted' => $psa_obj->getDeleted()
			);

		}
		
		unset($column_key);

		$viewData['title'] = 'Pay Stub Amendment List';
		$viewData['pay_stub_amendments'] = $pay_stub_amendments;

        return view('pay_stub_amendment/PayStubAmendmentList', $viewData);

    }

	public function delete($id){
		$delete = TRUE;

		$psalf = new PayStubAmendmentListFactory();
		$psalf->getById( $id );

		foreach ($psalf->rs as $pay_stub_amendment) {
			$psalf->data = (array)$pay_stub_amendment;
			$pay_stub_amendment = $psalf; 
			//Only delete PS amendments NOT in the paid status.
			if ( $pay_stub_amendment->getStatus() != 55 ) {
				$pay_stub_amendment->setDeleted($delete);
				$pay_stub_amendment->Save();
			}
		}
			
		unset($pay_stub_amendment);

		return redirect(URLBuilder::getURL( NULL, '/payroll/pay_stub_amendment', TRUE));
	}

	public function search(){
		Debug::Text('Action: '. $action, __FILE__, __LINE__, __METHOD__,10);

		$saved_search_id = UserGenericDataFactory::searchFormDataHandler( $action, $filter_data, URLBuilder::getURL(NULL, 'PayStubAmendmentList.php') );
	}

}



?>