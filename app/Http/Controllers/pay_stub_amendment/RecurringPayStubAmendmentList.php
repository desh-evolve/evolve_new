<?php

namespace App\Http\Controllers\pay_stub_amendment;

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
use App\Models\PayStub\PayStubEntryAccountListFactory;
use App\Models\PayStubAmendment\RecurringPayStubAmendmentListFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;

class RecurringPayStubAmendmentList extends Controller
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
				OR !( $permission->Check('pay_stub_amendment','view') OR $permission->Check('pay_stub_amendment','view_own') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
        */

        $viewData['title'] = 'Recurring Pay Stub Amendment List';

		extract	(FormVariables::GetVariables(
			array (
				'action',
				'page',
				'sort_column',
				'sort_order',
				'ids',
				'user_id'
			) 
		) );
		
		URLBuilder::setURL($_SERVER['SCRIPT_NAME'],
			array (
				'sort_column' => $sort_column,
				'sort_order' => $sort_order,
				'page' => $page
			) 
		);
		
		$sort_array = NULL;
		if ( $sort_column != '' ) {
			$sort_array = array($sort_column => $sort_order);
		}
		

		URLBuilder::setURL(NULL, array('sort_column' => $sort_column, 'sort_order' => $sort_order) );

		$rpsalf = new RecurringPayStubAmendmentListFactory();

		$rpsalf->getByCompanyId($current_company->getId(), $current_user_prefs->getItemsPerPage(), $page, NULL, $sort_array );

		$pager = new Pager($rpsalf);

		$psealf = new PayStubEntryAccountListFactory();

		foreach ($rpsalf as $recurring_pay_stub_amendment) {

			$recurring_pay_stub_amendments[] = array(
				'id' => $recurring_pay_stub_amendment->GetId(),
				'name' => $recurring_pay_stub_amendment->getName(),
				'description' => $recurring_pay_stub_amendment->getDescription(),
				'status' => Option::getByKey($recurring_pay_stub_amendment->getStatus(), $recurring_pay_stub_amendment->getOptions('status') ),
				'frequency' => Option::getByKey($recurring_pay_stub_amendment->getFrequency(), $recurring_pay_stub_amendment->getOptions('frequency') ),
				'pay_stub_entry_name' => $psealf->getById( $recurring_pay_stub_amendment->getPayStubEntryNameId() )->getCurrent()->getName(),
				'deleted' => $recurring_pay_stub_amendment->getDeleted()
			);

		}

		$viewData['recurring_pay_stub_amendments'] = $recurring_pay_stub_amendments;
		$viewData['sort_column'] = $sort_column;
		$viewData['sort_order'] = $sort_order;
		$viewData['paging_data'] = $pager->getPageVariables();
		$viewData['user_id'] = $user_id;

        return view('pay_stub_amendment/RecurringPayStubAmendmentList', $viewData);

    }

	public function add(){
		Redirect::Page( URLBuilder::getURL( NULL, 'EditRecurringPayStubAmendment', FALSE) );
	}

	public function delete(){
		if ( strtolower($action) == 'delete' ) {
			$delete = TRUE;
		} else {
			$delete = FALSE;
		}

		$rpsalf = new RecurringPayStubAmendmentListFactory();

		foreach ($ids as $id) {
			$rpsalf->getById( $id );
			foreach ($rpsalf as $recurring_pay_stub_amendment) {
				$recurring_pay_stub_amendment->setDeleted($delete);
				$recurring_pay_stub_amendment->Save();
			}
		}
		unset($recurring_pay_stub_amendment);

		Redirect::Page( URLBuilder::getURL( NULL, 'RecurringPayStubAmendmentList', FALSE) );
	}

}

?>