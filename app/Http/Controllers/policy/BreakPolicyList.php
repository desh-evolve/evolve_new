<?php

namespace App\Http\Controllers\policy;

use App\Http\Controllers\Controller;

use App\Models\Core\Environment;
use App\Models\Core\Factory;
use App\Models\Core\Redirect;
use App\Models\Core\URLBuilder;
use App\Models\Policy\BreakPolicyListFactory;
use Illuminate\Support\Facades\View;

class BreakPolicyList extends Controller
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
        if ( !$permission->Check('break_policy','enabled')
				OR !( $permission->Check('break_policy','view') OR $permission->Check('break_policy','view_own') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
        */

        $viewData['title'] = 'Break Policy List';
		$current_company = $this->currentCompany;

		$bplf = new BreakPolicyListFactory(); 
		$bplf->getByCompanyId( $current_company->getId() );

		$type_options = $bplf->getOptions('type');

		$show_no_policy_group_notice = FALSE;
		foreach ($bplf->rs as $bp_obj) {
			$bplf->data = (array)$bp_obj;
			$bp_obj = $bplf;

			if ( (int)$bp_obj->getColumn('assigned_policy_groups') == 0 ) {
				$show_no_policy_group_notice = TRUE;
			}

			$policies[] = array(
				'id' => $bp_obj->getId(),
				'name' => $bp_obj->getName(),
				'type_id' => $bp_obj->getType(),
				'type' => $type_options[$bp_obj->getType()],
				'amount' => Factory::convertToHoursAndMinutes($bp_obj->getAmount()),
				'trigger_time' => Factory::convertToHoursAndMinutes($bp_obj->getTriggerTime()),
				'assigned_policy_groups' => (int)$bp_obj->getColumn('assigned_policy_groups'),
				'deleted' => $bp_obj->getDeleted()
			);

		}

		$viewData['policies'] = $policies;
		$viewData['show_no_policy_group_notice'] = $show_no_policy_group_notice;
		
        return view('policy/BreakPolicyList', $viewData);

    }

	public function delete($id){
		if (empty($id)) {
            return response()->json(['error' => 'No Break Policy Selected.'], 400);
        }

		$current_company = $this->currentCompany;
		$delete = TRUE;

		$bplf = new BreakPolicyListFactory();
		$bplf->getByIdAndCompanyId($id, $current_company->getId() );

		foreach ($bplf as $bp_obj) {
			$bplf->data = (array)$bp_obj;
			$bp_obj = $bplf;

			$bp_obj->setDeleted($delete);
			if ( $bp_obj->isValid() ) {
				$res = $bp_obj->Save();

				if($res){
					return response()->json(['success' => 'Break Policy Deleted Successfully.']);
				}else{
					return response()->json(['error' => 'Break Policy Deleted Failed.']);
				}
			}
		}

		Redirect::Page( URLBuilder::getURL( NULL, 'BreakPolicyList') );

	}

}

?>