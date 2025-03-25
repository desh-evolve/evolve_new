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
use App\Models\Policy\HolidayPolicyListFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;

class HolidayPolicyList extends Controller
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
        if ( !$permission->Check('holiday_policy','enabled')
				OR !( $permission->Check('holiday_policy','view') OR $permission->Check('holiday_policy','view_own') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
        */

        $viewData['title'] = 'Holiday Policy List';
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

		$hplf = new HolidayPolicyListFactory(); 
		$hplf->getByCompanyId( $current_company->getId() );

		$pager = new Pager($hplf);

		$type_options = $hplf->getOptions('type');

		$show_no_policy_group_notice = FALSE;
		foreach ($hplf->rs as $hp_obj) {
			$hplf->data = (array)$hp_obj;
			$hp_obj = $hplf;

			if ( (int)$hp_obj->getColumn('assigned_policy_groups') == 0 ) {
				$show_no_policy_group_notice = TRUE;
			}

			$policies[] = array(
								'id' => $hp_obj->getId(),
								'name' => $hp_obj->getName(),
								'type_id' => $hp_obj->getType(),
								'type' => $type_options[$hp_obj->getType()],
								'assigned_policy_groups' => (int)$hp_obj->getColumn('assigned_policy_groups'),
								'deleted' => $hp_obj->getDeleted()
							);

		}

		$viewData['policies'] = $policies;
		$viewData['show_no_policy_group_notice'] = $show_no_policy_group_notice;
		$viewData['sort_column'] = $sort_column;
		$viewData['sort_order'] = $sort_order;
		$viewData['paging_data'] = $pager->getPageVariables();

        return view('accrual/ViewUserAccrualList', $viewData);

    }

	public function add(){
		Redirect::Page( URLBuilder::getURL( NULL, 'EditHolidayPolicy', FALSE) );
	}

	public function delete(){
		$current_company = $this->currentCompany;
		$delete = TRUE;

		$hplf = new HolidayPolicyListFactory();

		foreach ($ids as $id) {
			$hplf->getByIdAndCompanyId($id, $current_company->getId() );
			foreach ($hplf->rs as $hp_obj) {
				$hplf->data = (array)$hp_obj;
				$hp_obj = $hplf;

				$hp_obj->setDeleted($delete);
				if ( $hp_obj->isValid() ) {
					$hp_obj->Save();
				}
			}
		}

		Redirect::Page( URLBuilder::getURL( NULL, 'HolidayPolicyList') );

	}

}

?>