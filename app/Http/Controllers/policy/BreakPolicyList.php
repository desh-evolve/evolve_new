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

		$bplf = new BreakPolicyListFactory(); 
		$bplf->getByCompanyId( $current_company->getId() );

		$pager = new Pager($bplf);

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
				'amount' => $bp_obj->getAmount(),
				'trigger_time' => $bp_obj->getTriggerTime(),
				'assigned_policy_groups' => (int)$bp_obj->getColumn('assigned_policy_groups'),
				'deleted' => $bp_obj->getDeleted()
			);

		}

		$viewData['policies'] = $policies;
		$viewData['show_no_policy_group_notice'] = $show_no_policy_group_notice;
		$viewData['sort_column'] = $sort_column;
		$viewData['sort_order'] = $sort_order;
		$viewData['paging_data'] = $pager->getPageVariables();
		
        return view('policy/BreakPolicyList', $viewData);

    }

	public function add(){
		Redirect::Page( URLBuilder::getURL( NULL, 'EditBreakPolicy.php', FALSE) );
	}

	public function delete(){
		$current_company = $this->currentCompany;

		$delete = TRUE;

		$bplf = new BreakPolicyListFactory();

		foreach ($ids as $id) {
			$bplf->getByIdAndCompanyId($id, $current_company->getId() );
			foreach ($bplf as $bp_obj) {
				$bplf->data = (array)$bp_obj;
				$bp_obj = $bplf;

				$bp_obj->setDeleted($delete);
				if ( $bp_obj->isValid() ) {
					$bp_obj->Save();
				}
			}
		}

		Redirect::Page( URLBuilder::getURL( NULL, 'BreakPolicyList.php') );

	}

}

?>