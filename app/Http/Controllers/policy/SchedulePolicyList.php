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
use App\Models\Policy\AbsencePolicyListFactory;
use App\Models\Policy\MealPolicyListFactory;
use App\Models\Policy\SchedulePolicyListFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;

class SchedulePolicyList extends Controller
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
		if ( !$permission->Check('schedule_policy','enabled')
				OR !( $permission->Check('schedule_policy','view') OR $permission->Check('schedule_policy','view_own') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
        */

        $viewData['title'] = 'Schedule Policy List';
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

		$splf = new SchedulePolicyListFactory();
		$splf->getByCompanyId( $current_company->getId() );

		$pager = new Pager($splf);

		$aplf = new AbsencePolicyListFactory();
		$absence_options = $aplf->getByCompanyIDArray( $current_company->getId(), TRUE );

		$mplf = new MealPolicyListFactory();
		$meal_options = $mplf->getByCompanyIDArray( $current_company->getId(), TRUE );

		foreach ($splf->rs as $sp_obj) {
			$splf->data = (array)$sp_obj;
			$sp_obj = $splf;

			$policies[] = array(
				'id' => $sp_obj->getId(),
				'name' => $sp_obj->getName(),
				'meal_policy_id' => $sp_obj->getMealPolicyID(),
				'meal_policy' => Option::getByKey($sp_obj->getMealPolicyID(), $meal_options ),
				'absence_policy_id' => $sp_obj->getAbsencePolicyID(),
				'absence_policy' => Option::getByKey($sp_obj->getAbsencePolicyID(), $absence_options ),
				'start_stop_window' => $sp_obj->getStartStopWindow(),
				'deleted' => $sp_obj->getDeleted()
			);

		}
		
		$viewData['policies'] = $policies;
		$viewData['sort_column'] = $sort_column;
		$viewData['sort_order'] = $sort_order;
		$viewData['paging_data'] = $pager->getPageVariables();
		
        return view('policy/SchedulePolicyList', $viewData);

    }

	public function add(){
		Redirect::Page( URLBuilder::getURL( NULL, 'EditSchedulePolicy', FALSE) );
	}

	public function delete(){
		$current_company = $this->currentCompany;
		$delete = TRUE;

		$splf = new SchedulePolicyListFactory();

		foreach ($ids as $id) {
			$splf->getByIdAndCompanyId($id, $current_company->getId() );
			foreach ($splf->rs as $sp_obj) {
				$splf->data = (array)$sp_obj;
				$sp_obj = $splf;

				$sp_obj->setDeleted($delete);
				if ( $sp_obj->isValid() ) {
					$sp_obj->Save();
				}
			}
		}

		Redirect::Page( URLBuilder::getURL( NULL, 'SchedulePolicyList') );
	}

}


?>