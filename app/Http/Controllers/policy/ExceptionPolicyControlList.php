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
use App\Models\Policy\ExceptionPolicyControlListFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;

class ExceptionPolicyControlList extends Controller
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
        if ( !$permission->Check('exception_policy','enabled')
				OR !( $permission->Check('exception_policy','view') OR $permission->Check('exception_policy','view_own') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
        */

        $viewData['title'] = 'Exception Policy List';
		$current_company = $this->currentCompany;
		
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
		

		$epclf = new ExceptionPolicyControlListFactory();
		$epclf->getByCompanyId( $current_company->getId() );

		$pager = new Pager($epclf);

		$show_no_policy_group_notice = FALSE;
		foreach ($epclf->rs as $epc_obj) {
			$epclf->data = (array)$epc_obj;
			$epc_obj = $epclf;
			if ( (int)$epc_obj->getColumn('assigned_policy_groups') == 0 ) {
				$show_no_policy_group_notice = TRUE;
			}

			$policies[] = array(
								'id' => $epc_obj->getId(),
								'name' => $epc_obj->getName(),
								'assigned_policy_groups' => (int)$epc_obj->getColumn('assigned_policy_groups'),
								'deleted' => $epc_obj->getDeleted()
							);

		}

		$viewData['policies'] = $policies;
		$viewData['show_no_policy_group_notice'] = $show_no_policy_group_notice;
		$viewData['sort_column'] = $sort_column;
		$viewData['sort_order'] = $sort_order;
		$viewData['paging_data'] = $pager->getPageVariables();
		
        return view('policy/ExceptionPolicyControlList', $viewData);

    }

	public function add(){
		Redirect::Page( URLBuilder::getURL( NULL, 'EditExceptionPolicyControl', FALSE) );
	}

	public function delete(){
		$current_company = $this->currentCompany;

		$delete = TRUE;

		$epclf = new ExceptionPolicyControlListFactory();

		foreach ($ids as $id) {
			$epclf->getByIdAndCompanyId($id, $current_company->getId() );
			foreach ($epclf->rs as $epc_obj) {
				$epclf->data = (array)$epc_obj;
				$epc_obj = $epclf;
				
				$epc_obj->setDeleted($delete);
				if ( $epc_obj->isValid() ) {
					$epc_obj->Save();
				}
			}
		}

		Redirect::Page( URLBuilder::getURL( NULL, 'ExceptionPolicyControlList') );

	}
}




?>