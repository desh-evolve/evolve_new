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
use App\Models\Policy\OverTimePolicyFactory;
use App\Models\Policy\OverTimePolicyListFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;

class OverTimePolicyList extends Controller
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
        if ( !$permission->Check('over_time_policy','enabled')
				OR !( $permission->Check('over_time_policy','view') OR $permission->Check('over_time_policy','view_own') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
        */

        $viewData['title'] = 'Overtime Policy List';
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

		$otplf = new OverTimePolicyListFactory();
		$otplf->getByCompanyId( $current_company->getId() );

		$pager = new Pager($otplf);

		$type_options = $otplf->getOptions('type');

 		$show_no_policy_group_notice = FALSE;
		foreach ($otplf->rs as $otp_obj) {
			$otplf->data = (array)$otp_obj;
			$otp_obj = $otplf;

			if ( (int)$otp_obj->getColumn('assigned_policy_groups') == 0 ) {
				$show_no_policy_group_notice = TRUE;
			}

			$policies[] = array(
								'id' => $otp_obj->getId(),
								'name' => $otp_obj->getName(),
								'type_id' => $otp_obj->getType(),
								'type' => $type_options[$otp_obj->getType()],
								'trigger_time' => $otp_obj->getTriggerTime(),
								'assigned_policy_groups' => (int)$otp_obj->getColumn('assigned_policy_groups'),
								'deleted' => $otp_obj->getDeleted()
							);

		}

		$viewData['policies'] = $policies;
		$viewData['show_no_policy_group_notice'] = $show_no_policy_group_notice;
		$viewData['sort_column'] = $sort_column;
		$viewData['sort_order'] = $sort_order;
		$viewData['paging_data'] = $pager->getPageVariables();
		
        return view('policy/OverTimePolicyList', $viewData);

    }

	public function add(){
		Redirect::Page( URLBuilder::getURL( NULL, 'EditOverTimePolicy', FALSE) );
	}

	public function delete(){
		$delete = TRUE;
		$current_company = $this->currentCompany;

		$otplf = new OverTimePolicyListFactory();

		foreach ($ids as $id) {
			$otplf->getByIdAndCompanyId($id, $current_company->getId() );

			foreach ($otplf->rs as $otp_obj) {
				$otplf->data = (array)$otp_obj;
				$otp_obj = $otplf;

				$otp_obj->setDeleted($delete);
				if ( $otp_obj->isValid() ) {
					$otp_obj->Save();
				}
			}
		}

		Redirect::Page( URLBuilder::getURL( NULL, 'OverTimePolicyList') );

	}

}

?>