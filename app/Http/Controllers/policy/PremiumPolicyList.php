<?php

namespace App\Http\Controllers\policy;

use App\Http\Controllers\Controller;

use App\Models\Core\Environment;
use App\Models\Core\Redirect;
use App\Models\Core\URLBuilder;
use App\Models\Policy\PremiumPolicyListFactory;
use Illuminate\Support\Facades\View;

class PremiumPolicyList extends Controller
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
        if ( !$permission->Check('premium_policy','enabled')
				OR !( $permission->Check('premium_policy','view') OR $permission->Check('premium_policy','view_own') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
        */

        $viewData['title'] = 'Premium Policy List';
		$current_company = $this->currentCompany;
		
		$pplf = new PremiumPolicyListFactory();
		$pplf->getByCompanyId( $current_company->getId() );

		$type_options = $pplf->getOptions('type');

 		$show_no_policy_group_notice = FALSE;
		foreach ($pplf->rs as $pp_obj) {
			$pplf->data = (array)$pp_obj;
			$pp_obj = $pplf;
			
			if ( (int)$pp_obj->getColumn('assigned_policy_groups') == 0 ) {
				$show_no_policy_group_notice = TRUE;
			}

			$policies[] = array(
								'id' => $pp_obj->getId(),
								'name' => $pp_obj->getName(),
								'type_id' => $pp_obj->getType(),
								'type' => $type_options[$pp_obj->getType()],
								//'trigger_time' => $pp_obj->getTriggerTime(),
								'assigned_policy_groups' => (int)$pp_obj->getColumn('assigned_policy_groups'),
								'deleted' => $pp_obj->getDeleted()
							);

		}

		$viewData['policies'] = $policies;
		$viewData['show_no_policy_group_notice'] = $show_no_policy_group_notice;

        return view('policy/PremiumPolicyList', $viewData);

    }

	public function delete($id){
		if (empty($id)) {
			return response()->json(['error' => 'No Premium Policy Selected.'], 400);
		}

		$current_company = $this->currentCompany;
		$delete = TRUE;

		$pplf = new PremiumPolicyListFactory();
		$pplf->getByIdAndCompanyId($id, $current_company->getId() );

		foreach ($pplf->rs as $pp_obj) {
			$pplf->data = (array)$pp_obj;
			$pp_obj = $pplf;

			$pp_obj->setDeleted($delete);
			if ( $pp_obj->isValid() ) {
				$res = $pp_obj->Save();

				if($res){
					return response()->json(['success' => 'Premium Policy Deleted Successfully.']);
				}else{
					return response()->json(['error' => 'Premium Policy Deleted Failed.']);
				}
			}
		}

		Redirect::Page( URLBuilder::getURL( NULL, 'PremiumPolicyList') );

	}

}

?>