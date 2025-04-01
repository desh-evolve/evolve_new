<?php

namespace App\Http\Controllers\pay_stub;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Core\Environment;
use App\Models\Core\FormVariables;
use App\Models\Core\Pager;
use App\Models\Core\Redirect;
use App\Models\Core\URLBuilder;
use App\Models\PayStub\PayStubEntryAccountFactory;
use App\Models\PayStub\PayStubEntryAccountListFactory;
use Illuminate\Support\Facades\View;

class PayStubEntryAccountList extends Controller
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
        if ( !$permission->Check('pay_stub_account','enabled')
				OR !( $permission->Check('pay_stub_account','view') OR $permission->Check('pay_stub_account','view_own') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
        */

        $viewData['title'] = 'Pay Stub Account List';
		$current_company = $this->currentCompany;

		$psealf = new PayStubEntryAccountListFactory();
		$psealf->getByCompanyId( $current_company->getId() );

		$status_options = $psealf->getOptions('status');
		$type_options = $psealf->getOptions('type');

		foreach ($psealf->rs as $psea_obj) {
			$psealf->data = (array)$psea_obj;
			$psea_obj = $psealf;

			$rows[] = array(
				'id' => $psea_obj->getId(),
				'status_id' => $psea_obj->getStatus(),
				'status' => $status_options[$psea_obj->getStatus()],
				'type_id' => $psea_obj->getType(),
				'type' => $type_options[$psea_obj->getType()],
				'name' => $psea_obj->getName(),
				'ps_order' => $psea_obj->getOrder(),
				'debit_account' => $psea_obj->getDebitAccount(),
				'credit_account' => $psea_obj->getCreditAccount(),
				'deleted' => $psea_obj->getDeleted()
			);

		}
		
		$viewData['rows'] = $rows;
		
        return view('pay_stub/PayStubEntryAccountList', $viewData);

    }

	public function add_presets(){
		$current_company = $this->currentCompany;
		//Debug::setVerbosity(11);
		PayStubEntryAccountFactory::addPresets( $current_company->getId() );
		
		return redirect(URLBuilder::getURL( NULL, '/payroll/paystub_accounts'));
	}

	public function delete($id){
		$delete = TRUE;
		$current_company = $this->currentCompany;

		$psealf = new PayStubEntryAccountListFactory();
		$psealf->getByIdAndCompanyId($id, $current_company->getId() );

		foreach ($psealf->rs as $psea_obj) {
			$psealf->data = (array)$psea_obj;
			$psea_obj = $psealf;

			$psea_obj->setDeleted($delete);
			if ( $psea_obj->isValid() ) {
				$psea_obj->Save();
			}
		}

		URLBuilder::getURL( NULL, '/payroll/paystub_accounts');

	}

}

?>