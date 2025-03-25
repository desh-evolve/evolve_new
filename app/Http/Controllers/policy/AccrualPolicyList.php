<?php

namespace App\Http\Controllers\policy;

use App\Http\Controllers\Controller;

use App\Models\Core\Environment;
use App\Models\Core\Redirect;
use App\Models\Core\URLBuilder;
use App\Models\Policy\AccrualPolicyListFactory;
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
		$current_company = $this->currentCompany;

		$aplf = new AccrualPolicyListFactory();
		$aplf->getByCompanyId( $current_company->getId() );

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

        return view('policy/AccrualPolicyList', $viewData);

    }

	public function delete($id){
		if (empty($id)) {
            return response()->json(['error' => 'No Accrual Policy Selected.'], 400);
        }

		$current_company = $this->currentCompany;
		$delete = TRUE;

		$aplf = new AccrualPolicyListFactory();
		$aplf->getByIdAndCompanyId($id, $current_company->getId() );

		$aplf->StartTransaction();

		foreach ($aplf->rs as $ap_obj) {
			$aplf->data = (array)$ap_obj;
			$ap_obj = $aplf;

			$ap_obj->setDeleted($delete);
			if ( $ap_obj->isValid() ) {
				$res = $ap_obj->Save();

				if($res){
					return response()->json(['success' => 'Accrual Policy Deleted Successfully.']);
				}else{
					return response()->json(['error' => 'Accrual Policy Deleted Failed.']);
				}
			}
		}

		Redirect::Page( URLBuilder::getURL( NULL, 'AccrualPolicyList') );

	}

}

?>