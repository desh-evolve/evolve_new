<?php

namespace App\Http\Controllers\policy;

use App\Http\Controllers\Controller;

use App\Models\Core\Environment;
use App\Models\Core\Factory;
use App\Models\Core\Redirect;
use App\Models\Core\URLBuilder;
use App\Models\Policy\RoundIntervalPolicyListFactory;
use Illuminate\Support\Facades\View;

class RoundIntervalPolicyList extends Controller
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
        if ( !$permission->Check('round_policy','enabled')
				OR !( $permission->Check('round_policy','view') OR $permission->Check('round_policy','view_own') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
        */

        $viewData['title'] = 'Rounding Policy List';
		$current_company = $this->currentCompany;
		
		$riplf = new RoundIntervalPolicyListFactory(); 
		$riplf->getByCompanyId( $current_company->getId() );

		$punch_type_options = $riplf->getOptions('punch_type');

 		$show_no_policy_group_notice = FALSE;
		foreach ($riplf->rs as $rip_obj) {
			$riplf->data = (array)$rip_obj;
			$rip_obj = $riplf;

			if ( (int)$rip_obj->getColumn('assigned_policy_groups') == 0 ) {
				$show_no_policy_group_notice = TRUE;
			}

			$policies[] = array(
				'id' => $rip_obj->getId(),
				'name' => $rip_obj->getName(),
				'punch_type_id' => $rip_obj->getPunchType(),
				'punch_type' => $punch_type_options[$rip_obj->getPunchType()],
				'interval' => Factory::convertToHoursAndMinutes($rip_obj->getInterval()),
				'assigned_policy_groups' => (int)$rip_obj->getColumn('assigned_policy_groups'),
				'deleted' => $rip_obj->getDeleted()
			);

		}
		
		$viewData['policies'] = $policies;
		$viewData['show_no_policy_group_notice'] = $show_no_policy_group_notice;

        return view('policy/RoundIntervalPolicyList', $viewData);

    }

	public function delete( $id ){
		if (empty($id)) {
            return response()->json(['error' => 'No Round Interval Policy Selected.'], 400);
        }

		$current_company = $this->currentCompany;
		$delete = TRUE;

		$riplf = new RoundIntervalPolicyListFactory();
		$riplf->getByIdAndCompanyId($id, $current_company->getId() );

		foreach ($riplf->rs as $rip_obj) {
			$riplf->data = (array)$rip_obj;
			$rip_obj = $riplf;

			$rip_obj->setDeleted($delete);
			$res = $rip_obj->Save();

			if($res){
				return response()->json(['success' => 'Round Interval Policy Deleted Successfully.']);
			}else{
				return response()->json(['error' => 'Round Interval Policy Deleted Failed.']);
			}
		}

		Redirect::Page( URLBuilder::getURL( NULL, 'RoundIntervalPolicyList') );

	}
}

?>