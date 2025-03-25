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
use App\Models\Policy\RoundIntervalPolicyFactory;
use App\Models\Policy\RoundIntervalPolicyListFactory;
use App\Models\Users\UserListFactory;
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
		
		$riplf = new RoundIntervalPolicyListFactory(); 
		$riplf->getByCompanyId( $current_company->getId() );

		$pager = new Pager($riplf);

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
				'interval' => $rip_obj->getInterval(),
				'assigned_policy_groups' => (int)$rip_obj->getColumn('assigned_policy_groups'),
				'deleted' => $rip_obj->getDeleted()
			);

		}
		
		$viewData['policies'] = $policies;
		$viewData['show_no_policy_group_notice'] = $show_no_policy_group_notice;
		$viewData['sort_column'] = $sort_column;
		$viewData['sort_order'] = $sort_order;
		$viewData['paging_data'] = $pager->getPageVariables();

        return view('policy/RoundIntervalPolicyList', $viewData);

    }

	public function add(){
		Redirect::Page( URLBuilder::getURL( NULL, 'EditRoundIntervalPolicy', FALSE) );
	}

	public function delete(){
		$current_company = $this->currentCompany;
		$delete = TRUE;

		$riplf = new RoundIntervalPolicyListFactory();

		foreach ($ids as $id) {
			$riplf->getByIdAndCompanyId($id, $current_company->getId() );
			foreach ($riplf->rs as $rip_obj) {
				$riplf->data = (array)$rip_obj;
				$rip_obj = $riplf;

				$rip_obj->setDeleted($delete);
				$rip_obj->Save();
			}
		}

		Redirect::Page( URLBuilder::getURL( NULL, 'RoundIntervalPolicyList') );

	}
}

?>