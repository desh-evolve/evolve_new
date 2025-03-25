<?php

namespace App\Http\Controllers\pay_stub;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Core\Environment;
use App\Models\Core\Debug;
use App\Models\Core\FormVariables;
use App\Models\Core\Redirect;
use App\Models\Core\URLBuilder;
use Illuminate\Support\Facades\View;

class EditPayStubEntryNameAccount extends Controller
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
        if ( !$permission->Check('pay_stub','enabled')
				OR !$permission->Check('pay_stub','view') ) {
			$permission->Redirect( FALSE ); //Redirect
		}
        */

        $viewData['title'] = 'General Ledger Accounts';

		extract	(FormVariables::GetVariables(
			array (
				'action',
				'id',
				'name_account_data'
			) 
		) );
		
		$psenalf = new PayStubEntryNameAccountListFactory(); 

		if ( !isset($action) ) {

			$psenalf = new PayStubEntryNameAccountListFactory();
			$psenalf->getByCompanyId( $current_company->getId() );

			foreach ($psenalf->rs as $name_account_obj) {
				$psenalf->data = (array)$name_account_obj;
				$name_account_obj = $psenalf;
				//Debug::Arr($department,'Department', __FILE__, __LINE__, __METHOD__,10);

				$name_account_data[$name_account_obj->getPayStubEntryNameId()] = array(
											'id' => $name_account_obj->getId(),
											'pay_stub_entry_name_id' => $name_account_obj->getPayStubEntryNameId(),
											'debit_account' => $name_account_obj->getDebitAccount(),
											'credit_account' => $name_account_obj->getCreditAccount(),
											'created_date' => $name_account_obj->getCreatedDate(),
											'created_by' => $name_account_obj->getCreatedBy(),
											'updated_date' => $name_account_obj->getUpdatedDate(),
											'updated_by' => $name_account_obj->getUpdatedBy(),
											'deleted_date' => $name_account_obj->getDeletedDate(),
											'deleted_by' => $name_account_obj->getDeletedBy()
								);
			}

			//Get all accounts
			$psenlf = new PayStubEntryNameListFactory();
			$psenlf->getAll();

			$type_options  = $psenlf->getOptions('type');

			$i=0;
			foreach($psenlf->rs as $entry_name_obj) {
				$psenlf->data = (array)$entry_name_obj;
				$entry_name_obj = $psenlf;

				$display_type = FALSE;
				if ( $i == 0 ) {
					$display_type = TRUE;
				} else {
					if ( $entry_name_obj->getType() != $prev_type_id) {
						$display_type = TRUE;
					}
				}
				$name_account_data[$entry_name_obj->getId()]['pay_stub_entry_description'] = $entry_name_obj->getDescription();
				$name_account_data[$entry_name_obj->getId()]['pay_stub_entry_name_id'] = $entry_name_obj->getId();
				$name_account_data[$entry_name_obj->getId()]['type_id'] = $entry_name_obj->getType();
				$name_account_data[$entry_name_obj->getId()]['type'] = $type_options[$entry_name_obj->getType()];

				$name_account_data[$entry_name_obj->getId()]['display_type'] = $display_type;

				$data[] = $name_account_data[$entry_name_obj->getId()];

				$prev_type_id = $entry_name_obj->getType();
				$i++;
			}


		}

		$viewData['name_account_data'] = $data;
		$viewData['psenalf'] = $psenalf;

        return view('pay_stub/EditPayStubEntryNameAccount', $viewData);

    }

	public function submit(){
		$psenalf = new PayStubEntryNameAccountListFactory();

		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$psenaf = new PayStubEntryNameAccountFactory();

		$psenaf->StartTransaction();
		foreach($name_account_data as $pay_stub_entry_name_id => $value_arr){
			Debug::Text('Pay Stub Entry Name ID: '. $pay_stub_entry_name_id, __FILE__, __LINE__, __METHOD__,10);

			if ( ( isset($value_arr['debit_account'])  AND $value_arr['debit_account'] != '' )
					OR ( isset($value_arr['credit_account']) AND $value_arr['credit_account'] != '' )
					OR ( isset($value_arr['id']) AND $value_arr['id'] != '' )
				) {

				Debug::Text('Pay Stub Entry Name ID: '. $pay_stub_entry_name_id .' ID: '. $value_arr['id'] .'Debit Account: '. $value_arr['debit_account'] .' Credit Account: '. $value_arr['credit_account'], __FILE__, __LINE__, __METHOD__,10);

				if ( isset($value_arr['id']) AND $value_arr['id'] != '' ) {
					$psenaf->setId( $value_arr['id'] );
				}
				$psenaf->setCompany( $current_company->getId() );
				$psenaf->setPayStubEntryNameId( $pay_stub_entry_name_id  );
				$psenaf->setDebitAccount( $value_arr['debit_account'] );
				$psenaf->setCreditAccount( $value_arr['credit_account'] );
				if ( $psenaf->isValid() ) {
					$psenaf->Save();
				}
			} elseif ( ( isset($value_arr['id']) AND $value_arr['id'] != '' )
						AND $value_arr['debit_account'] == '' AND $value_arr['credit_account'] == '') {
				Debug::Text('Delete: ', __FILE__, __LINE__, __METHOD__,10);
			}
		}

		//$psenaf->FailTransaction();
		$psenaf->CommitTransaction();

		Redirect::Page( URLBuilder::getURL(NULL, Environment::getBaseURL().'/pay_stub/EditPayStubEntryNameAccount.php') );

	}
}


?>