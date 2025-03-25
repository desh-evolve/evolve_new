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
use App\Models\Policy\PremiumPolicyListFactory;
use App\Models\Users\UserListFactory;
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
		
		$pplf = new PremiumPolicyListFactory();
		$pplf->getByCompanyId( $current_company->getId() );

		$pager = new Pager($pplf);

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
		$viewData['sort_column'] = $sort_column;
		$viewData['sort_order'] = $sort_order;
		$viewData['paging_data'] = $pager->getPageVariables();

        return view('policy/PremiumPolicyList', $viewData);

    }

	public function add(){
		Redirect::Page( URLBuilder::getURL( NULL, 'EditPremiumPolicy', FALSE) );
	}

	public function delete(){
		$current_company = $this->currentCompany;
		$delete = TRUE;

		$pplf = new PremiumPolicyListFactory();

		foreach ($ids as $id) {
			$pplf->getByIdAndCompanyId($id, $current_company->getId() );

			foreach ($pplf->rs as $pp_obj) {
				$pplf->data = (array)$pp_obj;
				$pp_obj = $pplf;

				$pp_obj->setDeleted($delete);
				if ( $pp_obj->isValid() ) {
					$pp_obj->Save();
				}
			}
		}

		Redirect::Page( URLBuilder::getURL( NULL, 'PremiumPolicyList') );

	}

}

?>