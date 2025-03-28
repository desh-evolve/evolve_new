<?php

namespace App\Http\Controllers\policy;

use App\Http\Controllers\Controller;

use App\Models\Core\Environment;
use App\Models\Core\Pager;
use App\Models\Policy\PolicyGroupListFactory;
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

        return view('policy/PolicyGroupList', $viewData);

    }

	public function delete($id){
		if (empty($id)) {
            return response()->json(['error' => 'No Policy Groups Selected.'], 400);
        }

		$current_company = $this->currentCompany;
		$delete = TRUE;

		$pglf = new PolicyGroupListFactory();
		$pglf->getByIdAndCompanyId($id, $current_company->getId() );
		
		foreach ($pglf->rs as $pg_obj) {
			$pglf->data = (array)$pg_obj;
			$pg_obj = $pglf;

			$pg_obj->setDeleted($delete);
			$res = $pg_obj->Save();

			if($res){
				return response()->json(['success' => 'Policy Group Deleted Successfully.']);
			}else{
				return response()->json(['error' => 'Policy Group Deleted Failed.']);
			}
		}

	}
}






?>