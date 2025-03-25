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
use App\Models\Policy\PolicyGroupListFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;

class PolicyGroupList extends Controller
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
        if ( !$permission->Check('policy_group','enabled')
				OR !( $permission->Check('policy_group','view') OR $permission->Check('policy_group','view_own') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
        */

        $viewData['title'] = 'Policy Group List';

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
		
		$pglf = new PolicyGroupListFactory();
		$pglf->getByCompanyId( $current_company->getId() );

		$pager = new Pager($pglf);

		foreach ($pglf->rs as $pg_obj) {
			$pglf->data = (array)$pg_obj;
			$pg_obj = $pglf;

			$policies[] = array(
								'id' => $pg_obj->getId(),
								'name' => $pg_obj->getName(),
								'deleted' => $pg_obj->getDeleted()
							);

		}
		
		$viewData['policies'] = $policies;
		$viewData['sort_column'] = $sort_column;
		$viewData['sort_order'] = $sort_order;
		$viewData['paging_data'] = $pager->getPageVariables();

        return view('policy/PolicyGroupList', $viewData);

    }

	public function delete(){
		$current_company = $this->currentCompany;
		$delete = TRUE;

		$pglf = new PolicyGroupListFactory();

		foreach ($ids as $id) {
			$pglf->getByIdAndCompanyId($id, $current_company->getId() );
			foreach ($pglf->rs as $pg_obj) {
				$pglf->data = (array)$pg_obj;
				$pg_obj = $pglf;

				$pg_obj->setDeleted($delete);
				$pg_obj->Save();
			}
		}

		Redirect::Page( URLBuilder::getURL( NULL, 'PolicyGroupList') );

	}
}






?>