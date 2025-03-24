<?php

namespace App\Http\Controllers\pay_stub;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Core\Environment;
use App\Models\Core\Debug;
use App\Models\Core\FormVariables;
use App\Models\Core\Redirect;
use App\Models\Core\URLBuilder;
use App\Models\PayStub\PayStubEntryAccountFactory;
use App\Models\PayStub\PayStubEntryAccountListFactory;
use Illuminate\Support\Facades\View;

class EditPayStubEntryAccount extends Controller
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
				OR !( $permission->Check('pay_stub_account','edit') OR $permission->Check('pay_stub_account','edit_own') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
        */

        $viewData['title'] = 'Edit Pay Stub Account';

		extract	(FormVariables::GetVariables(
			array (
				'action',
				'id',
				'data'
			) 
		) );
		
		$pseaf = new PayStubEntryAccountFactory();

		if ( isset($id) ) {

			$psealf = new PayStubEntryAccountListFactory();
			$psealf->getById($id);

			foreach ($psealf->rs as $psea_obj) {
				$psealf->data = (array)$psea_obj;
				$psea_obj = $psealf;

				//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);

				$data = array(
									'id' => $psea_obj->getId(),
									'status_id' => $psea_obj->getStatus(),
									'type_id' => $psea_obj->getType(),
									'name' => $psea_obj->getName(),
									'order' => $psea_obj->getOrder(),
									'accrual_id' => $psea_obj->getAccrual(),
									'debit_account' => $psea_obj->getDebitAccount(),
									'credit_account' => $psea_obj->getCreditAccount(),
									'accrual_id' => $psea_obj->getAccrual(),
									'created_date' => $psea_obj->getCreatedDate(),
									'created_by' => $psea_obj->getCreatedBy(),
									'updated_date' => $psea_obj->getUpdatedDate(),
									'updated_by' => $psea_obj->getUpdatedBy(),
									'deleted_date' => $psea_obj->getDeletedDate(),
									'deleted_by' => $psea_obj->getDeletedBy()
								);
			}
		}

		//Select box options;
		$data['status_options'] = $pseaf->getOptions('status');
		$data['type_options'] = $pseaf->getOptions('type');

		$psealf = new PayStubEntryAccountListFactory();
		$data['accrual_options'] = $psealf->getByCompanyIdAndStatusIdAndTypeIdArray( $current_company->getId(), 10, array(50), TRUE );

		$viewData['data'] = $data;
		$viewData['pseaf'] = $pseaf;

        return view('pay_stub/EditPayStubEntryAccount', $viewData);

    }

	public function submit(){
		$pseaf = new PayStubEntryAccountFactory();

		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);
		//Debug::setVerbosity(11);

		$pseaf->setId( $data['id'] );
		$pseaf->setCompany( $current_company->getId() );
		$pseaf->setStatus( $data['status_id'] );
		$pseaf->setType( $data['type_id'] );
		$pseaf->setName( $data['name'] );
		$pseaf->setOrder( $data['order'] );
		$pseaf->setAccrual( $data['accrual_id'] );
		$pseaf->setDebitAccount( $data['debit_account'] );
		$pseaf->setCreditAccount( $data['credit_account'] );

		if ( $pseaf->isValid() ) {
			$pseaf->Save();

			Redirect::Page( URLBuilder::getURL( NULL, 'PayStubEntryAccountList.php') );

		}

	}
}

?>