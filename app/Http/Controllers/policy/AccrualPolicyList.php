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
use App\Models\Policy\AccrualPolicyListFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;

class AccrualPolicyList extends Controller
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
        if ( !$permission->Check('accrual_policy','enabled')
				OR !( $permission->Check('accrual_policy','view') OR $permission->Check('accrual_policy','view_own') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
        */

        $viewData['title'] = 'Accrual Policy List';

		extract	(FormVariables::GetVariables(
			array (
				'action',
				'page',
				'sort_column',
				'sort_order',
				'ids',
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

		$aplf = new AccrualPolicyListFactory();
		$aplf->getByCompanyId( $current_company->getId() );

		$pager = new Pager($aplf);

		$type_options = $aplf->getOptions('type');

 		$show_no_policy_group_notice = FALSE;
		foreach ($aplf->rs as $ap_obj) {
			$aplf->data = (array)$ap_obj;
			$ap_obj = $aplf;

			if ( (int)$ap_obj->getColumn('assigned_policy_groups') == 0 ) {
				$show_no_policy_group_notice = TRUE;
			}

			$policies[] = array(
				'id' => $ap_obj->getId(),
				'name' => $ap_obj->getName(),
				'type_id' => $ap_obj->getType(),
				'type' => $type_options[$ap_obj->getType()],
				'assigned_policy_groups' => (int)$ap_obj->getColumn('assigned_policy_groups'),
				'deleted' => $ap_obj->getDeleted()
			);

		}

		$viewData['policies'] = $policies;
		$viewData['show_no_policy_group_notice'] = $show_no_policy_group_notice;
		$viewData['sort_column'] = $sort_column;
		$viewData['sort_order'] = $sort_order;
		$viewData['paging_data'] = $pager->getPageVariables();

        return view('policy/AccrualPolicyList', $viewData);

    }

	public function add(){
		Redirect::Page( URLBuilder::getURL( NULL, 'EditAccrualPolicy', FALSE) );
	}

	public function delete($ids){
		$current_company = $this->currentCompany;
		$delete = TRUE;
		$aplf = new AccrualPolicyListFactory();

		foreach ($ids as $id) {
			$aplf->getByIdAndCompanyId($id, $current_company->getId() );

			$aplf->StartTransaction();

			foreach ($aplf->rs as $ap_obj) {
				$aplf->data = (array)$ap_obj;
				$ap_obj = $aplf;

				$ap_obj->setDeleted($delete);
				if ( $ap_obj->isValid() ) {
					$ap_obj->Save();
				}
			}

			$aplf->CommitTransaction();
		}

		Redirect::Page( URLBuilder::getURL( NULL, 'AccrualPolicyList') );

	}

}

?>