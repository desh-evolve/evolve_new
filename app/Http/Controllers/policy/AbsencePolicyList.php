<?php

namespace App\Http\Controllers\policy;

use App\Http\Controllers\Controller;

use App\Models\Core\Environment;
use App\Models\Core\Redirect;
use App\Models\Core\URLBuilder;
use App\Models\Policy\AbsencePolicyListFactory;
use Illuminate\Support\Facades\View;

class AbsencePolicyList extends Controller
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
        if ( !$permission->Check('absence_policy','enabled')
				OR !( $permission->Check('absence_policy','view') OR $permission->Check('absence_policy','view_own') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
        */

        $viewData['title'] = 'Absence Policy List';
		$current_company = $this->currentCompany;

		$aplf = new AbsencePolicyListFactory();
		$aplf->getByCompanyId( $current_company->getId() );

		$type_options = $aplf->getOptions('type');

		foreach ($aplf->rs as $ap_obj) {
			$aplf->data = (array)$ap_obj;
			$ap_obj = $aplf;

			$policies[] = array(
				'id' => $ap_obj->getId(),
				'name' => $ap_obj->getName(),
				'type_id' => $ap_obj->getType(),
				'type' => $type_options[$ap_obj->getType()],
				'deleted' => $ap_obj->getDeleted()
			);

		}

		$viewData['policies'] = $policies;
		
        return view('policy/AbsencePolicyList', $viewData);

    }

	public function delete( $id ){
		if (empty($id)) {
            return response()->json(['error' => 'No Absence Policy Selected.'], 400);
        }

		$current_company = $this->currentCompany;
		$delete = TRUE;
		
		$aplf = new AbsencePolicyListFactory();
		$aplf->getByIdAndCompanyId($id, $current_company->getId() );

		foreach ($aplf->rs as $ap_obj) {
			$aplf->data = (array)$ap_obj;
			$ap_obj = $aplf;

			$ap_obj->setDeleted($delete);
			if ( $ap_obj->isValid() ) {
				$res = $ap_obj->Save();

				if($res){
					return response()->json(['success' => 'Absence Policy Deleted Successfully.']);
				}else{
					return response()->json(['error' => 'Absence Policy Deleted Failed.']);
				}
			}
		}

		Redirect::Page( URLBuilder::getURL( NULL, 'AbsencePolicyList') );

	}

}


?>